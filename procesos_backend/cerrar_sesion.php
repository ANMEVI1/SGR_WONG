<?php
session_start();

// Vaciar datos de sesión
$_SESSION = array();

// Si se usan cookies de sesión, destruir la cookie en el cliente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destruir la sesión del servidor
session_destroy();

if (isset($_GET['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true]);
    exit();
}

// Redirigir al índice
header('Location: ../index.php');
exit();
?>