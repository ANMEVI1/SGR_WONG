<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

startSecureSession();

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_scope'] ?? '') !== 'backoffice') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$mesaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($mesaId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $db = getDB();
    
    $mesa = $db->fetchOne(
        "SELECT m.MesaID, m.Codigo_Mesa, m.Numero_Mesa, m.Capacidad, m.Estado,
                COUNT(DISTINCT p.PedidoID) as total_pedidos,
                MAX(p.Fecha_Hora) as ultimo_pedido
         FROM Mesa m
         LEFT JOIN Pedido p ON m.MesaID = p.MesaID AND p.Estado NOT IN ('Pagado', 'Cancelado')
         WHERE m.MesaID = :id
         GROUP BY m.MesaID",
        [':id' => $mesaId]
    );
    
    if (!$mesa) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Mesa no encontrada']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Mesa cargada exitosamente',
        'mesa' => $mesa
    ]);
    
} catch (Exception $e) {
    error_log('Error mesa-detalle: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar la mesa']);
}
