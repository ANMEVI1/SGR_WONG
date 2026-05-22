<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - Chifa Matsue</title>
    <link rel="stylesheet" href="/css/estilos.css">
    <link rel="stylesheet" href="/css/carrito.css">
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/responsivo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php include ('../../includes/header.php'); ?>
    <?php include '../../includes/aside.php'?>
    
    <main class="container carrito-section">
        <h1 class="section-title">Tu Carrito</h1>

        <div id="carritoItems" class="carrito-items">
        </div>

        <div class="carrito-resumen">
            <div class="resumen-line"><span>Subtotal:</span> <strong id="subtotal">S/ 0.00</strong></div>
            <div class="resumen-line"><span>Delivery:</span> <strong>S/ 8.00</strong></div>
            <div class="resumen-total"><span>Total:</span> <strong id="total">S/ 8.00</strong></div>
            <button id="btnPagar" class="btn btn-primary btn-full">Proceder al Pago</button>
            <a href="index.php" class="btn btn-secondary btn-full">Seguir Comprando</a>
        </div>
    </main>
<?php include ('../../includes/footer.php') ?>
    <script src="/js/carrito.js"></script>
    <script src="/js/sidebar.js"></script>
</body>
</html>