<?php
/**
 * Test: Verificar configuración de reservas en BD
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    require_once '../../config/Database.php';
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar si existe la tabla Reserva_Config
    $stmt = $conn->query("SHOW TABLES LIKE 'Reserva_Config'");
    if ($stmt->rowCount() === 0) {
        throw new Exception('La tabla Reserva_Config no existe. Ejecuta el script SQL de módulo de reservas.');
    }
    
    // Verificar si existe la tabla Reserva
    $stmt = $conn->query("SHOW TABLES LIKE 'Reserva'");
    if ($stmt->rowCount() === 0) {
        throw new Exception('La tabla Reserva no existe. Ejecuta el script SQL de módulo de reservas.');
    }
    
    // Verificar si existe la tabla Reserva_Historial
    $stmt = $conn->query("SHOW TABLES LIKE 'Reserva_Historial'");
    if ($stmt->rowCount() === 0) {
        throw new Exception('La tabla Reserva_Historial no existe. Ejecuta el script SQL de módulo de reservas.');
    }
    
    // Obtener configuración
    $config = $db->fetchOne("SELECT * FROM Reserva_Config WHERE Estado = 1 LIMIT 1");
    
    if (!$config) {
        throw new Exception('No existe configuración activa en Reserva_Config. Ejecuta el INSERT del seed.');
    }
    
    echo json_encode([
        'success' => true,
        'message' => '✅ Configuración de reservas OK',
        'config' => [
            'Anticipación mínima' => $config['Anticipacion_Min_Hrs'] . ' horas',
            'Anticipación máxima' => $config['Anticipacion_Max_Dias'] . ' días',
            'Tolerancia' => $config['Tolerancia_Min'] . ' minutos',
            'Duración estimada' => $config['Duracion_Estimada_Min'] . ' minutos',
            'Máx. comensales' => $config['Max_Comensales'],
            'Requiere confirmación' => $config['Requiere_Confirmacion'] ? 'Sí' : 'No'
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '❌ Error en configuración',
        'error' => $e->getMessage(),
        'ayuda' => 'Asegúrate de haber ejecutado el script SQL completo del módulo de reservas'
    ], JSON_UNESCAPED_UNICODE);
}
