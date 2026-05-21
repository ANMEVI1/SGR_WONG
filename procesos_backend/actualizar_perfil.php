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
$nombre = trim($_POST['nombre_completo'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$tipoDocumento = trim($_POST['tipo_documento'] ?? 'DNI');
$numDocumento = trim($_POST['num_documento'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if ($nombre === '' || $correo === '' || $telefono === '' || $numDocumento === '') {
    echo json_encode(['success' => false, 'message' => 'Completa los campos obligatorios.']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Ingresa un correo válido.']);
    exit;
}

$allowedDocumentTypes = ['DNI', 'CE', 'RUC'];
if (!in_array($tipoDocumento, $allowedDocumentTypes, true)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de documento inválido.']);
    exit;
}

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->beginTransaction();

    $updateCliente = "UPDATE Cliente
                      SET Nombre_Apellidos = :nombre,
                          Tipo_Documento = :tipoDoc,
                          Num_Documento = :numDoc,
                          Telefono = :telefono,
                          Direccion = :direccion,
                          Correo = :correo
                      WHERE UsuarioID = :usuarioID";

    $stmt = $pdo->prepare($updateCliente);
    $stmt->execute([
        ':nombre' => $nombre,
        ':tipoDoc' => $tipoDocumento,
        ':numDoc' => $numDocumento,
        ':telefono' => $telefono,
        ':direccion' => $direccion,
        ':correo' => $correo,
        ':usuarioID' => $usuarioID,
    ]);

    if ($correo !== ($_SESSION['usuario_login'] ?? '')) {
        $updateUsuario = "UPDATE Usuario SET Login = :correo WHERE UsuarioID = :usuarioID";
        $stmt = $pdo->prepare($updateUsuario);
        $stmt->execute([
            ':correo' => $correo,
            ':usuarioID' => $usuarioID,
        ]);
        $_SESSION['usuario_login'] = $correo;
    }

    $pdo->commit();

    $_SESSION['usuario_nombre'] = $nombre;

    echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
    exit;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('actualizar_perfil.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar tus datos. Intenta de nuevo más tarde.']);
    exit;
}