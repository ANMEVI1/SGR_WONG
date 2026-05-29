<?php
require_once '../config/conexion.php';

startSecureSession();
requireAuth();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Método no permitido', 405);
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    Response::unauthorized('Sesión expirada');
}

// Obtener y sanitizar datos
$data = Validator::sanitizeArray([
    'nombre' => $_POST['nombre_completo'] ?? '',
    'correo' => $_POST['correo'] ?? '',
    'tipo_documento' => $_POST['tipo_documento'] ?? 'DNI',
    'nmr_documento' => $_POST['nmr_documento'] ?? '',
    'telefono' => $_POST['telefono'] ?? '',
    'direccion' => $_POST['direccion'] ?? ''
]);

// Validar datos
$validator = new Validator();
$validator
    ->required('nombre', $data['nombre'], 'El nombre es requerido')
    ->maxLength('nombre', $data['nombre'], 100, 'El nombre no puede exceder 100 caracteres')
    ->required('correo', $data['correo'], 'El correo es requerido')
    ->email('correo', $data['correo'], 'Ingresa un correo válido')
    ->required('telefono', $data['telefono'], 'El teléfono es requerido')
    ->phone('telefono', $data['telefono'], 'Ingresa un teléfono válido');

// Validar documento si se proporciona
if (!empty($data['nmr_documento'])) {
    $documento = preg_replace('/[^0-9]/', '', $data['nmr_documento']);
    if ($data['tipo_documento'] === 'DNI') {
        $validator->dni('nmr_documento', $documento);
    } elseif ($data['tipo_documento'] === 'RUC') {
        $validator->ruc('nmr_documento', $documento);
    }
    $data['nmr_documento'] = $documento;
}

if ($validator->hasErrors()) {
    Response::validation($validator->getErrors());
}

try {
    $db = getDB();
    
    // Verificar si el email ya existe (si es diferente al actual)
    if ($data['correo'] !== $currentUser['login']) {
        $existingUser = $db->fetchOne(
            "SELECT COUNT(*) as count FROM Usuario WHERE Login = :correo AND UsuarioID != :userId",
            [':correo' => $data['correo'], ':userId' => $currentUser['id']]
        );
        
        if ($existingUser['count'] > 0) {
            Response::error('Ya existe otra cuenta con ese correo', 422);
        }
    }
    
    // Iniciar transacción
    $db->beginTransaction();
    
    try {
        // Actualizar Usuario
        $db->execute(
            "UPDATE Usuario SET Login = :correo WHERE UsuarioID = :userId",
            [':correo' => $data['correo'], ':userId' => $currentUser['id']]
        );
        
        // Actualizar Cliente
        $db->execute(
            "UPDATE Cliente SET 
                Nombre_Apellidos = :nombre,
                Tipo_Documento = :tipoDoc,
                Num_Documento = :numDoc,
                Telefono = :telefono,
                Direccion = :direccion,
                Correo = :correo
             WHERE UsuarioID = :userId",
            [
                ':nombre' => $data['nombre'],
                ':tipoDoc' => $data['tipo_documento'],
                ':numDoc' => $data['nmr_documento'],
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'],
                ':correo' => $data['correo'],
                ':userId' => $currentUser['id']
            ]
        );
        
        $db->commit();
        
        // Actualizar sesión si cambió el email
        if ($data['correo'] !== $currentUser['login']) {
            $_SESSION['usuario_login'] = $data['correo'];
        }
        
        Response::success([], 'Tus datos se actualizaron correctamente');
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Error updating profile: ' . $e->getMessage());
    Response::error('Error al guardar el perfil. Intenta de nuevo más tarde');
}
?>