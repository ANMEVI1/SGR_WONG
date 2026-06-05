<?php
/**
 * API: Obtener datos del cliente autenticado
 * Retorna información del cliente para autocompletar formulario
 */

require_once '../../config/conexion.php';
require_once '../../config/Response.php';

header('Content-Type: application/json; charset=utf-8');

// Iniciar sesión
startSecureSession();

// Verificar si hay usuario autenticado
if (!isAuthenticated()) {
    Response::error('No hay sesión activa', 401);
}

try {
    $db = Database::getInstance();
    $usuario_id = $_SESSION['usuario_id'];
    
    // Buscar cliente asociado al usuario
    $cliente = $db->fetchOne("
        SELECT 
            ClienteID,
            Nombre_Apellidos,
            Telefono,
            Correo,
            Direccion
        FROM Cliente
        WHERE UsuarioID = ?
        LIMIT 1
    ", [$usuario_id]);
    
    if ($cliente) {
        Response::success([
            'cliente_id' => $cliente['ClienteID'],
            'nombre' => $cliente['Nombre_Apellidos'],
            'correo' => $cliente['Correo'],
            'telefono' => $cliente['Telefono'],
            'direccion' => $cliente['Direccion']
        ]);
    } else {
        // Usuario autenticado pero sin perfil de cliente creado aún
        // Retornar datos básicos del usuario
        $usuario = $db->fetchOne("
            SELECT Login
            FROM Usuario
            WHERE UsuarioID = ?
        ", [$usuario_id]);
        
        Response::success([
            'nombre' => $usuario['Login'] ?? 'Usuario',
            'correo' => '',
            'telefono' => '',
            'direccion' => ''
        ], 'Completa tu perfil para reservas más rápidas');
    }
    
} catch (Exception $e) {
    error_log("Error obteniendo datos cliente: " . $e->getMessage());
    Response::error('Error al obtener datos del cliente', 500);
}
