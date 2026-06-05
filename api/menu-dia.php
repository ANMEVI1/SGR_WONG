<?php
/**
 * API del Menú del Día - Combos económicos 12pm-4pm
 */
require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    $db = getDB();
    
    // Consultar menús disponibles con sus componentes
    $sql = "SELECT 
                md.MenuDiaID AS id,
                md.Nombre AS nombre,
                md.Descripcion AS descripcion,
                md.Precio AS precio,
                md.Estado AS estado,
                md.Hora_Inicio AS hora_inicio,
                md.Hora_Fin AS hora_fin,
                md.Imagen_URL AS imagen,
                md.Dias_Activo AS dias_activo,
                md.Orden AS orden
            FROM Menu_Dia md
            WHERE md.Estado = 'Disponible'
            ORDER BY md.Orden ASC, md.Nombre ASC";
    
    $menus = $db->fetchAll($sql);
    
    if (empty($menus)) {
        Response::success([], 'No hay menús del día disponibles');
    }
    
    // Cargar componentes de cada menú
    foreach ($menus as &$menu) {
        $componentesSql = "SELECT 
                c.ComponenteID AS id,
                c.Tipo AS tipo,
                c.Descripcion AS descripcion,
                c.Orden AS orden,
                p.Nombre AS plato_nombre,
                p.Imagen_URL AS plato_imagen
            FROM Menu_Dia_Componente c
            LEFT JOIN Plato p ON c.PlatoID = p.PlatoID
            WHERE c.MenuDiaID = :menuId
            ORDER BY c.Orden ASC";
        
        $componentes = $db->fetchAll($componentesSql, [':menuId' => $menu['id']]);
        $menu['componentes'] = $componentes;
        
        // Procesar imagen
        $menu['imagen'] = !empty($menu['imagen']) ? $menu['imagen'] : 'assets/img/menu-dia/default.jpg';
        $menu['precio'] = (float) $menu['precio'];
    }
    
    Response::success($menus, 'Menús del día cargados exitosamente');
    
} catch (Exception $e) {
    error_log('API Menu Dia error: ' . $e->getMessage());
    Response::error('Error al cargar los menús del día');
}
