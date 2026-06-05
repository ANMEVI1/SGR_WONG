<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    startSecureSession();
    requireAuth();
    requirePermission('backoffice');

    $currentUser = getCurrentUser();
    $db = getDB();

    // Verificar que sea administrador o almacenero
    $userRole = $db->fetchOne(
        "SELECT TipUsuID FROM Usuario WHERE UsuarioID = :userId",
        [':userId' => $currentUser['UsuarioID']]
    );

    if (!in_array($userRole['TipUsuID'], [1, 4])) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit;
    }

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de insumo inválido']);
        exit;
    }

    $insumoId = (int)$_GET['id'];

    // Obtener datos del insumo
    $insumo = $db->fetchOne(
        "SELECT i.InsumoID, i.Nombre, i.Descripcion, i.Precio_Costo, 
                i.Stock_Actual, i.Stock_Minimo, i.Unidad_Medida, i.Estado, i.CatID,
                c.Nombre as Categoria
         FROM Insumo i
         JOIN Categoria c ON i.CatID = c.CatID
         WHERE i.InsumoID = :id",
        [':id' => $insumoId]
    );

    if (!$insumo) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Insumo no encontrado']);
        exit;
    }

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'insumo' => $insumo
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
