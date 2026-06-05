<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require_once '../../../config/conexion.php';
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
    
    $metodoId = isset($input['metodoId']) && $input['metodoId'] !== '' ? (int)$input['metodoId'] : null;
    $nombre = trim($input['nombre'] ?? '');
    $icono = trim($input['icono'] ?? '');
    $requiereReferencia = isset($input['requiereReferencia']) ? (int)$input['requiereReferencia'] : 0;
    $estado = isset($input['estado']) ? (int)$input['estado'] : 1;
    
    // Validaciones
    if (empty($nombre)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El nombre del método es obligatorio']);
        exit;
    }
    
    // Verificar nombre duplicado
    if ($metodoId) {
        $existe = $db->fetchOne(
            "SELECT MetPagID FROM Metodo_Pago WHERE Nombre = :nombre AND MetPagID != :id",
            [':nombre' => $nombre, ':id' => $metodoId]
        );
    } else {
        $existe = $db->fetchOne(
            "SELECT MetPagID FROM Metodo_Pago WHERE Nombre = :nombre",
            [':nombre' => $nombre]
        );
    }
    
    if ($existe) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un método de pago con ese nombre']);
        exit;
    }
    
    // Actualizar o insertar
    if ($metodoId) {
        // Actualizar
        $db->execute(
            "UPDATE Metodo_Pago 
             SET Nombre = :nombre, 
                 Icono = :icono, 
                 Requiere_Referencia = :requiere, 
                 Estado = :estado
             WHERE MetPagID = :id",
            [
                ':nombre' => $nombre,
                ':icono' => $icono ?: null,
                ':requiere' => $requiereReferencia,
                ':estado' => $estado,
                ':id' => $metodoId
            ]
        );
    } else {
        // Insertar
        $db->execute(
            "INSERT INTO Metodo_Pago (Nombre, Icono, Requiere_Referencia, Estado) 
             VALUES (:nombre, :icono, :requiere, :estado)",
            [
                ':nombre' => $nombre,
                ':icono' => $icono ?: null,
                ':requiere' => $requiereReferencia,
                ':estado' => $estado
            ]
        );
    }
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Método de pago guardado correctamente']);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
