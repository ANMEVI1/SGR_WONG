<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once '../../config/conexion.php';
startSecureSession();
requireAuth();
requirePermission('backoffice');

ob_end_clean();
ob_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDB();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $rolId = isset($input['rolId']) && $input['rolId'] !== '' ? (int)$input['rolId'] : null;
    $nombre = trim($input['nombre'] ?? '');
    $descripcion = trim($input['descripcion'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El nombre del rol es obligatorio']);
        exit;
    }
    
    if (empty($descripcion)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La descripción es obligatoria']);
        exit;
    }
    
    // Verificar nombre duplicado
    if ($rolId) {
        $existe = $db->fetchOne(
            "SELECT RolID FROM Tipo_Rol WHERE Nombre = :nombre AND RolID != :id",
            [':nombre' => $nombre, ':id' => $rolId]
        );
    } else {
        $existe = $db->fetchOne(
            "SELECT RolID FROM Tipo_Rol WHERE Nombre = :nombre",
            [':nombre' => $nombre]
        );
    }
    
    if ($existe) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un rol con ese nombre']);
        exit;
    }
    
    // Actualizar o insertar
    if ($rolId) {
        // Actualizar
        $db->execute(
            "UPDATE Tipo_Rol 
             SET Nombre = :nombre, Descripcion = :descripcion
             WHERE RolID = :id",
            [
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':id' => $rolId
            ]
        );
    } else {
        // Insertar
        $db->execute(
            "INSERT INTO Tipo_Rol (Nombre, Descripcion) 
             VALUES (:nombre, :descripcion)",
            [
                ':nombre' => $nombre,
                ':descripcion' => $descripcion
            ]
        );
    }
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Rol guardado correctamente']);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
