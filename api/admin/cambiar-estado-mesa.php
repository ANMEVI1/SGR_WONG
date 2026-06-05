<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

startSecureSession();

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_scope'] ?? '') !== 'backoffice') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

$mesaId = isset($data['mesaId']) ? (int)$data['mesaId'] : 0;
$estado = isset($data['estado']) ? trim($data['estado']) : '';

if ($mesaId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

if (!in_array($estado, ['Disponible', 'Ocupada', 'Reservada', 'Mantenimiento'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $db = getDB();
    
    $existe = $db->fetchOne(
        "SELECT MesaID FROM Mesa WHERE MesaID = :id",
        [':id' => $mesaId]
    );
    
    if (!$existe) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Mesa no encontrada']);
        exit;
    }
    
    $db->execute(
        "UPDATE Mesa SET Estado = :estado WHERE MesaID = :id",
        [':estado' => $estado, ':id' => $mesaId]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado correctamente'
    ]);
    
} catch (Exception $e) {
    error_log('Error cambiar-estado-mesa: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cambiar estado']);
}
