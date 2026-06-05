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

    // Verificar que sea administrador o almacenero
    $userRole = $db->fetchOne(
        "SELECT TipUsuID FROM Usuario WHERE UsuarioID = :userId",
        [':userId' => $currentUser['UsuarioID']]
    );

    if (!in_array($userRole['TipUsuID'], [1, 4])) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['insumoId']) || !is_numeric($input['insumoId'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de insumo inválido']);
        exit;
    }

    if (!isset($input['estado']) || !in_array($input['estado'], ['Activo', 'Inactivo'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Estado inválido']);
        exit;
    }

    $insumoId = (int)$input['insumoId'];
    $nuevoEstado = $input['estado'];

    // Verificar si el insumo existe
    $insumo = $db->fetchOne(
        "SELECT InsumoID FROM Insumo WHERE InsumoID = :id",
        [':id' => $insumoId]
    );

    if (!$insumo) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Insumo no encontrado']);
        exit;
    }

    // Actualizar estado
    $db->query(
        "UPDATE Insumo SET Estado = :estado WHERE InsumoID = :id",
        [':estado' => $nuevoEstado, ':id' => $insumoId]
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado correctamente'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
