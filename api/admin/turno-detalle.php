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

    // Verificar que sea administrador
    $userRole = $db->fetchOne(
        "SELECT TipUsuID FROM Usuario WHERE UsuarioID = :userId",
        [':userId' => $currentUser['UsuarioID']]
    );

    if ($userRole['TipUsuID'] != 1) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit;
    }

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de turno inválido']);
        exit;
    }

    $turnoId = (int)$_GET['id'];

    // Obtener datos del turno con cantidad de empleados
    $turno = $db->fetchOne(
        "SELECT t.TurnoID, t.Descripcion, t.Hora_Inicio, t.Hora_Fin,
                COUNT(e.EmpleadoID) as total_empleados
         FROM Turno t
         LEFT JOIN Empleado e ON t.TurnoID = e.TurnoID
         WHERE t.TurnoID = :id
         GROUP BY t.TurnoID",
        [':id' => $turnoId]
    );

    if (!$turno) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Turno no encontrado']);
        exit;
    }

    // Obtener empleados asignados a este turno
    $empleados = $db->fetchAll(
        "SELECT e.EmpleadoID, e.Nombre_Apellidos, e.DNI, e.Telefono,
                tr.Nombre as Rol
         FROM Empleado e
         JOIN Tipo_Rol tr ON e.RolID = tr.RolID
         WHERE e.TurnoID = :id
         ORDER BY e.Nombre_Apellidos ASC",
        [':id' => $turnoId]
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'turno' => $turno,
        'empleados' => $empleados
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
