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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$empleadoId = $input['empleadoId'] ?? null;
$nombreApellidos = trim($input['nombreApellidos'] ?? '');
$dni = trim($input['dni'] ?? '');
$telefono = trim($input['telefono'] ?? '');
$sueldo = $input['sueldo'] ?? null;
$rolId = $input['rolId'] ?? null;
$turnoId = $input['turnoId'] ?? null;

// Validaciones
if (empty($nombreApellidos) || empty($dni) || empty($telefono) || !$sueldo || !$rolId || !$turnoId) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

if (!preg_match('/^[0-9]{8}$/', $dni)) {
    echo json_encode(['success' => false, 'message' => 'DNI debe tener 8 dígitos']);
    exit;
}

if ($sueldo <= 0) {
    echo json_encode(['success' => false, 'message' => 'El sueldo debe ser mayor a 0']);
    exit;
}

try {
    $db->beginTransaction();
    
    if ($empleadoId) {
        // Actualizar empleado existente
        
        // Verificar que el DNI no esté duplicado (excepto el mismo empleado)
        $dniExiste = $db->fetchOne(
            "SELECT EmpleadoID FROM Empleado WHERE DNI = :dni AND EmpleadoID != :empleadoId",
            [':dni' => $dni, ':empleadoId' => $empleadoId]
        );
        
        if ($dniExiste) {
            throw new Exception('Ya existe un empleado con ese DNI');
        }
        
        $affected = $db->execute(
            "UPDATE Empleado SET 
                Nombre_Apellidos = :nombre,
                DNI = :dni,
                Telefono = :telefono,
                Sueldo = :sueldo,
                RolID = :rolId,
                TurnoID = :turnoId
             WHERE EmpleadoID = :empleadoId",
            [
                ':nombre' => $nombreApellidos,
                ':dni' => $dni,
                ':telefono' => $telefono,
                ':sueldo' => $sueldo,
                ':rolId' => $rolId,
                ':turnoId' => $turnoId,
                ':empleadoId' => $empleadoId
            ]
        );
        
        if ($affected === 0) {
            throw new Exception('No se pudo actualizar el empleado');
        }
        
        $mensaje = 'Empleado actualizado correctamente';
        
    } else {
        // Crear nuevo empleado
        
        // Verificar que el DNI no esté duplicado
        $dniExiste = $db->fetchOne(
            "SELECT EmpleadoID FROM Empleado WHERE DNI = :dni",
            [':dni' => $dni]
        );
        
        if ($dniExiste) {
            throw new Exception('Ya existe un empleado con ese DNI');
        }
        
        $db->execute(
            "INSERT INTO Empleado (Nombre_Apellidos, DNI, Telefono, Sueldo, Estado, Fecha_Contratacion, RolID, TurnoID)
             VALUES (:nombre, :dni, :telefono, :sueldo, 'Activo', NOW(), :rolId, :turnoId)",
            [
                ':nombre' => $nombreApellidos,
                ':dni' => $dni,
                ':telefono' => $telefono,
                ':sueldo' => $sueldo,
                ':rolId' => $rolId,
                ':turnoId' => $turnoId
            ]
        );
        
        $mensaje = 'Empleado creado correctamente';
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje
    ]);

} catch (Exception $e) {
    $db->rollback();
    error_log("Error en guardar-empleado.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}