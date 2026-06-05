<?php
/**
 * API: Obtener reservas del cliente logueado
 * Método: GET
 */

require_once '../../config/Database.php';
require_once '../../config/Response.php';
require_once '../../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('Método no permitido', 405);
}

try {
    // Verificar sesión
    startSecureSession();
    $currentUser = getCurrentUser();
    
    if (!$currentUser || !isset($currentUser['id'])) {
        Response::error('Debes iniciar sesión para ver tus reservas', 401);
    }
    
    $usuarioID = $currentUser['id'];
    
    $db = Database::getInstance();
    
    // Obtener ClienteID del usuario logueado
    $cliente = $db->fetchOne(
        "SELECT ClienteID FROM Cliente WHERE UsuarioID = ?",
        [$usuarioID]
    );
    
    if (!$cliente) {
        // Usuario no tiene perfil de cliente aún
        Response::success([], 'No tienes reservas registradas aún');
    }
    
    $clienteID = $cliente['ClienteID'];
    
    // Obtener todas las reservas del cliente
    $reservas = $db->fetchAll(
        "SELECT 
            ReservaID,
            Token_Cancelacion,
            Fecha_Reserva,
            Hora_Reserva,
            Num_Comensales,
            Observaciones,
            Estado,
            Fecha_Creacion
         FROM Reserva
         WHERE ClienteID = ?
         ORDER BY Fecha_Reserva DESC, Hora_Reserva DESC
         LIMIT 50",
        [$clienteID]
    );
    
    // Formatear datos
    $reservasFormateadas = array_map(function($r) {
        return [
            'id' => (int)$r['ReservaID'],
            'token' => $r['Token_Cancelacion'],
            'fecha' => $r['Fecha_Reserva'],
            'hora' => substr($r['Hora_Reserva'], 0, 5), // HH:MM
            'personas' => (int)$r['Num_Comensales'],
            'observaciones' => $r['Observaciones'] ?? '',
            'estado' => $r['Estado'],
            'fecha_creacion' => $r['Fecha_Creacion']
        ];
    }, $reservas);
    
    Response::success($reservasFormateadas, 'Reservas obtenidas exitosamente');
    
} catch (Exception $e) {
    error_log("Error en mis-reservas.php: " . $e->getMessage());
    Response::error('Error al obtener reservas', 500);
}
