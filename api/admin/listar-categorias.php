<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    startSecureSession();
    requireAuth();
    requirePermission('backoffice');

    $db = getDB();

    // Obtener tipo de categoría (Plato o Insumo)
    $tipo = isset($_GET['tipo']) && in_array($_GET['tipo'], ['Plato', 'Insumo']) ? $_GET['tipo'] : null;

    if (!$tipo) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tipo de categoría inválido']);
        exit;
    }

    // Obtener categorías del tipo especificado
    $categorias = $db->fetchAll(
        "SELECT CatID, Nombre, Descripcion
         FROM Categoria
         WHERE Tipo = :tipo
         ORDER BY Nombre ASC",
        [':tipo' => $tipo]
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'categorias' => $categorias
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
