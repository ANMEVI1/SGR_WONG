<?php
require_once 'config/conexion.php';
startSecureSession();

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Carta completa de Chifa Matsue - Sopas, chaufas, tallarines y más">
    <title>La Carta - Chifa Matsue</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/header.css">
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

    <main>
        <!-- Menú Completo con Filtros -->
        <section class="menu-full" id="menu" style="padding-top: 100px;">
            <div class="container">
                <div class="section-header">
                    <span class="section-eyebrow">Carta Completa</span>
                    <h2 class="section-title" style="color: white;">Nuestro Menú</h2>
                    <p class="section-subtitle" style="color: white;">Explora nuestra variedad de platos tradicionales</p>
                </div>

                <!-- Filtros de categoría -->
                <div class="menu-filters" id="menuFilters">
                    <button class="filter-btn active" data-cat="all">
                        <i class="fas fa-th"></i> Todos
                    </button>
                    <button class="filter-btn" data-cat="sopas">
                        <i class="fas fa-bowl-food"></i> Sopas
                    </button>
                    <button class="filter-btn" data-cat="chaufa">
                        <i class="fas fa-rice-bowl"></i> Chaufa
                    </button>
                    <button class="filter-btn" data-cat="tallarines">
                        <i class="fas fa-utensils"></i> Tallarines
                    </button>
                    <button class="filter-btn" data-cat="pollo">
                        <i class="fas fa-drumstick-bite"></i> Pollo
                    </button>
                    <button class="filter-btn" data-cat="langostinos">
                        <i class="fas fa-shrimp"></i> Mariscos
                    </button>
                    <button class="filter-btn" data-cat="bebidas">
                        <i class="fas fa-glass-water"></i> Bebidas
                    </button>
                </div>

                <!-- Grid de productos -->
                <div class="menu-grid" id="menuGrid">
                    <!-- Generado dinámicamente por JavaScript -->
                </div>
            </div>
        </section>

        <!-- Sección de Descarga de Carta PDF -->
        <section class="about" style="margin-top: 60px;">
            <div class="container">
                <div class="section-header">
                    <span class="section-eyebrow">Descarga</span>
                    <h2 class="section-title" style="color: white;">Carta en PDF</h2>
                    <p class="section-subtitle" style="color: white;">Descarga nuestra carta completa para verla offline</p>
                </div>
                <div style="text-align: center; margin-top: 40px;">
                    <a href="assets/CARTAS_MATSUE/LA_CARTA_PDF/CARTA MATSUE.pdf" 
                       class="btn btn-primary" 
                       download
                       style="font-size: 1.1rem; padding: 16px 40px;">
                        <i class="fas fa-download"></i> Descargar Carta PDF
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Botón WhatsApp Flotante -->
    <a href="https://wa.me/+51991183777?text=Hola,%20quiero%20hacer%20un%20pedido" 
       target="_blank" 
       class="whatsapp-btn" 
       aria-label="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="js/app.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/modal-perfil.js"></script>
</body>
</html>