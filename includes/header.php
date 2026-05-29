<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = !empty($_SESSION['usuario_login']) || !empty($_SESSION['ingresar']);
$loginName = $_SESSION['usuario_login'] ?? $_SESSION['ingresar'] ?? '';
$firstName = $loginName !== '' ? explode(' ', trim($loginName))[0] : '';
?>
<header class="header">
    <section class="container header-container">
        <button class="menu-toggle" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>

        <a href="index.php" class="logo">
            <img src="assets/" alt="Logo Chifa Matsue">
            <span>Matsue</span>
        </a>

        <div class="header-right">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Buscar plato..." aria-label="Buscar plato">
                <i class="fas fa-search"></i>
                <div class="search-results" id="searchResults" role="listbox"></div>
            </div>

            <div class="cart-icon">
                <a href="views/cliente/carrito.php" aria-label="Ver carrito">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
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
                    <a href="login.php" class="profile-button" aria-label="Iniciar sesión">
                        <i class="fas fa-user" aria-hidden="true"></i>
                        <span class="profile-label">Iniciar sesión</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</header>