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
$nombre = trim($_POST['nombre_completo'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$tipoDocumento = trim($_POST['tipo_documento'] ?? 'DNI');
$numDocumento = trim($_POST['nmr_documento'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if ($nombre === '' || $correo === '') {
    echo json_encode(['success' => false, 'message' => 'Nombre y correo son obligatorios.']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Correo inválido.']);
    exit;
}

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE Login = :login AND UsuarioID != :usuarioID");
    $stmt->execute([
        ':login' => $correo,
        ':usuarioID' => $usuarioID,
    ]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra cuenta con ese correo.']);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE Usuario SET Login = :login WHERE UsuarioID = :usuarioID");
    $stmt->execute([
        ':login' => $correo,
        ':usuarioID' => $usuarioID,
    ]);

    $stmt = $pdo->prepare(
        "UPDATE Cliente SET Nombre_Apellidos = :nombre, Tipo_Documento = :tipoDocumento,
                Num_Documento = :numDocumento, Telefono = :telefono,
                Direccion = :direccion, Correo = :correo
         WHERE UsuarioID = :usuarioID"
    );

    $stmt->execute([
        ':nombre' => $nombre,
        ':tipoDocumento' => $tipoDocumento,
        ':numDocumento' => $numDocumento,
        ':telefono' => $telefono,
        ':direccion' => $direccion,
        ':correo' => $correo,
        ':usuarioID' => $usuarioID,
    ]);

    $pdo->commit();
    $_SESSION['usuario_login'] = $correo;

    echo json_encode(['success' => true, 'message' => 'Tus datos se actualizaron correctamente.']);
    exit;
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Actualizar perfil PDO error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al guardar el perfil. Intenta de nuevo más tarde.']);
    exit;
}
