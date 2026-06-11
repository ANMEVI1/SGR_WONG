<?php
// Detectar ubicación actual y calcular rutas base
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Calcular el prefijo de ruta según la ubicación actual
if ($currentDir === 'admin') {
    // Estamos en /views/admin/
    $baseUrl = '';
} else {
    // Estamos en un subdirectorio /views/admin/xxx/
    $baseUrl = '../';
}
?>
<div class="admin-sidebar">
    <div style="padding: 25px; border-bottom: 2px solid var(--admin-border);">
        <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-utensils" style="color: var(--admin-primary); font-size: 1.5rem;"></i>
            Chifa Matsue
        </h3>
        <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Administración</small>
    </div>
    
    <nav class="admin-nav" style="padding: 25px 0;">
        <a href="<?= $baseUrl ?>dashboard.php" class="nav-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <div class="nav-section">
            <div class="nav-section-title">PUNTO DE VENTA</div>
            <a href="<?= $baseUrl ?>pos/caja.php" class="nav-item <?= $currentPage === 'caja.php' ? 'active' : '' ?>">
                <i class="fas fa-cash-register"></i> Caja
            </a>
            <a href="<?= $baseUrl ?>pedidos/index.php" class="nav-item <?= $currentPage === 'index.php' && $currentDir === 'pedidos' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a>
            <a href="<?= $baseUrl ?>pos/clientes.php" class="nav-item <?= $currentPage === 'clientes.php' ? 'active' : '' ?>">
                <i class="fas fa-address-book"></i> Clientes
            </a>
            <a href="<?= $baseUrl ?>pos/mesas.php" class="nav-item <?= $currentPage === 'mesas.php' ? 'active' : '' ?>">
                <i class="fas fa-table"></i> Mesas
            </a>
            <a href="<?= $baseUrl ?>pos/metodos-pago.php" class="nav-item <?= $currentPage === 'metodos-pago.php' ? 'active' : '' ?>">
                <i class="fas fa-credit-card"></i> Métodos de Pago
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">MENÚ Y PRODUCTOS</div>
            <a href="<?= $baseUrl ?>menu/index.php" class="nav-item <?= $currentPage === 'index.php' && $currentDir === 'menu' ? 'active' : '' ?>">
                <i class="fas fa-utensils"></i> Platos
            </a>
            <a href="<?= $baseUrl ?>menu/categorias.php" class="nav-item <?= $currentPage === 'categorias.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Categorías
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">PERSONAL</div>
            <a href="<?= $baseUrl ?>personal/empleados.php" class="nav-item <?= $currentPage === 'empleados.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Empleados
            </a>
            <a href="<?= $baseUrl ?>personal/roles.php" class="nav-item <?= $currentPage === 'roles.php' ? 'active' : '' ?>">
                <i class="fas fa-user-tag"></i> Roles
            </a>
            <a href="<?= $baseUrl ?>personal/turnos.php" class="nav-item <?= $currentPage === 'turnos.php' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> Turnos
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">INVENTARIO</div>
            <a href="<?= $baseUrl ?>inventario/index.php" class="nav-item <?= $currentPage === 'index.php' && $currentDir === 'inventario' ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i> Insumos
            </a>
            <a href="<?= $baseUrl ?>inventario/proveedores.php" class="nav-item <?= $currentPage === 'proveedores.php' ? 'active' : '' ?>">
                <i class="fas fa-truck"></i> Proveedores
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">REPORTES Y ANÁLISIS</div>
            <a href="<?= $baseUrl ?>reportes/index.php" class="nav-item <?= $currentPage === 'index.php' && $currentDir === 'reportes' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Reportes
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">CONTENIDO WEB</div>
            <a href="<?= $baseUrl ?>web/contenido.php" class="nav-item <?= $currentPage === 'contenido.php' ? 'active' : '' ?>">
                <i class="fas fa-globe"></i> Contenido Web
            </a>
            <a href="<?= $baseUrl ?>usuarios/index.php" class="nav-item <?= $currentPage === 'index.php' && $currentDir === 'usuarios' ? 'active' : '' ?>">
                <i class="fas fa-user-cog"></i> Usuarios Sistema
            </a>
        </div>
        
        <hr style="margin: 25px 20px; border: none; border-top: 1px solid var(--admin-border);">
        
        <a href="<?= $baseUrl ?>../../index.php" class="nav-item">
            <i class="fas fa-home"></i> Volver al Sitio
        </a>
        
        <a href="<?= $baseUrl ?>../../procesos_backend/cerrar_sesion.php" class="nav-item" style="color: #dc3545;">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </nav>
</div>
