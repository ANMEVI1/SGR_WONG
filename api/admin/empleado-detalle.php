<?php
require_once '../../config/conexion.php';

header('Content-Type: application/json');

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Verificar permisos de administrador
$userRole = $db->fetchOne(
    "SELECT TipUsuID FROM Usuario WHERE UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

if ($userRole['TipUsuID'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$empleadoId = $_GET['id'] ?? null;

if (!$empleadoId || !is_numeric($empleadoId)) {
    echo json_encode(['success' => false, 'message' => 'ID de empleado inválido']);
    exit;
}

try {
    $empleado = $db->fetchOne(
        "SELECT e.EmpleadoID, e.Nombre_Apellidos, e.DNI, e.Telefono, e.Sueldo, 
                e.Estado, e.Fecha_Contratacion, e.RolID, e.TurnoID,
                r.Nombre as Rol, t.Descripcion as Turno
         FROM Empleado e
         JOIN Tipo_Rol r ON e.RolID = r.RolID
         JOIN Turno t ON e.TurnoID = t.TurnoID
         WHERE e.EmpleadoID = :empleadoId",
        [':empleadoId' => $empleadoId]
    );

    if (!$empleado) {
        echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'empleado' => $empleado
    ]);

} catch (Exception $e) {
    error_log("Error en empleado-detalle.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}