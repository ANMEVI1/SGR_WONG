<?php
require_once 'config/conexion.php';
startSecureSession();

$currentUser = getCurrentUser();
$platoId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$platoId) {
    header('Location: carta.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Plato - Chifa Matsue</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/detalle-producto.css">
    <link rel="stylesheet" href="css/cards-platos.css">
    <link rel="stylesheet" href="css/modal-perfil.css">
    <link rel="stylesheet" href="css/responsivo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/aside-rol.php'; ?>
    <?php include 'includes/aside.php'; ?>

    <main class="producto-detalle container">
        <div class="producto-grid" id="productoContainer">
            <!-- Skeleton loading -->
            <div class="skeleton" style="height: 500px; border-radius: 16px;"></div>
            <div>
                <div class="skeleton" style="height: 40px; width: 70%; margin-bottom: 16px;"></div>
                <div class="skeleton" style="height: 20px; width: 100%; margin-bottom: 8px;"></div>
                <div class="skeleton" style="height: 20px; width: 90%; margin-bottom: 24px;"></div>
                <div class="skeleton" style="height: 60px; width: 200px;"></div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Botón WhatsApp Flotante -->
    <a href="https://wa.me/+51991183777?text=Hola,%20quiero%20hacer%20un%20pedido" 
       target="_blank" 
       class="whatsapp-btn" 
       aria-label="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script>
        const PLATO_ID = <?php echo $platoId; ?>;
    </script>
    <script src="js/app.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/modal-perfil.js"></script>
    <script src="js/detalle-plato.js"></script>
</body>
</html>
