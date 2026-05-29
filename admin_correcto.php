<?php
require_once 'config/conexion.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Delete existing records
    $pdo->exec("DELETE FROM empleado WHERE DNI = '87654321'");
    $pdo->exec("DELETE FROM usuario WHERE Login = 'admin@test.com'");
    
    // Create admin user
    $stmt = $pdo->prepare("INSERT INTO usuario (Login, Contrasena, Estado, TipUsuID) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin@test.com', password_hash('admin123', PASSWORD_DEFAULT), 'activo', 1]);
    
    $usuario_id = $pdo->lastInsertId();
    
    // Create employee record
    $stmt = $pdo->prepare("INSERT INTO empleado (Nombre_Apellidos, DNI, Telefono, Sueldo, Estado, Fecha_Contratacion, RolID, TurnoID, UsuarioID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin Sistema', '87654321', '999999999', 3000.00, 'activo', date('Y-m-d H:i:s'), 1, 1, $usuario_id]);
    
    echo "✅ ADMIN CREADO EXITOSAMENTE<br><br>";
    echo "<strong>Credenciales:</strong><br>";
    echo "Login: admin@test.com<br>";
    echo "Password: admin123<br>";
    echo "DNI: 87654321<br><br>";
    echo "Ahora puedes hacer login en el sistema.";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>