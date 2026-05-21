<?php
// Script de prueba: establece una sesión simulando un usuario con rol 1
session_start();
// Valores de prueba
$_SESSION['cliente_nom'] = 'Admin Test';
$_SESSION['cliente_id'] = 1;
$_SESSION['cliente_rol'] = 1;

// Redirigir al índice para ver la cabecera
header('Location: index.php');
exit;
?>
