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
            <img src="assets/plato ramen.jpg" alt="Logo Chifa Matsue">
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
                        <span class="user-status-icon" aria-hidden="true"><i class="fas fa-user-circle"></i></span>
                        <span class="user-email"><?= htmlspecialchars($loginName, ENT_QUOTES, 'UTF-8') ?></span>
                        <a href="procesos_backend/cerrar_sesion.php" class="logout-link" aria-label="Cerrar sesión">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                            Cerrar sesión
                        </a>
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