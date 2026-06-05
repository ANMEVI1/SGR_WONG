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
$codigoMesa = isset($data['codigoMesa']) ? trim($data['codigoMesa']) : '';
$numeroMesa = isset($data['numeroMesa']) ? (int)$data['numeroMesa'] : 0;
$capacidad = isset($data['capacidad']) ? (int)$data['capacidad'] : 0;
$estado = isset($data['estado']) ? trim($data['estado']) : 'Disponible';

if (empty($codigoMesa)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El código de mesa es obligatorio']);
    exit;
}

if ($numeroMesa <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El número de mesa debe ser mayor a 0']);
    exit;
}

if ($capacidad <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La capacidad debe ser mayor a 0']);
    exit;
}

if (!in_array($estado, ['Disponible', 'Ocupada', 'Reservada', 'Mantenimiento'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $db = getDB();
    
    if ($mesaId > 0) {
        // EDITAR
        $existe = $db->fetchOne(
            "SELECT MesaID FROM Mesa WHERE MesaID = :id",
            [':id' => $mesaId]
        );
        
        if (!$existe) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Mesa no encontrada']);
            exit;
        }
        
        // Verificar código duplicado
        $duplicado = $db->fetchOne(
            "SELECT MesaID FROM Mesa WHERE Codigo_Mesa = :codigo AND MesaID != :id",
            [':codigo' => $codigoMesa, ':id' => $mesaId]
        );
        
        if ($duplicado) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ya existe una mesa con ese código']);
            exit;
        }
        
        $db->execute(
            "UPDATE Mesa 
             SET Codigo_Mesa = :codigo, Numero_Mesa = :numero, Capacidad = :capacidad, Estado = :estado
             WHERE MesaID = :id",
            [
                ':codigo' => $codigoMesa,
                ':numero' => $numeroMesa,
                ':capacidad' => $capacidad,
                ':estado' => $estado,
                ':id' => $mesaId
            ]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Mesa actualizada correctamente',
            'mesaId' => $mesaId
        ]);
        
    } else {
        // CREAR
        $duplicado = $db->fetchOne(
            "SELECT MesaID FROM Mesa WHERE Codigo_Mesa = :codigo",
            [':codigo' => $codigoMesa]
        );
        
        if ($duplicado) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ya existe una mesa con ese código']);
            exit;
        }
        
        $db->execute(
            "INSERT INTO Mesa (Codigo_Mesa, Numero_Mesa, Capacidad, Estado) 
             VALUES (:codigo, :numero, :capacidad, :estado)",
            [
                ':codigo' => $codigoMesa,
                ':numero' => $numeroMesa,
                ':capacidad' => $capacidad,
                ':estado' => $estado
            ]
        );
        
        $nuevoMesaId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Mesa creada correctamente',
            'mesaId' => $nuevoMesaId
        ]);
    }
    
} catch (Exception $e) {
    error_log('Error guardar-mesa: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar la mesa: ' . $e->getMessage()]);
}
