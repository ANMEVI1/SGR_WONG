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

$clienteId = $_GET['id'] ?? null;

if (!$clienteId || !is_numeric($clienteId)) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente inválido']);
    exit;
}

try {
    $cliente = $db->fetchOne(
        "SELECT c.ClienteID, c.Nombre_Apellidos, c.Tipo_Documento, c.Num_Documento, 
                c.Telefono, c.Direccion, c.Correo, c.Fecha_Creacion,
                u.Login as Usuario_Web, u.Estado as Estado_Usuario,
                COUNT(p.PedidoID) as total_pedidos,
                COALESCE(SUM(cp.Total), 0) as total_gastado
         FROM Cliente c
         LEFT JOIN Usuario u ON c.UsuarioID = u.UsuarioID
         LEFT JOIN Pedido p ON c.ClienteID = p.ClienteID
         LEFT JOIN Comprobante_Pago cp ON p.PedidoID = cp.PedidoID
         WHERE c.ClienteID = :clienteId
         GROUP BY c.ClienteID",
        [':clienteId' => $clienteId]
    );

    if (!$cliente) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'cliente' => $cliente
    ]);

} catch (Exception $e) {
    error_log("Error en cliente-detalle.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}