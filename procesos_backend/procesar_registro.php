<?php
require_once '../config/conexion.php';

if (!isset($_POST['registrar'])) {
    header('Location: ../registro.php');
    exit;
}

$nombre      = trim($_POST['nombre_completo'] ?? '');
$documento   = trim($_POST['nmr_documento'] ?? '');
$telefono    = trim($_POST['telefono'] ?? '');
$correo      = trim($_POST['correo'] ?? '');
$passwordRaw = $_POST['pass'] ?? '';

if ($nombre === '' || $telefono === '' || $correo === '' || $passwordRaw === '') {
    echo "<script>
            alert('Por favor completa todos los campos obligatorios.');
            window.location='../registro.php';
        </script>";
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
            alert('Ingresa un correo válido.');
            window.location='../registro.php';
        </script>";
    exit;
}

$tipoDocumento = 'DNI';
$numDocumento = '';
if ($documento !== '') {
    $documento = preg_replace('/[^0-9]/', '', $documento);
    if (strlen($documento) === 11) {
        $tipoDocumento = 'RUC';
        $numDocumento = $documento;
    } elseif (strlen($documento) === 8) {
        $tipoDocumento = 'DNI';
        $numDocumento = $documento;
    } else {
        echo "<script>
                alert('Documento inválido. Ingresa DNI de 8 dígitos o RUC de 11 dígitos.');
                window.location='../registro.php';
            </script>";
        exit;
    }
}

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Buscar el tipo de usuario para cliente web. Si no existe, usamos 5 como alternativa.
    $stmt = $pdo->prepare("SELECT TipUsuID FROM Tipo_Usuario WHERE Descripcion = 'Cliente web' LIMIT 1");
    $stmt->execute();
    $tipoUsuario = $stmt->fetchColumn();
    $tipoUsuario = $tipoUsuario ? $tipoUsuario : 5;

    // Verificar que no exista el login ya registrado en Usuario
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE Login = :login");
    $stmt->execute([':login' => $correo]);
    if ($stmt->fetchColumn() > 0) {
        echo "<script>
                alert('Ya existe una cuenta con ese correo.');
                window.location='../registro.php';
              </script>";
        exit;
    }

    $hashPassword = password_hash($passwordRaw, PASSWORD_BCRYPT);
    $pdo->beginTransaction();

    $sqlUsuario = "INSERT INTO Usuario (Login, Contrasena, Estado, TipUsuID)
                   VALUES (:login, :contrasena, 'Activo', :tipUsuID)";
    $stmt = $pdo->prepare($sqlUsuario);
    $stmt->execute([
        ':login' => $correo,
        ':contrasena' => $hashPassword,
        ':tipUsuID' => $tipoUsuario,
    ]);
    $usuarioID = $pdo->lastInsertId();

    $sqlCliente = "INSERT INTO Cliente (Nombre_Apellidos, Tipo_Documento, Num_Documento, Telefono, Direccion, Correo, Fecha_Creacion, UsuarioID)
                   VALUES (:nombre, :tipoDoc, :numDoc, :telefono, NULL, :correo, NOW(), :usuarioID)";
    $stmt = $pdo->prepare($sqlCliente);
    $stmt->execute([
        ':nombre' => $nombre,
        ':tipoDoc' => $tipoDocumento,
        ':numDoc' => $numDocumento,
        ':telefono' => $telefono,
        ':correo' => $correo,
        ':usuarioID' => $usuarioID,
    ]);

    $pdo->commit();

    echo "<script>
            alert('Registro exitoso. Ahora puedes iniciar sesión con tu correo y contraseña.');
            window.location='../login.php';
          </script>";
    exit;

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Registro PDO error: ' . $e->getMessage());

    $message = strpos($e->getMessage(), 'Duplicate entry') !== false
        ? 'Ya existe una cuenta con ese correo.'
        : 'Error al procesar el registro. Intenta de nuevo más tarde.';

    echo "<script>
            alert('$message');
            window.location='../registro.php';
          </script>";
    exit;
}
?>