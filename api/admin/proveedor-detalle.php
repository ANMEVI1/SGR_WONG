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

$proveedorId = $_GET['id'] ?? null;

if (!$proveedorId || !is_numeric($proveedorId)) {
    echo json_encode(['success' => false, 'message' => 'ID de proveedor inválido']);
    exit;
}

try {
    $proveedor = $db->fetchOne(
        "SELECT p.ProveedorID, p.Razon_Social, p.Ruc, p.Contacto, p.Telefono, 
                p.Direccion, p.Tipo_Producto, p.Correo, p.Estado,
                COUNT(k.KardexID) as total_movimientos,
                COALESCE(SUM(k.Cantidad * k.Precio_Unitario), 0) as total_compras,
                MAX(k.Fecha) as ultima_compra
         FROM Proveedor p
         LEFT JOIN Kardex k ON p.ProveedorID = k.ProveedorID AND k.Tipo_Movimiento = 'Entrada'
         WHERE p.ProveedorID = :proveedorId
         GROUP BY p.ProveedorID",
        [':proveedorId' => $proveedorId]
    );

    if (!$proveedor) {
        echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'proveedor' => $proveedor
    ]);

} catch (Exception $e) {
    error_log("Error en proveedor-detalle.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}