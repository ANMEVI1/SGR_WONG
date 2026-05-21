<?php
session_start();
if (!isset($_SESSION['cliente_rol']) || $_SESSION['cliente_rol'] != 1) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Acceso denegado';
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Administrar Negocio</title>
</head>
<body>
    <h1>Administrar Negocio (Panel)</h1>
    <p>Página de administración para la gestión del negocio.</p>
    <p><a href="index.php">Volver</a></p>
</body>
</html>
