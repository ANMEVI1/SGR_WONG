<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once '../../../config/conexion.php';
startSecureSession();
requireAuth();
requirePermission('backoffice');

ob_end_clean();
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDB();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['metodoId']) || !isset($input['estado'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $metodoId = (int)$input['metodoId'];
    $estado = (int)$input['estado'];
    
    // Validar que el método existe
    $metodo = $db->fetchOne(
        "SELECT MetPagID FROM Metodo_Pago WHERE MetPagID = :id",
        [':id' => $metodoId]
    );
    
    if (!$metodo) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Método de pago no encontrado']);
        exit;
    }
    
    // Actualizar estado
    $db->execute(
        "UPDATE Metodo_Pago SET Estado = :estado WHERE MetPagID = :id",
        [':estado' => $estado, ':id' => $metodoId]
    );
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
