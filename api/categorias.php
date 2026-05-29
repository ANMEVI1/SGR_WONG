<?php
/**
 * API de Categorías - Obtiene categorías de platos disponibles
 */
require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300'); // Cache 5 minutos
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    $db = getDB();
    
    $sql = "SELECT DISTINCT cat.CatID as id, cat.Nombre as nombre, cat.Descripcion as descripcion,
                   COUNT(pl.PlatoID) as total_platos
            FROM Categoria cat
            JOIN Plato pl ON cat.CatID = pl.CatID
            WHERE cat.Tipo = 'Plato' 
              AND pl.Estado = 'Disponible'
            GROUP BY cat.CatID, cat.Nombre, cat.Descripcion
            ORDER BY cat.Nombre ASC";
    
    $categorias = $db->fetchAll($sql);
    
    // Procesar resultados
    $categoriasProcessed = array_map(function($cat) {
        return [
            'id' => (int) $cat['id'],
            'nombre' => $cat['nombre'],
            'descripcion' => $cat['descripcion'] ?? '',
            'total_platos' => (int) $cat['total_platos']
        ];
    }, $categorias);
    
    Response::success($categoriasProcessed, 'Categorías cargadas exitosamente');
    
} catch (Exception $e) {
    error_log('API Categorías error: ' . $e->getMessage());
    Response::error('Error al cargar las categorías');
}
?>