<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    startSecureSession();
    requireAuth();
    requirePermission('backoffice');

    $db = getDB();

    // Obtener todos los turnos
    $turnos = $db->fetchAll(
        "SELECT TurnoID, Descripcion, Hora_Inicio, Hora_Fin
         FROM Turno
         ORDER BY Hora_Inicio ASC"
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'turnos' => $turnos
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
