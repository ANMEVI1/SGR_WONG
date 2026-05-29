<?php
require_once '../../config/conexion.php';

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
    exit('Acceso denegado');
}

try {
    $usuarios = $db->fetchAll(
        "SELECT u.UsuarioID, u.Login, u.Estado,
                c.Nombre_Apellidos, c.Telefono, c.Direccion, c.Fecha_Creacion,
                COUNT(p.PedidoID) as total_pedidos,
                COALESCE(SUM(cp.Total), 0) as total_gastado
         FROM Usuario u
         LEFT JOIN Cliente c ON u.UsuarioID = c.UsuarioID
         LEFT JOIN Pedido p ON c.ClienteID = p.ClienteID
         LEFT JOIN Comprobante_Pago cp ON p.PedidoID = cp.PedidoID
         WHERE u.TipUsuID = 5
         GROUP BY u.UsuarioID
         ORDER BY c.Fecha_Creacion DESC"
    );

    // Configurar headers para descarga CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="usuarios_web_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, [
        'ID Usuario',
        'Email',
        'Nombre Completo',
        'Teléfono',
        'Dirección',
        'Fecha Registro',
        'Estado',
        'Total Pedidos',
        'Total Gastado'
    ]);
    
    // Datos
    foreach ($usuarios as $usuario) {
        fputcsv($output, [
            $usuario['UsuarioID'],
            $usuario['Login'],
            $usuario['Nombre_Apellidos'] ?: 'Sin nombre',
            $usuario['Telefono'] ?: 'No registrado',
            $usuario['Direccion'] ?: 'No registrada',
            $usuario['Fecha_Creacion'] ? date('d/m/Y H:i', strtotime($usuario['Fecha_Creacion'])) : 'N/A',
            $usuario['Estado'],
            $usuario['total_pedidos'],
            'S/ ' . number_format($usuario['total_gastado'], 2)
        ]);
    }
    
    fclose($output);

} catch (Exception $e) {
    error_log("Error en exportar-usuarios.php: " . $e->getMessage());
    http_response_code(500);
    echo 'Error interno del servidor';
}