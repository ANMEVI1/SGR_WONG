<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json');

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Verificar permisos de administrador
$userRole = $db->fetchOne(
    "SELECT TipUsuID FROM Usuario WHERE UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

if ($userRole['TipUsuID'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$usuarioId = $input['usuarioId'] ?? null;
$nuevoEstado = $input['nuevoEstado'] ?? null;

if (!$usuarioId || !is_numeric($usuarioId) || !in_array($nuevoEstado, ['Activo', 'Inactivo'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Verificar que el usuario existe y es tipo cliente web
    $usuario = $db->fetchOne(
        "SELECT UsuarioID FROM Usuario WHERE UsuarioID = :userId AND TipUsuID = 5",
        [':userId' => $usuarioId]
    );
    
    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }
    
    // Actualizar estado
    $affected = $db->execute(
        "UPDATE Usuario SET Estado = :estado WHERE UsuarioID = :userId",
        [':estado' => $nuevoEstado, ':userId' => $usuarioId]
    );
    
    if ($affected === 0) {
        throw new Exception('No se pudo actualizar el estado');
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado actualizado correctamente'
    ]);

} catch (Exception $e) {
    $db->rollback();
    error_log("Error en toggle-usuario.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}