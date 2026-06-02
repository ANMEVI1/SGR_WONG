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
    <meta name="description" content="Chifa Matsue - Sabores chinos y peruanos en un solo lugar. Menú de chifa, chaufa, tallarines y más.">
    <title>Chifa Matsue - Menú de Chifa y Chino-Peruano</title>
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
    <?php include 'includes/header.php';?>
    <?php include 'includes/aside-rol.php'; ?>
    <?php include 'includes/aside.php'?>
    <main>

        <!-- ===== HERO ===== -->
        <section class="hero" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/ramen_IVI5301-scaled.jpg') center/cover;" aria-labelledby="hero-title">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <p class="hero-eyebrow" style="color: var(--gold-light);">Cocina chino-peruana auténtica</p>
                <h1 id="hero-title" style="color: white;">CHIFA<br>MATSUE</h1>
                <p class="hero-subtitle" style="color: rgba(255, 255, 255, 0.8);">Tradición, sabor y calidez en cada plato</p>
                <div class="hero-buttons">
                    <a href="carta.php" class="btn btn-primary">
                        <i class="fas fa-utensils"></i> VER LA CARTA
                    </a>
                    <a href="reservas.php" class="btn btn-secondary">
                        HACER RESERVA
                    </a>
                </div>
            </div>
        </section>

        <!-- Top Ventas -->
        <section class="top-ventas container" id="top-ventas">
            <h2 class="section-title" style="color: white;">Top Ventas</h2>
            <p class="section-subtitle" style="color: white;">Los favoritos de nuestros clientes</p>
            <div class="mini-carousel"></div>
        </section>

        <!-- Promociones -->
        <section class="promociones container" id="promociones">
            <h2 class="section-title" style="color: white;">Promociones</h2>
            <p class="section-subtitle" style="color: white;">Ofertas especiales que no puedes perderte</p>
            <div class="mini-carousel"></div>
        </section>

        <!-- Menú Completo -->
        <section class="menu-full container" id="menu">
            <h2 class="section-title" style="color: white;">Menú del Chifa</h2>
            <p class="section-subtitle" style="color: white;">Descubre lo mejor del chifa Matsue: sopas, chaufas, tallarines y más</p>
            <div class="menu-grid" id="menuGrid"></div>
        </section>

        <!-- Sobre Nosotros -->
        <section class="about">
    <div class="container">
        <h2 class="section-title" style="color: white;">Sobre Nosotros</h2>
        <p class="section-subtitle" style="color: white;">Conoce más sobre Chifa Matsue</p>
        
        <div class="about-grid">
            
            
            <div class="about-card">
                <div class="about-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>¿Quiénes somos?</h3>
                <p>Somos Chifa Matsue, un lugar especializado en cocina chino-peruana con recetas auténticas y sabores para toda la familia.</p>
            </div>
            
            <div class="about-card">
                <div class="about-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Política de Privacidad</h3>
                <p>Protegemos tus datos personales y garantizamos la confidencialidad de tu información. Tu confianza es nuestra prioridad.</p>
                <a href="#politica">Leer política completa</a>
            </div>
            
            <div class="about-card">
                <div class="about-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Trabaja con Nosotros</h3>
                <p>¿Te apasiona la gastronomía? Únete a nuestro equipo y crece profesionalmente con nosotros.</p>
                <p><strong>Envía tu CV a:</strong></p>
                <a href="mailto:rrhh@expressate.com">rrhh@expressate.com</a>
            </div>
        </div>
    </div>
</section>
        <!-- Sección de Ubicación con Mapa -->
        <section class="location-section">
            <div class="location-container">
                <div class="location-header">
                    <h2 class="section-title" style="color: white;">Ubícanos en el Mapa</h2>
                    <p class="section-subtitle" style="color: white;">Visítanos en nuestro local de Zorrillos</p>
                </div>
                <div class="location-content">
                    <div class="map-wrapper">
                        <iframe src="https://www.google.com/maps?q=Zorrillos,+Tumbes,+Peru&output=embed" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <div class="location-info">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Dirección</h4>
                                <p>Zorrillos, Tumbes, Perú</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Horario</h4>
                                <p>Lunes a Domingo</p>
                                <p>11:00 AM - 10:00 PM</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Teléfono</h4>
                                <p>+51 991 183 777</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'includes/footer.php';?>
    <!-- Botón WhatsApp Flotante -->
    <a href="https://wa.me/+51991183777?text=Hola,%20quiero%20hacer%20un%20pedido" target="_blank" class="whatsapp-btn" aria-label="Contactar por WhatsApp" title="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    <script src="js/app.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/modal-perfil.js"></script>
</body>
</html>