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

$usuarioId = $_GET['id'] ?? null;

if (!$usuarioId || !is_numeric($usuarioId)) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

try {
    $usuario = $db->fetchOne(
        "SELECT u.UsuarioID, u.Login, u.Estado,
                c.Nombre_Apellidos, c.Telefono, c.Direccion, c.Fecha_Creacion,
                COUNT(p.PedidoID) as total_pedidos,
                COALESCE(SUM(cp.Total), 0) as total_gastado
         FROM Usuario u
         LEFT JOIN Cliente c ON u.UsuarioID = c.UsuarioID
         LEFT JOIN Pedido p ON c.ClienteID = p.ClienteID
         LEFT JOIN Comprobante_Pago cp ON p.PedidoID = cp.PedidoID
         WHERE u.UsuarioID = :userId AND u.TipUsuID = 5
         GROUP BY u.UsuarioID",
        [':userId' => $usuarioId]
    );

    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'usuario' => $usuario
    ]);

} catch (Exception $e) {
    error_log("Error en usuario-detalle.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}