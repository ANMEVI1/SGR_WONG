<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

startSecureSession();

// Verificar autenticación
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

// Leer JSON del body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

// Validar datos
$categoriaId = isset($data['categoriaId']) ? (int)$data['categoriaId'] : 0;
$nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
$descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
$tipo = isset($data['tipo']) ? trim($data['tipo']) : '';

if (empty($nombre)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
    exit;
}

if (!in_array($tipo, ['Plato', 'Insumo'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tipo inválido. Debe ser Plato o Insumo']);
    exit;
}

try {
    $db = getDB();
    
    if ($categoriaId > 0) {
        // EDITAR categoría existente
        
        // Verificar que existe
        $existe = $db->fetchOne(
            "SELECT CatID FROM Categoria WHERE CatID = :id",
            [':id' => $categoriaId]
        );
        
        if (!$existe) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            exit;
        }
        
        // Verificar nombre duplicado (excepto la misma categoría)
        $duplicado = $db->fetchOne(
            "SELECT CatID FROM Categoria WHERE Nombre = :nombre AND CatID != :id",
            [':nombre' => $nombre, ':id' => $categoriaId]
        );
        
        if ($duplicado) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre']);
            exit;
        }
        
        // Actualizar
        $db->execute(
            "UPDATE Categoria 
             SET Nombre = :nombre, Descripcion = :descripcion, Tipo = :tipo
             WHERE CatID = :id",
            [
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':tipo' => $tipo,
                ':id' => $categoriaId
            ]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Categoría actualizada correctamente',
            'categoriaId' => $categoriaId
        ]);
        
    } else {
        // CREAR nueva categoría
        
        // Verificar nombre duplicado
        $duplicado = $db->fetchOne(
            "SELECT CatID FROM Categoria WHERE Nombre = :nombre",
            [':nombre' => $nombre]
        );
        
        if ($duplicado) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre']);
            exit;
        }
        
        // Insertar
        $db->execute(
            "INSERT INTO Categoria (Nombre, Descripcion, Tipo) 
             VALUES (:nombre, :descripcion, :tipo)",
            [
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':tipo' => $tipo
            ]
        );
        
        $nuevoCategoriaId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Categoría creada correctamente',
            'categoriaId' => $nuevoCategoriaId
        ]);
    }
    
} catch (Exception $e) {
    error_log('Error guardar-categoria: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar la categoría: ' . $e->getMessage()]);
}
