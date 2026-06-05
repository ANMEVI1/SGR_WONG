<?php
/**
 * API Endpoint: Crear nueva reserva
 * Método: POST
 * Descripción: Permite a clientes crear reservas validando disponibilidad y horarios
 */

// Incluir configuraciones
require_once '../../config/Database.php';
require_once '../../config/Response.php';
require_once '../../config/Validator.php';

// Headers CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

try {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $personas = trim($_POST['personas'] ?? '');
    $comentarios = trim($_POST['comentarios'] ?? '');
    
    // ========== VALIDACIONES ==========
    $validator = new Validator();
    
    $validator
        ->required('nombre', $nombre, 'El nombre es requerido')
        ->minLength('nombre', $nombre, 3, 'El nombre debe tener al menos 3 caracteres')
        ->maxLength('nombre', $nombre, 150, 'El nombre no puede exceder 150 caracteres');
    
    $validator
        ->required('correo', $correo, 'El correo es requerido')
        ->email('correo', $correo, 'El correo no es válido');
    
    $validator
        ->required('telefono', $telefono, 'El teléfono es requerido')
        ->minLength('telefono', $telefono, 7, 'El teléfono debe tener al menos 7 dígitos');
    
    $validator
        ->required('fecha', $fecha, 'La fecha es requerida');
    
    $validator
        ->required('hora', $hora, 'La hora es requerida');
    
    $validator
        ->required('personas', $personas, 'El número de personas es requerido')
        ->numeric('personas', $personas, 'El número de personas debe ser numérico');
    
    // Si hay errores de validación, retornar
    if ($validator->hasErrors()) {
        Response::validation($validator->getErrors());
    }
    
    // Convertir personas a entero
    $num_personas = (int)$personas;
    
    // Validar rango de personas
    if ($num_personas < 1 || $num_personas > 20) {
        Response::error('El número de personas debe estar entre 1 y 20', 422);
    }
    
    // ========== CONEXIÓN A BASE DE DATOS ==========
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // ========== OBTENER CONFIGURACIÓN DE RESERVAS ==========
    $config = $db->fetchOne("SELECT * FROM Reserva_Config WHERE Estado = 1 LIMIT 1");
    
    if (!$config) {
        Response::error('El sistema de reservas no está configurado. Contacta al administrador.', 503);
    }
    
    // ========== VALIDAR ANTICIPACIÓN MÍNIMA ==========
    $fecha_hora_reserva = new DateTime("$fecha $hora");
    $ahora = new DateTime();
    
    // Calcular diferencia en horas
    $diferencia_segundos = $fecha_hora_reserva->getTimestamp() - $ahora->getTimestamp();
    $diferencia_horas = $diferencia_segundos / 3600;
    
    if ($diferencia_horas < $config['Anticipacion_Min_Hrs']) {
        Response::error(
            "Debes reservar con al menos {$config['Anticipacion_Min_Hrs']} horas de anticipación", 
            422
        );
    }
    
    // ========== VALIDAR ANTICIPACIÓN MÁXIMA ==========
    $diferencia_dias = $diferencia_segundos / 86400;
    
    if ($diferencia_dias > $config['Anticipacion_Max_Dias']) {
        Response::error(
            "No puedes reservar con más de {$config['Anticipacion_Max_Dias']} días de anticipación", 
            422
        );
    }
    
    // ========== VALIDAR HORARIO DE ATENCIÓN ==========
    // Horario: 11:00 AM - 10:00 PM
    $hora_obj = new DateTime($hora);
    $hora_int = (int)$hora_obj->format('H');
    $minuto_int = (int)$hora_obj->format('i');
    
    // Convertir a minutos desde medianoche para comparar
    $minutos_reserva = ($hora_int * 60) + $minuto_int;
    $minutos_apertura = 11 * 60; // 11:00 AM
    $minutos_cierre = 22 * 60;   // 10:00 PM
    
    if ($minutos_reserva < $minutos_apertura || $minutos_reserva >= $minutos_cierre) {
        Response::error('Nuestro horario de atención es de 11:00 AM a 10:00 PM', 422);
    }
    
    // ========== VALIDAR FECHA NO SEA PASADA ==========
    if ($fecha_hora_reserva < $ahora) {
        Response::error('No puedes reservar en una fecha u hora pasada', 422);
    }
    
    // ========== BUSCAR O CREAR CLIENTE ==========
    $cliente = $db->fetchOne("SELECT ClienteID FROM Cliente WHERE Correo = ?", [$correo]);
    
    $cliente_id = null;
    
    if ($cliente) {
        // Cliente existente
        $cliente_id = $cliente['ClienteID'];
        
        // Actualizar datos por si cambiaron
        $db->execute(
            "UPDATE Cliente SET 
                Nombre_Apellidos = ?, 
                Telefono = ?
             WHERE ClienteID = ?",
            [$nombre, $telefono, $cliente_id]
        );
    } else {
        // Crear nuevo cliente anónimo (sin usuario web)
        $db->execute(
            "INSERT INTO Cliente 
                (Nombre_Apellidos, Tipo_Documento, Num_Documento, Telefono, Correo, UsuarioID)
             VALUES (?, 'DNI', '00000000', ?, ?, NULL)",
            [$nombre, $telefono, $correo]
        );
        
        $cliente_id = $db->lastInsertId();
    }
    
    // ========== GENERAR TOKEN ÚNICO DE CANCELACIÓN ==========
    $token = bin2hex(random_bytes(16)); // 32 caracteres hexadecimales
    
    // ========== INSERTAR RESERVA ==========
    $db->execute(
        "INSERT INTO Reserva 
            (Token_Cancelacion, ClienteID, Nombre_Contacto, Telefono_Contacto, 
             Correo_Contacto, Fecha_Reserva, Hora_Reserva, Num_Comensales, 
             Observaciones, Estado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')",
        [
            $token,
            $cliente_id,
            $nombre,
            $telefono,
            $correo,
            $fecha,
            $hora,
            $num_personas,
            $comentarios
        ]
    );
    
    $reserva_id = $db->lastInsertId();
    
    // ========== REGISTRAR EN HISTORIAL ==========
    $db->execute(
        "INSERT INTO Reserva_Historial 
            (ReservaID, Estado_Antes, Estado_Nuevo, Origen, Observacion)
         VALUES (?, NULL, 'Pendiente', 'cliente_web', 'Reserva creada desde web')",
        [$reserva_id]
    );
    
    // ========== CAPTURAR DATOS ADICIONALES (NO SE GUARDAN EN BD AÚN) ==========
    $metodo_pago = trim($_POST['metodo_pago'] ?? '');
    $total_senal = trim($_POST['total_senal'] ?? '0');
    $platos_json = trim($_POST['platos'] ?? '');
    $subtotal_platos = trim($_POST['subtotal_platos'] ?? '0');
    
    // Decodificar platos si existen
    $platos_pre_ordenados = [];
    if (!empty($platos_json)) {
        $platos_pre_ordenados = json_decode($platos_json, true);
    }
    
    // Log de información adicional (para futuro procesamiento)
    if ($metodo_pago) {
        error_log("Reserva #{$reserva_id} - Señal: S/ {$total_senal} vía {$metodo_pago}");
    }
    if (!empty($platos_pre_ordenados)) {
        error_log("Reserva #{$reserva_id} - Platos pre-ordenados: " . count($platos_pre_ordenados));
    }
    
    // ========== RESPUESTA EXITOSA ==========
    Response::success([
        'reserva_id' => $reserva_id,
        'token' => $token,
        'fecha' => $fecha,
        'hora' => $hora,
        'nombre' => $nombre,
        'num_comensales' => $num_personas,
        'estado' => 'Pendiente',
        'metodo_pago' => $metodo_pago,
        'total_senal' => floatval($total_senal),
        'platos_pre_ordenados' => $platos_pre_ordenados,
        'subtotal_platos' => floatval($subtotal_platos)
    ], '¡Reserva creada exitosamente! Te contactaremos pronto para confirmar.');
    
} catch (PDOException $e) {
    error_log("Error BD en crear reserva: " . $e->getMessage());
    Response::error('Error al procesar la reserva. Intenta nuevamente.', 500);
} catch (Exception $e) {
    error_log("Error en crear reserva: " . $e->getMessage());
    Response::error($e->getMessage(), 500);
}
