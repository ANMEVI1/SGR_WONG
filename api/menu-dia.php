<?php
/**
 * API Menú del Día - Obtiene platos con variante "Personal"
 * Estrategia: Filtrar por nombre de variante que contenga "Personal"
 */
require_once __DIR__ . '/../config/conexion.php';

// Configurar headers para API
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    $db = getDB();
    
    // Consulta: Solo variantes "Personal" activas de platos disponibles
    $sql = "SELECT
                pl.PlatoID      AS id,
                pl.Nombre       AS nombre,
                pl.Descripcion  AS descripcion,
                pl.Imagen_URL   AS imagen,
                cat.Nombre      AS categoria,
                pv.Nombre       AS variante,
                pv.Precio_Venta AS precio
            FROM Plato pl
            JOIN Categoria cat ON pl.CatID = cat.CatID
            JOIN Plato_Variante pv ON pl.PlatoID = pv.PlatoID
            WHERE pl.Estado = 'Disponible'
              AND pv.Estado = 1
              AND cat.Tipo = 'Plato'
              AND pv.Nombre LIKE '%Personal%'
            ORDER BY pl.Orden ASC, pl.Nombre ASC";
    
    $platos = $db->fetchAll($sql);
    
    // Procesar resultados
    $platosProcessed = array_map(function($plato) {
        $imagen = !empty($plato['imagen']) ? $plato['imagen'] : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23f0ece4' width='400' height='300'/%3E%3Ctext fill='%23c9954a' font-size='18' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3ESin imagen%3C/text%3E%3C/svg%3E";
        
        return [
            'id' => (int) $plato['id'],
            'nombre' => $plato['nombre'],
            'descripcion' => $plato['descripcion'] ?? '',
            'imagen' => $imagen,
            'categoria' => $plato['categoria'],
            'variante' => $plato['variante'],
            'precio' => (float) $plato['precio']
        ];
    }, $platos);
    
    Response::success($platosProcessed, 'Menú del día cargado exitosamente');
    
} catch (Exception $e) {
    error_log('API Menu Día error: ' . $e->getMessage());
    Response::error('Error al cargar el menú del día');
}
