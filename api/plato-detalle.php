<?php
/**
 * API de Detalle de Plato - Obtiene información completa de un plato
 */
require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

$platoId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);
if (!$platoId) {
    Response::error('ID de plato inválido', 400);
}

try {
    $services = getServices();
    $plato = $services->getPlatoDetails($platoId);
    
    if (!$plato) {
        Response::notFound('Plato no encontrado');
    }
    
    $db = getDB();
    
    // Obtener modificadores del plato
    $sqlModificadores = "SELECT mg.GrupoID as grupo_id, mg.Nombre as grupo_nombre,
                               mg.Tipo as grupo_tipo, mg.Obligatorio as grupo_obligatorio,
                               mo.OpcionID as opcion_id, mo.Nombre as opcion_nombre,
                               mo.Precio_Extra as opcion_precio
                        FROM Modificador_Grupo mg
                        LEFT JOIN Modificador_Opcion mo ON mg.GrupoID = mo.GrupoID
                        WHERE mg.PlatoID = :platoId AND mo.Estado = 1
                        ORDER BY mg.GrupoID, mo.Nombre";
    
    $modificadores = $db->fetchAll($sqlModificadores, [':platoId' => $platoId]);
    
    // Agrupar modificadores por grupo
    $gruposModificadores = [];
    foreach ($modificadores as $mod) {
        $grupoId = $mod['grupo_id'];
        if (!isset($gruposModificadores[$grupoId])) {
            $gruposModificadores[$grupoId] = [
                'id' => (int) $mod['grupo_id'],
                'nombre' => $mod['grupo_nombre'],
                'tipo' => $mod['grupo_tipo'],
                'obligatorio' => (bool) $mod['grupo_obligatorio'],
                'opciones' => []
            ];
        }
        
        if ($mod['opcion_id']) {
            $gruposModificadores[$grupoId]['opciones'][] = [
                'id' => (int) $mod['opcion_id'],
                'nombre' => $mod['opcion_nombre'],
                'precio_extra' => (float) $mod['opcion_precio']
            ];
        }
    }
    
    // Procesar resultado final
    $resultado = [
        'id' => (int) $plato['id'],
        'nombre' => $plato['nombre'],
        'descripcion' => $plato['descripcion'] ?? '',
        'imagen' => !empty($plato['imagen']) ? $plato['imagen'] : 'assets/img/platos/default.jpg',
        'categoria' => $plato['categoria'],
        'es_top' => (bool) $plato['top'],
        'es_promo' => (bool) $plato['promo'],
        'variantes' => array_map(function($variante) {
            return [
                'id' => (int) $variante['id'],
                'nombre' => $variante['nombre'],
                'descripcion' => $variante['descripcion'] ?? '',
                'precio' => (float) $variante['precio']
            ];
        }, $plato['variantes']),
        'modificadores' => array_values($gruposModificadores)
    ];
    
    Response::success($resultado, 'Detalle del plato cargado exitosamente');
    
} catch (Exception $e) {
    error_log('API Plato Detalle error: ' . $e->getMessage());
    Response::error('Error al cargar el detalle del plato');
}
?>