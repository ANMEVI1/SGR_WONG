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

    // Verificar que sea administrador
    $userRole = $db->fetchOne(
        "SELECT TipUsuID FROM Usuario WHERE UsuarioID = :userId",
        [':userId' => $currentUser['UsuarioID']]
    );

    if ($userRole['TipUsuID'] != 1) {
        ob_end_clean();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['elementoId']) || !is_numeric($input['elementoId'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de elemento inválido']);
        exit;
    }

    if (!isset($input['tipo']) || !in_array($input['tipo'], ['Plato', 'Insumo'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
        exit;
    }

    if (!isset($input['nuevaCategoriaId']) || !is_numeric($input['nuevaCategoriaId'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de categoría inválido']);
        exit;
    }

    $elementoId = (int)$input['elementoId'];
    $tipo = $input['tipo'];
    $nuevaCategoriaId = (int)$input['nuevaCategoriaId'];

    // Verificar que la categoría existe y es del tipo correcto
    $categoria = $db->fetchOne(
        "SELECT CatID, Tipo FROM Categoria WHERE CatID = :id",
        [':id' => $nuevaCategoriaId]
    );

    if (!$categoria) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
        exit;
    }

    if ($categoria['Tipo'] !== $tipo) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La categoría no es del tipo correcto']);
        exit;
    }

    // Actualizar la categoría según el tipo
    if ($tipo === 'Plato') {
        // Verificar que el plato existe
        $plato = $db->fetchOne(
            "SELECT PlatoID FROM Plato WHERE PlatoID = :id",
            [':id' => $elementoId]
        );

        if (!$plato) {
            ob_end_clean();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Plato no encontrado']);
            exit;
        }

        // Actualizar categoría del plato
        $db->query(
            "UPDATE Plato SET CatID = :catId WHERE PlatoID = :id",
            [':catId' => $nuevaCategoriaId, ':id' => $elementoId]
        );
    } else {
        // Verificar que el insumo existe
        $insumo = $db->fetchOne(
            "SELECT InsumoID FROM Insumo WHERE InsumoID = :id",
            [':id' => $elementoId]
        );

        if (!$insumo) {
            ob_end_clean();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Insumo no encontrado']);
            exit;
        }

        // Actualizar categoría del insumo
        $db->query(
            "UPDATE Insumo SET CatID = :catId WHERE InsumoID = :id",
            [':catId' => $nuevaCategoriaId, ':id' => $elementoId]
        );
    }

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Categoría actualizada correctamente'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
