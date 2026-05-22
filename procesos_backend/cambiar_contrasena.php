<?php
session_start();
require_once '../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión expirada. Inicia sesión de nuevo.']);
    exit;
}

$usuarioID = (int) $_SESSION['usuario_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (trim($currentPassword) === '' || trim($newPassword) === '' || trim($confirmPassword) === '') {
    echo json_encode(['success' => false, 'message' => 'Completa todos los campos.']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden.']);
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

    if (!$usuario || !password_verify($currentPassword, $usuario['Contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual no es correcta.']);
        exit;
    }

    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE Usuario SET Contrasena = :contrasena WHERE UsuarioID = :usuarioID");
    $stmt->execute([
        ':contrasena' => $newHash,
        ':usuarioID' => $usuarioID,
    ]);

    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    exit;
} catch (PDOException $e) {
    error_log('Cambiar contraseña PDO error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al cambiar la contraseña. Intenta de nuevo más tarde.']);
    exit;
}
