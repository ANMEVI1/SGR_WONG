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
    
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de método inválido']);
        exit;
    }
    
    $metodoId = (int)$_GET['id'];
    
    // Obtener detalles básicos del método
    $metodo = $db->fetchOne(
        "SELECT MetPagID, Nombre, Icono, Requiere_Referencia, Estado
         FROM Metodo_Pago
         WHERE MetPagID = :id",
        [':id' => $metodoId]
    );
    
    if (!$metodo) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Método de pago no encontrado']);
        exit;
    }
    
    // Obtener estadísticas de uso
    $stats = $db->fetchOne(
        "SELECT COUNT(DISTINCT ComPagID) as total_usos,
                COALESCE(SUM(Total), 0) as total_ventas
         FROM Comprobante_Pago
         WHERE MetPagID = :id",
        [':id' => $metodoId]
    );
    
    $metodo['total_usos'] = $stats['total_usos'] ?? 0;
    $metodo['total_ventas'] = $stats['total_ventas'] ?? 0;
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'metodo' => $metodo
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
