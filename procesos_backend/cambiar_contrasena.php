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
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validar datos
$validator = new Validator();
$validator
    ->required('current_password', $currentPassword, 'La contraseña actual es requerida')
    ->required('new_password', $newPassword, 'La nueva contraseña es requerida')
    ->minLength('new_password', $newPassword, 6, 'La nueva contraseña debe tener al menos 6 caracteres')
    ->required('confirm_password', $confirmPassword, 'Confirma la nueva contraseña');

if ($newPassword !== $confirmPassword) {
    $validator->errors['confirm_password'] = 'Las contraseñas no coinciden';
}

if ($validator->hasErrors()) {
    Response::validation($validator->getErrors());
}

try {
    $services = getServices();
    
    // Cambiar contraseña usando el servicio
    $result = $services->changePassword($currentUser['id'], $currentPassword, $newPassword);
    
    if ($result['success']) {
        Response::success([], $result['message']);
    } else {
        Response::error($result['message'], 422);
    }
    
} catch (Exception $e) {
    error_log('Error changing password: ' . $e->getMessage());
    Response::error('Error al cambiar la contraseña. Intenta de nuevo más tarde');
}
?>