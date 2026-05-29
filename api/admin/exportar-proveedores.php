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
    $proveedores = $db->fetchAll(
        "SELECT p.ProveedorID, p.Razon_Social, p.Ruc, p.Contacto, p.Telefono, 
                p.Direccion, p.Tipo_Producto, p.Correo, p.Estado,
                COUNT(k.KardexID) as total_movimientos,
                COALESCE(SUM(k.Cantidad * k.Precio_Unitario), 0) as total_compras,
                MAX(k.Fecha) as ultima_compra
         FROM Proveedor p
         LEFT JOIN Kardex k ON p.ProveedorID = k.ProveedorID AND k.Tipo_Movimiento = 'Entrada'
         GROUP BY p.ProveedorID
         ORDER BY p.Razon_Social ASC"
    );

    // Configurar headers para descarga CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="proveedores_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, [
        'ID Proveedor',
        'Razón Social',
        'RUC',
        'Contacto',
        'Teléfono',
        'Correo',
        'Tipo de Producto',
        'Dirección',
        'Estado',
        'Total Movimientos',
        'Total Compras',
        'Última Compra'
    ]);
    
    // Datos
    foreach ($proveedores as $proveedor) {
        fputcsv($output, [
            $proveedor['ProveedorID'],
            $proveedor['Razon_Social'],
            $proveedor['Ruc'],
            $proveedor['Contacto'],
            $proveedor['Telefono'],
            $proveedor['Correo'] ?: 'No registrado',
            $proveedor['Tipo_Producto'] ?: 'No especificado',
            $proveedor['Direccion'] ?: 'No registrada',
            $proveedor['Estado'] === 'A' ? 'Activo' : 'Inactivo',
            $proveedor['total_movimientos'],
            'S/ ' . number_format($proveedor['total_compras'], 2),
            $proveedor['ultima_compra'] ? date('d/m/Y', strtotime($proveedor['ultima_compra'])) : 'Nunca'
        ]);
    }
    
    fclose($output);

} catch (Exception $e) {
    error_log("Error en exportar-proveedores.php: " . $e->getMessage());
    http_response_code(500);
    echo 'Error interno del servidor';
}