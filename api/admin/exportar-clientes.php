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
    $clientes = $db->fetchAll(
        "SELECT c.ClienteID, c.Nombre_Apellidos, c.Tipo_Documento, c.Num_Documento, 
                c.Telefono, c.Direccion, c.Correo, c.Fecha_Creacion,
                u.Login as Usuario_Web, u.Estado as Estado_Usuario,
                COUNT(p.PedidoID) as total_pedidos,
                COALESCE(SUM(cp.Total), 0) as total_gastado
         FROM Cliente c
         LEFT JOIN Usuario u ON c.UsuarioID = u.UsuarioID
         LEFT JOIN Pedido p ON c.ClienteID = p.ClienteID
         LEFT JOIN Comprobante_Pago cp ON p.PedidoID = cp.PedidoID
         GROUP BY c.ClienteID
         ORDER BY c.Fecha_Creacion DESC"
    );

    // Configurar headers para descarga CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="clientes_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, [
        'ID Cliente',
        'Nombre Completo',
        'Tipo Documento',
        'Número Documento',
        'Teléfono',
        'Correo',
        'Dirección',
        'Fecha Registro',
        'Usuario Web',
        'Estado Usuario',
        'Total Pedidos',
        'Total Gastado'
    ]);
    
    // Datos
    foreach ($clientes as $cliente) {
        fputcsv($output, [
            $cliente['ClienteID'],
            $cliente['Nombre_Apellidos'],
            $cliente['Tipo_Documento'],
            $cliente['Num_Documento'],
            $cliente['Telefono'] ?: 'No registrado',
            $cliente['Correo'] ?: 'No registrado',
            $cliente['Direccion'] ?: 'No registrada',
            date('d/m/Y H:i', strtotime($cliente['Fecha_Creacion'])),
            $cliente['Usuario_Web'] ?: 'Solo presencial',
            $cliente['Estado_Usuario'] ?: 'N/A',
            $cliente['total_pedidos'],
            'S/ ' . number_format($cliente['total_gastado'], 2)
        ]);
    }
    
    fclose($output);

} catch (Exception $e) {
    error_log("Error en exportar-clientes.php: " . $e->getMessage());
    http_response_code(500);
    echo 'Error interno del servidor';
}