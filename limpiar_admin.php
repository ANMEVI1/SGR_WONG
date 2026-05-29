<?php
require_once 'config/conexion.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Delete existing admin records to start fresh
    $pdo->exec("DELETE FROM empleado WHERE DNI IN ('12345678', '87654321')");
    $pdo->exec("DELETE FROM usuario WHERE email = 'admin@test.com'");
    
    // Create new admin user
    $stmt = $pdo->prepare("INSERT INTO usuario (nombre, apellido, email, password, scope) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'Sistema', 'admin@test.com', password_hash('admin123', PASSWORD_DEFAULT), 'backoffice']);
    
    $usuario_id = $pdo->lastInsertId();
    
    // Create employee record
    $stmt = $pdo->prepare("INSERT INTO empleado (DNI, usuario_id, cargo, fecha_contratacion, salario, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['87654321', $usuario_id, 'Administrador', date('Y-m-d'), 3000.00, 'activo']);
    
    echo "✅ Admin creado exitosamente:<br>";
    echo "Email: admin@test.com<br>";
    echo "Password: admin123<br>";
    echo "DNI: 87654321";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>