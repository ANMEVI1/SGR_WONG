<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$loggedIn = !empty($_SESSION['usuario_login']) || !empty($_SESSION['ingresar']);
$loginName = $_SESSION['usuario_login'] ?? $_SESSION['ingresar'] ?? '';
$firstName = $loginName !== '' ? explode(' ', trim($loginName))[0] : '';
?>
<aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="logo-sidebar">Chifa Matsue</span>
            <button class="close-sidebar" aria-label="Cerrar menú"><i class="fas fa-times"></i></button>
        </div>
        <nav class="sidebar-nav">
            <?php if ($loggedIn): ?>
            <div class="sidebar-user-logged" style="margin-bottom: 10px;">
                <p style="margin: 0; font-size: 0.9rem; color: #666;">Bienvenido,</p>
                <p style="margin: 0; font-weight: 800; color: #28a745; font-size: 1.1rem;">
                    <?= htmlspecialchars($firstName ?: 'Usuario', ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
            <?php else: ?>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i><span>INICIAR SESIÓN</span></a>
            <?php endif; ?>
            <a href="#menu"><i class="fas fa-utensils"></i><span>Menú Completo</span></a>
            <a href="#top-ventas"><i class="fas fa-fire"></i><span>Favoritos</span></a>
            <a href="#promociones"><i class="fas fa-tag"></i><span>Promociones</span></a>
            <a href="#menu"><i class="fas fa-bowl-food"></i><span>Sopas</span></a>
            <a href="#menu"><i class="fas fa-utensils"></i><span>Chaufa</span></a>
            <a href="#menu"><i class="fas fa-bowl-food"></i><span>Tallarines</span></a>
            <a href="#menu"><i class="fas fa-drumstick-bite"></i><span>Pollo</span></a>
            <a href="#menu"><i class="fas fa-fish"></i><span>Langostinos</span></a>
            <a href="#menu"><i class="fas fa-fish"></i><span>Pescados</span></a>
            <a href="#menu"><i class="fas fa-cloud"></i><span>Vapor</span></a>
            <a href="#menu"><i class="fas fa-plate-utensils"></i><span>Combinados</span></a>
            <a href="#menu"><i class="fas fa-mug-hot"></i><span>Bebidas</span></a>
        </nav>
    </aside>
    <div class="overlay" id="overlay"></div>