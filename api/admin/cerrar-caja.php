<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    startSecureSession();
    requireAuth();
    requirePermission('backoffice');

    $currentUser = getCurrentUser();
    $db = getDB();

    // Verificar que sea administrador o cajero
    $userRole = $db->fetchOne(
        "SELECT TipUsuID FROM Usuario WHERE UsuarioID = :userId",
        [':userId' => $currentUser['UsuarioID']]
    );

    if (!in_array($userRole['TipUsuID'], [1, 2])) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit;
    }

    // Obtener empleado actual
    $empleado = $db->fetchOne(
        "SELECT EmpleadoID FROM Empleado WHERE UsuarioID = :userId",
        [':userId' => $currentUser['UsuarioID']]
    );

    if (!$empleado) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No se encontró el empleado asociado']);
        exit;
    }

    // Verificar que tenga una caja abierta
    $cajaAbierta = $db->fetchOne(
        "SELECT CajaID, Monto_Apertura FROM Caja 
         WHERE EmpleadoID = :empId AND Estado = 'Abierta' 
         ORDER BY CajaID DESC LIMIT 1",
        [':empId' => $empleado['EmpleadoID']]
    );

    if (!$cajaAbierta) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No tienes una caja abierta']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    $montoCierre = (float)($input['montoCierre'] ?? 0);
    $observaciones = trim($input['observacionesCierre'] ?? '');

    // Validaciones
    if ($montoCierre < 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El monto de cierre no puede ser negativo']);
        exit;
    }

    // Calcular total de ventas del día
    $ventasDelDia = $db->fetchOne(
        "SELECT COALESCE(SUM(cp.Total), 0) as total
         FROM Comprobante_Pago cp
         JOIN Pedido p ON cp.PedidoID = p.PedidoID
         WHERE DATE(cp.Fecha_Emision) = CURDATE()
         AND p.EmpleadoID = :empId",
        [':empId' => $empleado['EmpleadoID']]
    );

    $totalEsperado = $cajaAbierta['Monto_Apertura'] + $ventasDelDia['total'];
    $diferencia = $montoCierre - $totalEsperado;

    // Actualizar caja con monto de cierre
    $observacionesFinal = $observaciones;
    if ($diferencia != 0) {
        $tipoDesc = $diferencia > 0 ? 'Sobrante' : 'Faltante';
        $observacionesFinal .= "\n\n[ARQUEO] {$tipoDesc}: S/ " . number_format(abs($diferencia), 2);
        $observacionesFinal .= "\nEsperado: S/ " . number_format($totalEsperado, 2);
        $observacionesFinal .= "\nContado: S/ " . number_format($montoCierre, 2);
    }

    $db->query(
        "UPDATE Caja 
         SET Monto_Cierre = :montoCierre,
             Fecha_hora_Cierre = NOW(),
             Estado = 'Cerrada',
             Observaciones = CONCAT(COALESCE(Observaciones, ''), :obsAdicionales)
         WHERE CajaID = :cajaId",
        [
            ':montoCierre' => $montoCierre,
            ':obsAdicionales' => "\n\n" . trim($observacionesFinal),
            ':cajaId' => $cajaAbierta['CajaID']
        ]
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Caja cerrada correctamente',
        'diferencia' => $diferencia,
        'totalEsperado' => $totalEsperado,
        'montoCierre' => $montoCierre
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
