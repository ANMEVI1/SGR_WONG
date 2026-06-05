<?php
/**
 * API: Listar platos para selector de reservas
 * Retorna lista simple de platos con variantes para pre-ordenar
 */

require_once '../../config/Database.php';
require_once '../../config/Response.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = Database::getInstance();
    
    // Obtener platos con sus variantes más populares
    $sql = "
        SELECT 
            pv.VarianteID AS variante_id,
            pl.Nombre AS nombre,
            pv.Nombre AS variante,
            pl.Descripcion AS descripcion,
            pv.Precio_Venta AS precio,
            cat.Nombre AS categoria,
            pl.Imagen_URL AS imagen
        FROM Plato_Variante pv
        JOIN Plato pl ON pv.PlatoID = pl.PlatoID
        JOIN Categoria cat ON pl.CatID = cat.CatID
        WHERE pl.Estado = 'Disponible'
          AND pv.Estado = 1
          AND cat.Tipo = 'Plato'
        ORDER BY cat.Nombre, pl.Orden, pl.Nombre, pv.Precio_Venta
        LIMIT 50
    ";
    
    $platos = $db->fetchAll($sql);
    
    // Procesar resultados
    $resultado = array_map(function($p) {
        return [
            'variante_id' => (int)$p['variante_id'],
            'nombre' => $p['nombre'] . ($p['variante'] !== 'Regular' ? ' (' . $p['variante'] . ')' : ''),
            'descripcion' => $p['descripcion'] ?? '',
            'precio' => number_format((float)$p['precio'], 2, '.', ''),
            'categoria' => $p['categoria']
        ];
    }, $platos);
    
    Response::success($resultado, 'Platos cargados');
    
} catch (Exception $e) {
    error_log('Error listando platos: ' . $e->getMessage());
    Response::error('Error al cargar platos', 500);
}
