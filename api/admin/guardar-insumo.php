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

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    $insumoId = isset($input['insumoId']) && $input['insumoId'] !== '' ? (int)$input['insumoId'] : null;
    $nombre = trim($input['nombre'] ?? '');
    $descripcion = trim($input['descripcion'] ?? '');
    $categoriaId = (int)($input['categoriaId'] ?? 0);
    $precioCosto = (float)($input['precioCosto'] ?? 0);
    $stockActual = (float)($input['stockActual'] ?? 0);
    $stockMinimo = (float)($input['stockMinimo'] ?? 0);
    $unidadMedida = trim($input['unidadMedida'] ?? '');

    // Validaciones
    if (empty($nombre)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
        exit;
    }

    if ($categoriaId <= 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Debes seleccionar una categoría']);
        exit;
    }

    if ($precioCosto < 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El precio de costo no puede ser negativo']);
        exit;
    }

    if ($stockActual < 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El stock actual no puede ser negativo']);
        exit;
    }

    if ($stockMinimo < 0) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El stock mínimo no puede ser negativo']);
        exit;
    }

    if (empty($unidadMedida)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La unidad de medida es obligatoria']);
        exit;
    }

    // Verificar que la categoría exista y sea de tipo Insumo
    $categoria = $db->fetchOne(
        "SELECT CatID FROM Categoria WHERE CatID = :id AND Tipo = 'Insumo'",
        [':id' => $categoriaId]
    );

    if (!$categoria) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Categoría inválida o no es de tipo Insumo']);
        exit;
    }

    // Validar que no exista otro insumo con el mismo nombre (excepto el actual si es edición)
    if ($insumoId) {
        $existe = $db->fetchOne(
            "SELECT InsumoID FROM Insumo WHERE Nombre = :nombre AND InsumoID != :id",
            [':nombre' => $nombre, ':id' => $insumoId]
        );
    } else {
        $existe = $db->fetchOne(
            "SELECT InsumoID FROM Insumo WHERE Nombre = :nombre",
            [':nombre' => $nombre]
        );
    }

    if ($existe) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un insumo con ese nombre']);
        exit;
    }

    // Guardar insumo
    if ($insumoId) {
        // Actualizar
        $db->execute(
            "UPDATE Insumo 
             SET Nombre = :nombre, 
                 Descripcion = :desc, 
                 CatID = :catId,
                 Precio_Costo = :precio,
                 Stock_Actual = :stockActual,
                 Stock_Minimo = :stockMinimo,
                 Unidad_Medida = :unidad
             WHERE InsumoID = :id",
            [
                ':nombre' => $nombre,
                ':desc' => $descripcion,
                ':catId' => $categoriaId,
                ':precio' => $precioCosto,
                ':stockActual' => $stockActual,
                ':stockMinimo' => $stockMinimo,
                ':unidad' => $unidadMedida,
                ':id' => $insumoId
            ]
        );
    } else {
        // Crear
        $db->execute(
            "INSERT INTO Insumo (Nombre, Descripcion, CatID, Precio_Costo, Stock_Actual, Stock_Minimo, Unidad_Medida, Estado) 
             VALUES (:nombre, :desc, :catId, :precio, :stockActual, :stockMinimo, :unidad, 'Activo')",
            [
                ':nombre' => $nombre,
                ':desc' => $descripcion,
                ':catId' => $categoriaId,
                ':precio' => $precioCosto,
                ':stockActual' => $stockActual,
                ':stockMinimo' => $stockMinimo,
                ':unidad' => $unidadMedida
            ]
        );
    }

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $insumoId ? 'Insumo actualizado correctamente' : 'Insumo creado correctamente'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
