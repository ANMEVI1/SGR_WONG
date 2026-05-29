<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

header('Content-Type: application/json');

try {
    $db = getDB();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            createUser($db);
            break;
        case 'update':
            updateUser($db);
            break;
        case 'toggleStatus':
            toggleUserStatus($db);
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function createUser($db) {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $roleId = (int)($_POST['role'] ?? 0);
    
    // Validaciones básicas
    if (empty($login) || empty($password) || empty($roleId)) {
        throw new Exception('Todos los campos obligatorios deben completarse');
    }
    
    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El email no tiene un formato válido');
    }
    
    if ($password !== $confirmPassword) {
        throw new Exception('Las contraseñas no coinciden');
    }
    
    if (strlen($password) < 6) {
        throw new Exception('La contraseña debe tener al menos 6 caracteres');
    }
    
    // Verificar si el email ya existe
    $existingUser = $db->fetchOne(
        "SELECT UsuarioID FROM Usuario WHERE Login = :login",
        [':login' => $login]
    );
    
    if ($existingUser) {
        throw new Exception('Ya existe un usuario con este email');
    }
    
    // Obtener información del rol
    $roleInfo = $db->fetchOne(
        "SELECT Scope FROM Tipo_Usuario WHERE TipUsuID = :roleId",
        [':roleId' => $roleId]
    );
    
    if (!$roleInfo) {
        throw new Exception('Rol no válido');
    }
    
    $db->beginTransaction();
    
    try {
        // Crear usuario
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $db->execute(
            "INSERT INTO Usuario (Login, Contrasena, Estado, TipUsuID) VALUES (:login, :password, 'Activo', :roleId)",
            [
                ':login' => $login,
                ':password' => $hashedPassword,
                ':roleId' => $roleId
            ]
        );
        
        $userId = $db->lastInsertId();
        
        // Si es un cliente web, crear registro en tabla Cliente
        if ($roleInfo['Scope'] === 'web') {
            $clientName = trim($_POST['clientName'] ?? '');
            $clientPhone = trim($_POST['clientPhone'] ?? '');
            $clientAddress = trim($_POST['clientAddress'] ?? '');
            
            if (empty($clientName) || empty($clientPhone)) {
                throw new Exception('Nombre y teléfono son obligatorios para clientes');
            }
            
            // Generar DNI temporal si no se proporciona
            $tempDni = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            
            $db->execute(
                "INSERT INTO Cliente (Nombre_Apellidos, Tipo_Documento, Num_Documento, Telefono, Direccion, Correo, UsuarioID) 
                 VALUES (:nombre, 'DNI', :dni, :telefono, :direccion, :correo, :userId)",
                [
                    ':nombre' => $clientName,
                    ':dni' => $tempDni,
                    ':telefono' => $clientPhone,
                    ':direccion' => $clientAddress,
                    ':correo' => $login,
                    ':userId' => $userId
                ]
            );
        }
        // Si es empleado/admin, crear registro en tabla Empleado
        else if ($roleInfo['Scope'] === 'backoffice') {
            $employeeName = trim($_POST['clientName'] ?? 'Empleado Sistema');
            $tempDni = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            
            $db->execute(
                "INSERT INTO Empleado (Nombre_Apellidos, DNI, Telefono, Sueldo, Estado, Fecha_Contratacion, RolID, TurnoID, UsuarioID) 
                 VALUES (:nombre, :dni, '999999999', 3000.00, 'activo', NOW(), 1, 1, :userId)",
                [
                    ':nombre' => $employeeName,
                    ':dni' => $tempDni,
                    ':userId' => $userId
                ]
            );
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

function updateUser($db) {
    $userId = (int)($_POST['userId'] ?? 0);
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $roleId = (int)($_POST['role'] ?? 0);
    
    if (empty($userId) || empty($login) || empty($roleId)) {
        throw new Exception('Datos incompletos para actualizar');
    }
    
    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El email no tiene un formato válido');
    }
    
    // Verificar si el email ya existe en otro usuario
    $existingUser = $db->fetchOne(
        "SELECT UsuarioID FROM Usuario WHERE Login = :login AND UsuarioID != :userId",
        [':login' => $login, ':userId' => $userId]
    );
    
    if ($existingUser) {
        throw new Exception('Ya existe otro usuario con este email');
    }
    
    // Si se proporciona contraseña, validarla
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            throw new Exception('Las contraseñas no coinciden');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres');
        }
    }
    
    $db->beginTransaction();
    
    try {
        // Actualizar usuario
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $db->execute(
                "UPDATE Usuario SET Login = :login, Contrasena = :password, TipUsuID = :roleId WHERE UsuarioID = :userId",
                [
                    ':login' => $login,
                    ':password' => $hashedPassword,
                    ':roleId' => $roleId,
                    ':userId' => $userId
                ]
            );
        } else {
            $db->execute(
                "UPDATE Usuario SET Login = :login, TipUsuID = :roleId WHERE UsuarioID = :userId",
                [
                    ':login' => $login,
                    ':roleId' => $roleId,
                    ':userId' => $userId
                ]
            );
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

function toggleUserStatus($db) {
    $userId = (int)($_POST['userId'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    if (empty($userId) || !in_array($status, ['Activo', 'Inactivo'])) {
        throw new Exception('Datos inválidos para cambiar estado');
    }
    
    // No permitir desactivar el usuario actual
    $currentUser = getCurrentUser();
    if ($userId == $currentUser['id']) {
        throw new Exception('No puedes desactivar tu propio usuario');
    }
    
    $db->execute(
        "UPDATE Usuario SET Estado = :status WHERE UsuarioID = :userId",
        [':status' => $status, ':userId' => $userId]
    );
    
    echo json_encode([
        'success' => true,
        'message' => "Usuario {$status} exitosamente"
    ]);
}
?>