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

$proveedorId = $input['proveedorId'] ?? null;
$razonSocial = trim($input['razonSocial'] ?? '');
$ruc = trim($input['ruc'] ?? '');
$contacto = trim($input['contacto'] ?? '');
$telefono = trim($input['telefono'] ?? '');
$correo = trim($input['correo'] ?? '') ?: null;
$tipoProducto = trim($input['tipoProducto'] ?? '') ?: null;
$direccion = trim($input['direccion'] ?? '') ?: null;

// Validaciones
if (empty($razonSocial) || empty($ruc) || empty($contacto) || empty($telefono)) {
    echo json_encode(['success' => false, 'message' => 'Razón social, RUC, contacto y teléfono son obligatorios']);
    exit;
}

// Validar formato de RUC
if (!preg_match('/^[0-9]{11}$/', $ruc)) {
    echo json_encode(['success' => false, 'message' => 'RUC debe tener exactamente 11 dígitos']);
    exit;
}

// Validar correo si se proporciona
if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Formato de correo inválido']);
    exit;
}

try {
    $db->beginTransaction();
    
    if ($proveedorId) {
        // Actualizar proveedor existente
        
        // Verificar que el RUC no esté duplicado (excepto el mismo proveedor)
        $rucExiste = $db->fetchOne(
            "SELECT ProveedorID FROM Proveedor WHERE Ruc = :ruc AND ProveedorID != :proveedorId",
            [':ruc' => $ruc, ':proveedorId' => $proveedorId]
        );
        
        if ($rucExiste) {
            throw new Exception('Ya existe un proveedor con ese RUC');
        }
        
        $affected = $db->execute(
            "UPDATE Proveedor SET 
                Razon_Social = :razonSocial,
                Ruc = :ruc,
                Contacto = :contacto,
                Telefono = :telefono,
                Correo = :correo,
                Tipo_Producto = :tipoProducto,
                Direccion = :direccion
             WHERE ProveedorID = :proveedorId",
            [
                ':razonSocial' => $razonSocial,
                ':ruc' => $ruc,
                ':contacto' => $contacto,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':tipoProducto' => $tipoProducto,
                ':direccion' => $direccion,
                ':proveedorId' => $proveedorId
            ]
        );
        
        if ($affected === 0) {
            throw new Exception('No se pudo actualizar el proveedor');
        }
        
        $mensaje = 'Proveedor actualizado correctamente';
        
    } else {
        // Crear nuevo proveedor
        
        // Verificar que el RUC no esté duplicado
        $rucExiste = $db->fetchOne(
            "SELECT ProveedorID FROM Proveedor WHERE Ruc = :ruc",
            [':ruc' => $ruc]
        );
        
        if ($rucExiste) {
            throw new Exception('Ya existe un proveedor con ese RUC');
        }
        
        $db->execute(
            "INSERT INTO Proveedor (Razon_Social, Ruc, Contacto, Telefono, Correo, Tipo_Producto, Direccion, Estado)
             VALUES (:razonSocial, :ruc, :contacto, :telefono, :correo, :tipoProducto, :direccion, 'A')",
            [
                ':razonSocial' => $razonSocial,
                ':ruc' => $ruc,
                ':contacto' => $contacto,
                ':telefono' => $telefono,
                ':correo' => $correo,
                ':tipoProducto' => $tipoProducto,
                ':direccion' => $direccion
            ]
        );
        
        $mensaje = 'Proveedor creado correctamente';
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje
    ]);

} catch (Exception $e) {
    $db->rollback();
    error_log("Error en guardar-proveedor.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}