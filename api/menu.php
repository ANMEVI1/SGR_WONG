<?php
/**
 * API del Menú - Obtiene platos desde la base de datos
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

// Obtener y validar parámetros
$filtro = Validator::sanitize($_GET['filtro'] ?? 'todos');
$validFilters = ['todos', 'top', 'promo'];

// Si no es un filtro válido, asumir que es una categoría
if (!in_array($filtro, $validFilters)) {
    $categoria = $filtro;
    $filtro = 'categoria';
}

try {
    $db = getDB();
    
    // Construir consulta base
    $baseWhere = "pl.Estado = 'Disponible' AND pv.Estado = 1 AND cat.Tipo = 'Plato'";
    $params = [];
    
    // Aplicar filtros
    switch ($filtro) {
        case 'top':
            $where = $baseWhere . " AND pl.Es_Top = 1";
            break;
        case 'promo':
            $where = $baseWhere . " AND pl.Es_Promo = 1";
            break;
        case 'categoria':
            $where = $baseWhere . " AND cat.Nombre = :categoria";
            $params[':categoria'] = $categoria;
            break;
        default: // 'todos'
            $where = $baseWhere;
            break;
    }
    
    // Consulta optimizada
    $sql = "SELECT
                pl.PlatoID      AS id,
                pl.Nombre       AS nombre,
                pl.Descripcion  AS descripcion,
                pl.Imagen_URL   AS imagen,
                pl.Es_Top       AS top,
                pl.Es_Promo     AS promo,
                cat.Nombre      AS categoria,
                MIN(pv.Precio_Venta) AS precio_min,
                MAX(pv.Precio_Venta) AS precio_max,
                COUNT(pv.PlatoVarianteID) AS variantes
            FROM Plato pl
            JOIN Categoria cat ON pl.CatID = cat.CatID
            JOIN Plato_Variante pv ON pl.PlatoID = pv.PlatoID
            WHERE {$where}
            GROUP BY pl.PlatoID, pl.Nombre, pl.Descripcion, pl.Imagen_URL,
                     pl.Es_Top, pl.Es_Promo, cat.Nombre
            ORDER BY pl.Orden ASC, pl.Nombre ASC";
    
    $platos = $db->fetchAll($sql, $params);
    
    // Procesar resultados
    $platosProcessed = array_map(function($plato) {
        return [
            'id' => (int) $plato['id'],
            'nombre' => $plato['nombre'],
            'descripcion' => $plato['descripcion'] ?? '',
            'imagen' => !empty($plato['imagen']) ? $plato['imagen'] : 'assets/img/platos/default.jpg',
            'top' => (bool) $plato['top'],
            'promo' => (bool) $plato['promo'],
            'categoria' => $plato['categoria'],
            'precio' => (float) $plato['precio_min'],
            'precio_max' => (float) $plato['precio_max'],
            'tiene_variantes' => (int) $plato['variantes'] > 1
        ];
    }, $platos);
    
    Response::success($platosProcessed, 'Menú cargado exitosamente');
    
} catch (Exception $e) {
    error_log('API Menu error: ' . $e->getMessage());
    Response::error('Error al cargar el menú');
}