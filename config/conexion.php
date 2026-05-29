<?php
/**
 * Archivo de conexión - Mantiene compatibilidad con código existente
 * Usa la nueva clase Database internamente
 */

// Incluir las clases necesarias
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Services.php';

// Variables para compatibilidad con código existente
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'db_restaurante';

// Obtener instancia de Database
try {
    $db = Database::getInstance();
    $conexion = $db->getConnection(); // Para compatibilidad con mysqli
} catch (Exception $e) {
    error_log("Connection error: " . $e->getMessage());
    echo "<h2 style='color:red;'>TENEMOS DIFICULTADES PARA PROCESAR ESTO</h2>";
    exit;
}

/**
 * Función helper para obtener la instancia de Database
 */
function getDB(): Database {
    return Database::getInstance();
}

/**
 * Función helper para obtener la instancia de Services
 */
function getServices(): Services {
    static $services = null;
    if ($services === null) {
        $services = new Services();
    }
    return $services;
}

/**
 * Función helper para iniciar sesión de forma segura
 */
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración segura de sesión
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
        session_start();
    }
}

/**
 * Función helper para verificar autenticación
 */
function isAuthenticated(): bool {
    return !empty($_SESSION['usuario_id']);
}

/**
 * Función helper para obtener usuario actual
 */
function getCurrentUser(): ?array {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'UsuarioID' => $_SESSION['usuario_id'] ?? null,
        'id' => $_SESSION['usuario_id'] ?? null, // Compatibilidad
        'login' => $_SESSION['usuario_login'] ?? null,
        'rol_id' => $_SESSION['usuario_rol_id'] ?? null,
        'rol' => $_SESSION['usuario_rol'] ?? null,
        'scope' => $_SESSION['usuario_scope'] ?? null
    ];
}

/**
 * Función helper para verificar permisos
 */
function hasPermission(string $requiredScope): bool {
    $user = getCurrentUser();
    return $user && $user['scope'] === $requiredScope;
}

/**
 * Función helper para redireccionar si no está autenticado
 */
function requireAuth(string $redirectTo = '/login.php'): void {
    if (!isAuthenticated()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Función helper para redireccionar si no tiene permisos
 */
function requirePermission(string $requiredScope, string $redirectTo = '/index.php'): void {
    if (!hasPermission($requiredScope)) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
?>