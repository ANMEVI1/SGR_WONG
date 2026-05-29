<?php
// Detectar profundidad para rutas dinámicas
$currentPath = $_SERVER['PHP_SELF'];
$depth = substr_count(dirname($currentPath), '/');
$baseUrl = str_repeat('../', $depth);
?>
    <link rel="stylesheet" href="<?= $baseUrl ?>css/estilos.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/header.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/login-registro.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/modal-perfil.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/responsivo.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/reclamaciones.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/carrito.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>css/modal-perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
