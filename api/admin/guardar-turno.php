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

    if (!$input) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    $turnoId = isset($input['turnoId']) && $input['turnoId'] !== '' ? (int)$input['turnoId'] : null;
    $descripcion = trim($input['descripcion'] ?? '');
    $horaInicio = trim($input['horaInicio'] ?? '');
    $horaFin = trim($input['horaFin'] ?? '');

    // Validaciones
    if (empty($descripcion)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La descripción es obligatoria']);
        exit;
    }

    if (empty($horaInicio) || empty($horaFin)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Las horas de inicio y fin son obligatorias']);
        exit;
    }

    // Validar que hora fin sea mayor que hora inicio
    if ($horaInicio >= $horaFin) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La hora de fin debe ser mayor que la hora de inicio']);
        exit;
    }

    // Validar que no exista otro turno con la misma descripción (excepto el actual si es edición)
    if ($turnoId) {
        $existe = $db->fetchOne(
            "SELECT TurnoID FROM Turno WHERE Descripcion = :desc AND TurnoID != :id",
            [':desc' => $descripcion, ':id' => $turnoId]
        );
    } else {
        $existe = $db->fetchOne(
            "SELECT TurnoID FROM Turno WHERE Descripcion = :desc",
            [':desc' => $descripcion]
        );
    }

    if ($existe) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ya existe un turno con ese nombre']);
        exit;
    }

    // Guardar turno
    if ($turnoId) {
        // Actualizar
        $db->query(
            "UPDATE Turno 
             SET Descripcion = :desc, Hora_Inicio = :inicio, Hora_Fin = :fin
             WHERE TurnoID = :id",
            [
                ':desc' => $descripcion,
                ':inicio' => $horaInicio,
                ':fin' => $horaFin,
                ':id' => $turnoId
            ]
        );
    } else {
        // Crear
        $db->query(
            "INSERT INTO Turno (Descripcion, Hora_Inicio, Hora_Fin) 
             VALUES (:desc, :inicio, :fin)",
            [
                ':desc' => $descripcion,
                ':inicio' => $horaInicio,
                ':fin' => $horaFin
            ]
        );
    }

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $turnoId ? 'Turno actualizado correctamente' : 'Turno creado correctamente'
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
