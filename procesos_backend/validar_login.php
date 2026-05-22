<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_POST['ingresar'])) {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['correo'] ?? '');
$passwordInput = $_POST['pass'] ?? '';

if ($email === '' || $passwordInput === '') {
    echo "<script>
            alert('Debes ingresar correo y contraseña.');
            window.location='../login.php';
          </script>";
    exit;
}

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $sql = "SELECT u.UsuarioID, u.Login, u.Contrasena, u.Estado, u.TipUsuID,
                   t.Descripcion AS RolDescripcion, t.Scope AS RolScope
            FROM Usuario u
            JOIN Tipo_Usuario t ON u.TipUsuID = t.TipUsuID
            WHERE u.Login = :login
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':login' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        echo "<script>
                alert('Correo o contraseña incorrectos.');
                window.location='../login.php';
              </script>";
        exit;
    }

    if ($usuario['Estado'] !== 'Activo') {
        echo "<script>
                alert('Tu cuenta no está activa. Contacta al administrador.');
                window.location='../login.php';
              </script>";
        exit;
    }

    if (!password_verify($passwordInput, $usuario['Contrasena'])) {
        echo "<script>
                alert('Correo o contraseña incorrectos.');
                window.location='../login.php';
              </script>";
        exit;
    }

    // Exitoso: guardamos información mínima en sesión
    $_SESSION['usuario_id'] = $usuario['UsuarioID'];
    $_SESSION['usuario_login'] = $usuario['Login'];
    $_SESSION['usuario_rol_id'] = $usuario['TipUsuID'];
    $_SESSION['usuario_rol'] = $usuario['RolDescripcion'];
    $_SESSION['usuario_scope'] = $usuario['RolScope'];

    if ($usuario['RolScope'] === 'backoffice') {
        header('Location: ../admin_web.php');
    } else {
        header('Location: ../index.php');
    }
    exit;

} catch (PDOException $e) {
    error_log('Login PDO error: ' . $e->getMessage());
    echo "<script>
            alert('Error de conexión. Intenta de nuevo más tarde.');
            window.location='../login.php';
          </script>";
    exit;
}
?>