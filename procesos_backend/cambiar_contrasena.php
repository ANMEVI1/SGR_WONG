<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión inválida. Inicia sesión nuevamente.']);
    exit;
}

$usuarioID = (int) $_SESSION['usuario_id'];
$actual = trim($_POST['actual'] ?? '');
$nueva = trim($_POST['nueva'] ?? '');
$repetir = trim($_POST['repetir'] ?? '');

if ($actual === '' || $nueva === '' || $repetir === '') {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos de contraseña.']);
    exit;
}

if (strlen($nueva) < 6) {
    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
    exit;
}

if ($nueva !== $repetir) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
    exit;
}

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare("SELECT Contrasena FROM Usuario WHERE UsuarioID = :usuarioID LIMIT 1");
    $stmt->execute([':usuarioID' => $usuarioID]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($actual, $usuario['Contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
        exit;
    }

    $hashPassword = password_hash($nueva, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("UPDATE Usuario SET Contrasena = :contrasena WHERE UsuarioID = :usuarioID");
    $stmt->execute([
        ':contrasena' => $hashPassword,
        ':usuarioID' => $usuarioID,
    ]);

    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    exit;
} catch (PDOException $e) {
    error_log('cambiar_contrasena.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al cambiar la contraseña. Intenta de nuevo más tarde.']);
    exit;
}