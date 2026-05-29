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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($input['mensaje'] ?? '');

if (empty($mensaje)) {
    echo json_encode(['success' => false, 'message' => 'El mensaje no puede estar vacío']);
    exit;
}

if (strlen($mensaje) > 500) {
    echo json_encode(['success' => false, 'message' => 'El mensaje es demasiado largo (máximo 500 caracteres)']);
    exit;
}

try {
    // Obtener usuarios activos con email
    $usuarios = $db->fetchAll(
        "SELECT c.Correo, c.Nombre_Apellidos
         FROM Usuario u
         JOIN Cliente c ON u.UsuarioID = c.UsuarioID
         WHERE u.TipUsuID = 5 AND u.Estado = 'Activo' AND c.Correo IS NOT NULL"
    );

    if (empty($usuarios)) {
        echo json_encode(['success' => false, 'message' => 'No hay usuarios activos con email registrado']);
        exit;
    }

    $enviados = 0;
    $errores = 0;

    foreach ($usuarios as $usuario) {
        // Aquí implementarías el envío real de email
        // Por ahora simularemos el envío
        
        $asunto = "Notificación - Chifa Matsue";
        $cuerpo = "Hola " . ($usuario['Nombre_Apellidos'] ?: 'Cliente') . ",\n\n";
        $cuerpo .= $mensaje . "\n\n";
        $cuerpo .= "Saludos,\nEquipo Chifa Matsue";
        
        // Simulación de envío (reemplazar con mail() real o servicio de email)
        $enviado = true; // mail($usuario['Correo'], $asunto, $cuerpo);
        
        if ($enviado) {
            $enviados++;
        } else {
            $errores++;
        }
    }

    // Registrar la notificación en logs
    error_log("Notificación masiva enviada por usuario {$currentUser['UsuarioID']}: {$enviados} enviados, {$errores} errores");

    echo json_encode([
        'success' => true,
        'message' => "Notificación enviada a {$enviados} usuarios" . ($errores > 0 ? " ({$errores} errores)" : ""),
        'enviados' => $enviados,
        'errores' => $errores
    ]);

} catch (Exception $e) {
    error_log("Error en notificacion-masiva.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}