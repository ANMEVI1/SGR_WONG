<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    startSecureSession();
    requireAuth();
    requirePermission('backoffice');

    $db = getDB();

    // Obtener todos los roles
    $roles = $db->fetchAll(
        "SELECT RolID, Nombre, Descripcion
         FROM Tipo_Rol
         ORDER BY Nombre ASC"
    );

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'roles' => $roles
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
