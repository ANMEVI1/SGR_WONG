<?php
/**
 * API: Obtener detalles completos de una reserva
 * Método: GET
 * Parámetro: id (ReservaID)
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
        Response::error('Debes iniciar sesión', 401);
    }
    
    $reservaID = $_GET['id'] ?? null;
    
    if (!$reservaID || !is_numeric($reservaID)) {
        Response::error('ID de reserva inválido', 400);
    }
    
    $usuarioID = $currentUser['id'];
    $db = Database::getInstance();
    
    // Obtener ClienteID del usuario
    $cliente = $db->fetchOne(
        "SELECT ClienteID FROM Cliente WHERE UsuarioID = ?",
        [$usuarioID]
    );
    
    if (!$cliente) {
        Response::error('No tienes acceso a esta reserva', 403);
    }
    
    $clienteID = $cliente['ClienteID'];
    
    // Obtener datos completos de la reserva
    $reserva = $db->fetchOne(
        "SELECT 
            r.ReservaID,
            r.Token_Cancelacion,
            r.Fecha_Reserva,
            r.Hora_Reserva,
            r.Num_Comensales,
            r.Observaciones,
            r.Estado,
            r.Monto_Senal,
            r.Fecha_Creacion,
            c.Nombre_Apellidos,
            c.Telefono,
            c.Correo
         FROM Reserva r
         INNER JOIN Cliente c ON r.ClienteID = c.ClienteID
         WHERE r.ReservaID = ? AND r.ClienteID = ?",
        [$reservaID, $clienteID]
    );
    
    if (!$reserva) {
        Response::error('Reserva no encontrada', 404);
    }
    
    // Obtener platos pre-ordenados
    $platos = $db->fetchAll(
        "SELECT 
            p.Nombre as nombre_plato,
            p.Precio_Unitario as precio,
            drp.Cantidad as cantidad,
            (p.Precio_Unitario * drp.Cantidad) as subtotal
         FROM Detalle_Reserva_Plato drp
         INNER JOIN Plato p ON drp.PlatoID = p.PlatoID
         WHERE drp.ReservaID = ?",
        [$reservaID]
    );
    
    $platosFormateados = array_map(function($p) {
        return [
            'nombre' => $p['nombre_plato'],
            'precio' => (float)$p['precio'],
            'cantidad' => (int)$p['cantidad'],
            'subtotal' => (float)$p['subtotal']
        ];
    }, $platos);
    
    $totalPlatos = array_reduce($platosFormateados, function($carry, $p) {
        return $carry + $p['subtotal'];
    }, 0);
    
    $detalle = [
        'id' => (int)$reserva['ReservaID'],
        'token' => $reserva['Token_Cancelacion'],
        'fecha' => $reserva['Fecha_Reserva'],
        'hora' => substr($reserva['Hora_Reserva'], 0, 5),
        'personas' => (int)$reserva['Num_Comensales'],
        'observaciones' => $reserva['Observaciones'] ?? '',
        'estado' => $reserva['Estado'],
        'monto_senal' => (float)$reserva['Monto_Senal'],
        'fecha_creacion' => $reserva['Fecha_Creacion'],
        'cliente' => [
            'nombre' => $reserva['Nombre_Apellidos'],
            'telefono' => $reserva['Telefono'],
            'correo' => $reserva['Correo']
        ],
        'platos' => $platosFormateados,
        'total_platos' => $totalPlatos
    ];
    
    Response::success($detalle, 'Detalle obtenido exitosamente');
    
} catch (Exception $e) {
    error_log("Error en detalle-reserva.php: " . $e->getMessage());
    Response::error('Error al obtener detalle de reserva', 500);
}
