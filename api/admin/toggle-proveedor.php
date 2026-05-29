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
$proveedorId = $input['proveedorId'] ?? null;
$nuevoEstado = $input['nuevoEstado'] ?? null;

if (!$proveedorId || !is_numeric($proveedorId) || !in_array($nuevoEstado, ['A', 'I'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Verificar que el proveedor existe
    $proveedor = $db->fetchOne(
        "SELECT ProveedorID FROM Proveedor WHERE ProveedorID = :proveedorId",
        [':proveedorId' => $proveedorId]
    );
    
    if (!$proveedor) {
        throw new Exception('Proveedor no encontrado');
    }
    
    // Actualizar estado
    $affected = $db->execute(
        "UPDATE Proveedor SET Estado = :estado WHERE ProveedorID = :proveedorId",
        [':estado' => $nuevoEstado, ':proveedorId' => $proveedorId]
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
    error_log("Error en toggle-proveedor.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}