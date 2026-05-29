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

$clienteId = $input['clienteId'] ?? null;
$nombreApellidos = trim($input['nombreApellidos'] ?? '');
$tipoDocumento = trim($input['tipoDocumento'] ?? '');
$numDocumento = trim($input['numDocumento'] ?? '');
$telefono = trim($input['telefono'] ?? '') ?: null;
$correo = trim($input['correo'] ?? '') ?: null;
$direccion = trim($input['direccion'] ?? '') ?: null;

// Validaciones
if (empty($nombreApellidos) || empty($tipoDocumento) || empty($numDocumento)) {
    echo json_encode(['success' => false, 'message' => 'Nombre, tipo y número de documento son obligatorios']);
    exit;
}

// Validar formato de documento según tipo
if ($tipoDocumento === 'DNI' && !preg_match('/^[0-9]{8}$/', $numDocumento)) {
    echo json_encode(['success' => false, 'message' => 'DNI debe tener 8 dígitos']);
    exit;
}

if ($tipoDocumento === 'RUC' && !preg_match('/^[0-9]{11}$/', $numDocumento)) {
    echo json_encode(['success' => false, 'message' => 'RUC debe tener 11 dígitos']);
    exit;
}

// Validar correo si se proporciona
if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Formato de correo inválido']);
    exit;
}

try {
    $db->beginTransaction();
    
    if ($clienteId) {
        // Actualizar cliente existente
        
        // Verificar que el documento no esté duplicado (excepto el mismo cliente)
        $docExiste = $db->fetchOne(
            "SELECT ClienteID FROM Cliente WHERE Tipo_Documento = :tipo AND Num_Documento = :num AND ClienteID != :clienteId",
            [':tipo' => $tipoDocumento, ':num' => $numDocumento, ':clienteId' => $clienteId]
        );
        
        if ($docExiste) {
            throw new Exception('Ya existe un cliente con ese documento');
        }
        
        // Verificar correo único si se proporciona
        if ($correo) {
            $correoExiste = $db->fetchOne(
                "SELECT ClienteID FROM Cliente WHERE Correo = :correo AND ClienteID != :clienteId",
                [':correo' => $correo, ':clienteId' => $clienteId]
            );
            
            if ($correoExiste) {
                throw new Exception('Ya existe un cliente con ese correo');
            }
        }
        
        $affected = $db->execute(
            "UPDATE Cliente SET 
                Nombre_Apellidos = :nombre,
                Tipo_Documento = :tipo,
                Num_Documento = :num,
                Telefono = :telefono,
                Correo = :correo,
                Direccion = :direccion
             WHERE ClienteID = :clienteId",
            [
                ':nombre' => $nombreApellidos,
                ':tipo' => $tipoDocumento,
                ':num' => $numDocumento,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':direccion' => $direccion,
                ':clienteId' => $clienteId
            ]
        );
        
        if ($affected === 0) {
            throw new Exception('No se pudo actualizar el cliente');
        }
        
        $mensaje = 'Cliente actualizado correctamente';
        
    } else {
        // Crear nuevo cliente
        
        // Verificar que el documento no esté duplicado
        $docExiste = $db->fetchOne(
            "SELECT ClienteID FROM Cliente WHERE Tipo_Documento = :tipo AND Num_Documento = :num",
            [':tipo' => $tipoDocumento, ':num' => $numDocumento]
        );
        
        if ($docExiste) {
            throw new Exception('Ya existe un cliente con ese documento');
        }
        
        // Verificar correo único si se proporciona
        if ($correo) {
            $correoExiste = $db->fetchOne(
                "SELECT ClienteID FROM Cliente WHERE Correo = :correo",
                [':correo' => $correo]
            );
            
            if ($correoExiste) {
                throw new Exception('Ya existe un cliente con ese correo');
            }
        }
        
        $db->execute(
            "INSERT INTO Cliente (Nombre_Apellidos, Tipo_Documento, Num_Documento, Telefono, Correo, Direccion, Fecha_Creacion)
             VALUES (:nombre, :tipo, :num, :telefono, :correo, :direccion, NOW())",
            [
                ':nombre' => $nombreApellidos,
                ':tipo' => $tipoDocumento,
                ':num' => $numDocumento,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':direccion' => $direccion
            ]
        );
        
        $mensaje = 'Cliente creado correctamente';
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje
    ]);

} catch (Exception $e) {
    $db->rollback();
    error_log("Error en guardar-cliente.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}