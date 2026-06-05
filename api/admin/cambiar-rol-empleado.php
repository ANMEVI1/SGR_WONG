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

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['empleadoId']) || !is_numeric($input['empleadoId'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de empleado inválido']);
        exit;
    }

    if (!isset($input['nuevoRolId']) || !is_numeric($input['nuevoRolId'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de rol inválido']);
        exit;
    }

    $empleadoId = (int)$input['empleadoId'];
    $nuevoRolId = (int)$input['nuevoRolId'];

    // Verificar que el empleado existe
    $empleado = $db->fetchOne(
        "SELECT EmpleadoID, Nombre_Apellidos FROM Empleado WHERE EmpleadoID = :id",
        [':id' => $empleadoId]
    );

    if (!$empleado) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
        exit;
    }

    // Verificar que el rol existe
    $rol = $db->fetchOne(
        "SELECT RolID, Nombre FROM Tipo_Rol WHERE RolID = :id",
        [':id' => $nuevoRolId]
    );

    if (!$rol) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
        exit;
    }

    // Actualizar el rol del empleado
    $db->query(
        "UPDATE Empleado SET RolID = :rolId WHERE EmpleadoID = :empId",
        [
            ':rolId' => $nuevoRolId,
            ':empId' => $empleadoId
        ]
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Rol del empleado actualizado correctamente'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
