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

    if (!isset($input['turnoId']) || !is_numeric($input['turnoId'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de turno inválido']);
        exit;
    }

    $turnoId = (int)$input['turnoId'];

    // Verificar si el turno existe
    $turno = $db->fetchOne(
        "SELECT TurnoID FROM Turno WHERE TurnoID = :id",
        [':id' => $turnoId]
    );

    if (!$turno) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Turno no encontrado']);
        exit;
    }

    // Verificar si hay empleados asignados a este turno
    $empleados = $db->fetchOne(
        "SELECT COUNT(*) as total FROM Empleado WHERE TurnoID = :id",
        [':id' => $turnoId]
    );

    if ($empleados['total'] > 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No se puede eliminar el turno porque tiene ' . $empleados['total'] . ' empleado(s) asignado(s)'
        ]);
        exit;
    }

    // Eliminar el turno
    $db->query(
        "DELETE FROM Turno WHERE TurnoID = :id",
        [':id' => $turnoId]
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Turno eliminado correctamente'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
