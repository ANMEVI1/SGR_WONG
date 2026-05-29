<?php
require_once __DIR__ . '/../config/conexion.php';
startSecureSession();

$loggedIn = isAuthenticated();
$currentUser = getCurrentUser();
$firstName = $currentUser ? explode(' ', trim($currentUser['login']))[0] : '';

// Detectar profundidad para rutas dinámicas
$currentPath = $_SERVER['PHP_SELF'];
$depth = substr_count(dirname($currentPath), '/');
$baseUrl = str_repeat('../', $depth);
?>
<header class="header">
    <div class="header-container">

        <!-- IZQUIERDA: Hamburguesa + Logo -->
        <div class="header-left">
            <button class="menu-toggle" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?= $baseUrl ?>index.php" class="logo">
                <img src="<?= $baseUrl ?>assets/plato ramen.jpg" alt="Logo Matsue">
                <span>Matsue</span>
            </a>
        </div>

        <!-- CENTRO: Navegación -->
        <nav class="nav-menu" aria-label="Navegación principal">
            <a href="<?= $baseUrl ?>index.php" class="nav-link">INICIO</a>
            <a href="<?= $baseUrl ?>carta.php" class="nav-link">LA CARTA</a>
            <a href="<?= $baseUrl ?>reservas.php" class="nav-link">RESERVAS</a>
        </nav>

        <!-- DERECHA: Búsqueda + Carrito + Sesión -->
        <div class="header-right">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Buscar plato..." aria-label="Buscar plato">
                <i class="fas fa-search" aria-hidden="true"></i>
                <div class="search-results" id="searchResults" role="listbox"></div>
            </div>

            <div class="cart-icon">
                <a href="<?= $baseUrl ?>views/cliente/carrito.php" aria-label="Ver carrito">
                    <i class="fas fa-shopping-cart"></i>
                    <span id="cartCount">0</span>
                </a>
            </div>

            <div class="profile-wrapper">
                <?php if ($loggedIn): ?>
                    <div class="user-logged">
                        <button id="openProfile" class="profile-button" aria-label="Mi cuenta">
                            <i class="fas fa-user-circle" aria-hidden="true"></i>
                            <span class="profile-label">Mi cuenta</span>
                        </button>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>login.php" class="profile-button" aria-label="Iniciar sesión">
                        <i class="fas fa-user" aria-hidden="true"></i>
                        <span class="profile-label">Iniciar sesión</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>