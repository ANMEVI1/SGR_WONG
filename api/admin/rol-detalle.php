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
    
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de rol inválido']);
        exit;
    }
    
    $rolId = (int)$_GET['id'];
    
    // Obtener detalles del rol con cantidad de empleados
    $rol = $db->fetchOne(
        "SELECT tr.RolID, tr.Nombre, tr.Descripcion,
                COUNT(e.EmpleadoID) as total_empleados
         FROM Tipo_Rol tr
         LEFT JOIN Empleado e ON tr.RolID = e.RolID
         WHERE tr.RolID = :id
         GROUP BY tr.RolID",
        [':id' => $rolId]
    );
    
    if (!$rol) {
        ob_end_clean();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Rol no encontrado']);
        exit;
    }
    
    // Obtener empleados asignados a este rol
    $empleados = $db->fetchAll(
        "SELECT e.EmpleadoID, e.Nombre_Apellidos, e.DNI, e.Telefono,
                t.Descripcion as Turno
         FROM Empleado e
         JOIN Turno t ON e.TurnoID = t.TurnoID
         WHERE e.RolID = :id
         ORDER BY e.Nombre_Apellidos ASC",
        [':id' => $rolId]
    );
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'rol' => $rol,
        'empleados' => $empleados
    ]);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
