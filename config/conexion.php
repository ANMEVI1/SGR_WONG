<?php 
$host= 'localhost';
$user= 'root';
$password= '';
$database= 'db_restaurante';

$conexion= new mysqli($host,$user,$password,$database);

if ($conexion->connect_error){
    echo "<h2 style='color:red;'>TENEMOS DIFICULTADES PARA PROCESAR ESTO</h2>";
    exit;
}
?>