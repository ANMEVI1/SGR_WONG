<?php
// Archivo de prueba para verificar conexión y datos
require_once '../../../config/conexion.php';

echo "<h2>Prueba de Conexión y Datos</h2>";

try {
    $db = getDB();
    echo "<p style='color: green;'>✓ Conexión a BD exitosa</p>";
    
    // Probar consulta de empleados
    $empleados = $db->fetchAll("SELECT COUNT(*) as total FROM Empleado");
    echo "<p>Total empleados en BD: " . $empleados[0]['total'] . "</p>";
    
    // Probar consulta de roles
    $roles = $db->fetchAll("SELECT * FROM Tipo_Rol");
    echo "<p>Roles disponibles: " . count($roles) . "</p>";
    foreach ($roles as $rol) {
        echo "- " . $rol['Nombre'] . "<br>";
    }
    
    // Probar consulta de turnos
    $turnos = $db->fetchAll("SELECT * FROM Turno");
    echo "<p>Turnos disponibles: " . count($turnos) . "</p>";
    foreach ($turnos as $turno) {
        echo "- " . $turno['Descripcion'] . "<br>";
    }
    
    echo "<p style='color: green;'>✓ Todas las consultas funcionan correctamente</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>