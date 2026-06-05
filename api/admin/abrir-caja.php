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

    // Verificar que no tenga una caja ya abierta
    $cajaAbierta = $db->fetchOne(
        "SELECT CajaID FROM Caja WHERE EmpleadoID = :empId AND Estado = 'Abierta'",
        [':empId' => $empleado['EmpleadoID']]
    );

    if ($cajaAbierta) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya tienes una caja abierta']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    $montoApertura = (float)($input['montoApertura'] ?? 0);
    $observaciones = trim($input['observacionesApertura'] ?? '');

    // Validaciones
    if ($montoApertura < 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El monto de apertura no puede ser negativo']);
        exit;
    }

    // Crear registro de apertura de caja
    $db->query(
        "INSERT INTO Caja (EmpleadoID, Monto_Apertura, Fecha_hora_Apertura, Estado, Observaciones) 
         VALUES (:empId, :monto, NOW(), 'Abierta', :obs)",
        [
            ':empId' => $empleado['EmpleadoID'],
            ':monto' => $montoApertura,
            ':obs' => $observaciones ?: null
        ]
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Caja abierta correctamente'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
