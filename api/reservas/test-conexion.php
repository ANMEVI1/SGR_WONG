<?php
/**
 * Test: Verificar conexión a base de datos
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    require_once '../../config/Database.php';
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Probar consulta simple
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result['test'] === 1) {
        echo json_encode([
            'success' => true,
            'message' => '✅ Conexión a base de datos exitosa',
            'database' => 'db_restaurante',
            'host' => 'localhost'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Query test falló');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '❌ Error de conexión',
        'error' => $e->getMessage(),
        'ayuda' => 'Verifica que XAMPP esté corriendo y que exista la BD db_restaurante'
    ], JSON_UNESCAPED_UNICODE);
}
