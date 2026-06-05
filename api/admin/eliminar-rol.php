<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once '../../config/conexion.php';
startSecureSession();
requireAuth();
requirePermission('backoffice');

ob_end_clean();
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDB();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['rolId'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $rolId = (int)$input['rolId'];
    
    // Verificar que el rol existe
    $rol = $db->fetchOne(
        "SELECT RolID FROM Tipo_Rol WHERE RolID = :id",
        [':id' => $rolId]
    );
    
    if (!$rol) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
        exit;
    }
    
    // Verificar que no tenga empleados asignados
    $empleados = $db->fetchOne(
        "SELECT COUNT(*) as total FROM Empleado WHERE RolID = :id",
        [':id' => $rolId]
    );
    
    if ($empleados['total'] > 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar un rol con empleados asignados']);
        exit;
    }
    
    // Eliminar el rol
    $db->execute(
        "DELETE FROM Tipo_Rol WHERE RolID = :id",
        [':id' => $rolId]
    );
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Rol eliminado correctamente']);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
