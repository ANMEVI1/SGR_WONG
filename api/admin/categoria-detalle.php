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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$categoriaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoriaId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $db = getDB();
    
    // Obtener categoría con contadores
    $categoria = $db->fetchOne(
        "SELECT c.CatID, c.Nombre, c.Descripcion, c.Tipo,
                COUNT(DISTINCT CASE WHEN c.Tipo = 'Plato' THEN p.PlatoID END) as total_platos,
                COUNT(DISTINCT CASE WHEN c.Tipo = 'Insumo' THEN i.InsumoID END) as total_insumos
         FROM Categoria c
         LEFT JOIN Plato p ON c.CatID = p.CatID
         LEFT JOIN Insumo i ON c.CatID = i.CatID
         WHERE c.CatID = :id
         GROUP BY c.CatID",
        [':id' => $categoriaId]
    );
    
    if (!$categoria) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
        exit;
    }
    
    // Obtener elementos según el tipo
    $elementos = [];
    if ($categoria['Tipo'] === 'Plato') {
        // Obtener platos de esta categoría con cantidad de variantes
        $elementos = $db->fetchAll(
            "SELECT p.PlatoID as ID, p.Nombre, p.Estado,
                    COUNT(pv.VarianteID) as total_variantes
             FROM Plato p
             LEFT JOIN Plato_Variante pv ON p.PlatoID = pv.PlatoID
             WHERE p.CatID = :id
             GROUP BY p.PlatoID
             ORDER BY p.Nombre ASC",
            [':id' => $categoriaId]
        );
    } else {
        // Obtener insumos de esta categoría
        $elementos = $db->fetchAll(
            "SELECT InsumoID as ID, Nombre, Stock_Actual, Unidad_Medida
             FROM Insumo
             WHERE CatID = :id
             ORDER BY Nombre ASC",
            [':id' => $categoriaId]
        );
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Categoría cargada exitosamente',
        'categoria' => $categoria,
        'elementos' => $elementos
    ]);
    
} catch (Exception $e) {
    error_log('Error categoria-detalle: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cargar la categoría']);
}
