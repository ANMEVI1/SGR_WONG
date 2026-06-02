<?php
require_once '../config/conexion.php';

startSecureSession();

// Verificar que sea una petición POST válida
if (!isset($_POST['registrar']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.php');
    exit;
}

// Obtener y sanitizar datos
$data = Validator::sanitizeArray([
    'nombre' => $_POST['nombre_completo'] ?? '',
    'documento' => $_POST['nmr_documento'] ?? '',
    'telefono' => $_POST['telefono'] ?? '',
    'correo' => $_POST['correo'] ?? '',
    'password' => $_POST['pass'] ?? ''
]);

// Validar datos
$validator = new Validator();
$validator
    ->required('nombre', $data['nombre'], 'El nombre completo es requerido')
    ->maxLength('nombre', $data['nombre'], 100, 'El nombre no puede exceder 100 caracteres')
    ->required('telefono', $data['telefono'], 'El teléfono es requerido')
    ->phone('telefono', $data['telefono'], 'Ingresa un teléfono válido')
    ->required('correo', $data['correo'], 'El correo es requerido')
    ->email('correo', $data['correo'], 'Ingresa un correo válido')
    ->required('password', $data['password'], 'La contraseña es requerida')
    ->minLength('password', $data['password'], 6, 'La contraseña debe tener al menos 6 caracteres');

// Validar documento si se proporciona
$tipoDocumento = 'DNI';
$numDocumento = '';
if (!empty($data['documento'])) {
    $documento = preg_replace('/[^0-9]/', '', $data['documento']);
    if (strlen($documento) === 11) {
        $validator->ruc('documento', $documento);
        $tipoDocumento = 'RUC';
        $numDocumento = $documento;
    } elseif (strlen($documento) === 8) {
        $validator->dni('documento', $documento);
        $tipoDocumento = 'DNI';
        $numDocumento = $documento;
    } else {
        $validator->errors['documento'] = 'Documento inválido. Ingresa DNI de 8 dígitos o RUC de 11 dígitos';
    }
}

if ($validator->hasErrors()) {
    $errors = implode('\n', $validator->getErrors());
    echo "<script>
            alert('{$errors}');
            window.location='../registro.php';
        </script>";
    exit;
}

try {
    $db = getDB();
    
    // Verificar que no exista el correo
    $existingUser = $db->fetchOne(
        "SELECT COUNT(*) as count FROM Usuario WHERE Login = :login",
        [':login' => $data['correo']]
    );
    
    if ($existingUser['count'] > 0) {
        echo "<script>
                alert('Ya existe una cuenta con ese correo.');
                window.location='../registro.php';
                </script>";
        exit;
    }
    
    // Obtener tipo de usuario para cliente web
    $tipoUsuario = $db->fetchOne(
        "SELECT TipUsuID FROM Tipo_Usuario WHERE Descripcion = 'Cliente web' LIMIT 1"
    );
    $tipoUsuarioId = $tipoUsuario['TipUsuID'] ?? 5; // Fallback a 5
    
    // Iniciar transacción
    $db->beginTransaction();
    
    try {
        // Insertar usuario
        $hashPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $usuarioId = $db->query(
            "INSERT INTO Usuario (Login, Contrasena, Estado, TipUsuID) VALUES (:login, :contrasena, 'Activo', :tipUsuID)",
            [
                ':login' => $data['correo'],
                ':contrasena' => $hashPassword,
                ':tipUsuID' => $tipoUsuarioId
            ]
        );
        $usuarioId = $db->lastInsertId();
        
        // Insertar cliente
        $db->query(
            "INSERT INTO Cliente (Nombre_Apellidos, Tipo_Documento, Num_Documento, Telefono, Direccion, Correo, Fecha_Creacion, UsuarioID) 
            VALUES (:nombre, :tipoDoc, :numDoc, :telefono, NULL, :correo, NOW(), :usuarioID)",
            [
                ':nombre' => $data['nombre'],
                ':tipoDoc' => $tipoDocumento,
                ':numDoc' => $numDocumento,
                ':telefono' => $data['telefono'],
                ':correo' => $data['correo'],
                ':usuarioID' => $usuarioId
            ]
        );
        
        $db->commit();
        
        echo "<script>
                alert('Registro exitoso. Ahora puedes iniciar sesión con tu correo y contraseña.');
                window.location='../login.php';
              </script>";
        exit;
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Registro error: ' . $e->getMessage());
    
    $message = (strpos($e->getMessage(), 'Duplicate entry') !== false)
        ? 'Ya existe una cuenta con ese correo.'
        : 'Error al procesar el registro. Intenta de nuevo más tarde.';
    
    echo "<script>
            alert('{$message}');
            window.location='../registro.php';
        </script>";
    exit;
}
?>