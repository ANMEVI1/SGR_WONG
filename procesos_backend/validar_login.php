<?php
require_once '../config/conexion.php';

startSecureSession();

// Verificar que sea una petición POST válida
if (!isset($_POST['ingresar']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// Obtener y sanitizar datos
$email = Validator::sanitize($_POST['correo'] ?? '');
$passwordInput = $_POST['pass'] ?? '';

// Validar datos
$validator = new Validator();
$validator
    ->required('correo', $email, 'El correo es requerido')
    ->email('correo', $email, 'Ingresa un correo válido')
    ->required('pass', $passwordInput, 'La contraseña es requerida');

if ($validator->hasErrors()) {
    $errors = implode('\n', $validator->getErrors());
    echo "<script>
            alert('{$errors}');
            window.location='../login.php';
          </script>";
    exit;
}

try {
    $db = getDB();
    
    // Consulta para obtener usuario
    $sql = "SELECT u.UsuarioID, u.Login, u.Contrasena, u.Estado, u.TipUsuID,
                   t.Descripcion AS RolDescripcion, t.Scope AS RolScope
            FROM Usuario u
            JOIN Tipo_Usuario t ON u.TipUsuID = t.TipUsuID
            WHERE u.Login = :login AND u.Estado = 'Activo'
            LIMIT 1";

    $usuario = $db->fetchOne($sql, [':login' => $email]);

    if (!$usuario) {
        echo "<script>
                alert('Correo o contraseña incorrectos.');
                window.location='../login.php';
              </script>";
        exit;
    }

    // Verificar contraseña
    if (!password_verify($passwordInput, $usuario['Contrasena'])) {
        echo "<script>
                alert('Correo o contraseña incorrectos.');
                window.location='../login.php';
              </script>";
        exit;
    }

    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
    
    // Guardar información en sesión
    $_SESSION['usuario_id'] = $usuario['UsuarioID'];
    $_SESSION['usuario_login'] = $usuario['Login'];
    $_SESSION['usuario_rol_id'] = $usuario['TipUsuID'];
    $_SESSION['usuario_rol'] = $usuario['RolDescripcion'];
    $_SESSION['usuario_scope'] = $usuario['RolScope'];
    $_SESSION['login_time'] = time();

    // Redireccionar según el rol
    if ($usuario['RolScope'] === 'backoffice') {
        // Redirección específica según tipo de usuario backoffice
        switch ($usuario['TipUsuID']) {
            case 1: // Administrador
                header('Location: ../views/admin/dashboard.php');
                break;
            case 2: // Cajero
                header('Location: ../views/admin/pos/caja.php');
                break;
            case 3: // Mozo
                header('Location: ../views/admin/pedidos/index.php');
                break;
            case 4: // Almacenero
                header('Location: ../views/admin/inventario/index.php');
                break;
            default:
                // Cualquier otro rol backoffice va al dashboard
                header('Location: ../views/admin/dashboard.php');
        }
    } else {
        // Clientes web y otros usuarios van al sitio público
        header('Location: ../index.php');
    }
    exit;

} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo "<script>
            alert('Error de conexión. Intenta de nuevo más tarde.');
            window.location='../login.php';
          </script>";
    exit;
}
?>