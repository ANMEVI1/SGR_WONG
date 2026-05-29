<!-- Admin Sidebar Navigation (Modelo Completo) -->
<div class="admin-sidebar">
    <div style="padding: 25px; border-bottom: 2px solid var(--admin-border);">
        <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-utensils" style="color: var(--admin-primary); font-size: 1.5rem;"></i>
            Chifa Matsue
        </h3>
        <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Panel de Administración</small>
    </div>
    <nav class="admin-nav" style="padding: 25px 0;">
        <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'dashboard.php'; ?>" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <!-- PUNTO DE VENTA -->
        <div class="nav-section">
            <div class="nav-section-title">PUNTO DE VENTA</div>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'pos/caja.php'; ?>" class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'pos/caja') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cash-register"></i> Caja
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'pedidos/index.php'; ?>" class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'pedidos') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Pedidos
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'pos/clientes.php'; ?>" class="nav-item">
                <i class="fas fa-users"></i> Clientes
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'pos/mesas.php'; ?>" class="nav-item">
                <i class="fas fa-table"></i> Mesas
            </a>
        </div>
        
        <!-- GESTIÓN DEL NEGOCIO -->
        <div class="nav-section">
            <div class="nav-section-title">GESTIÓN DEL NEGOCIO</div>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'menu/index.php'; ?>" class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'menu/index') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> Menú
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'usuarios/index.php'; ?>" class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'usuarios') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-user-tie"></i> Empleados
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'reportes/index.php'; ?>" class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'reportes') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Reportes
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'negocio/configuracion.php'; ?>" class="nav-item">
                <i class="fas fa-cogs"></i> Configuración
            </a>
        </div>
        
        <!-- INVENTARIO -->
        <div class="nav-section">
            <div class="nav-section-title">INVENTARIO</div>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'inventario/index.php'; ?>" class="nav-item <?php echo (strpos($_SERVER['PHP_SELF'], 'inventario') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-boxes"></i> Stock
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'inventario/movimientos.php'; ?>" class="nav-item">
                <i class="fas fa-exchange-alt"></i> Movimientos
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'inventario/proveedores.php'; ?>" class="nav-item">
                <i class="fas fa-truck"></i> Proveedores
            </a>
        </div>
        
        <!-- ADMINISTRACIÓN WEB -->
        <div class="nav-section">
            <div class="nav-section-title">ADMINISTRACIÓN WEB</div>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'web/usuarios.php'; ?>" class="nav-item">
                <i class="fas fa-users-cog"></i> Usuarios Web
            </a>
            <a href="<?php echo (isset($basePath) ? $basePath : '../') . 'web/contenido.php'; ?>" class="nav-item">
                <i class="fas fa-globe"></i> Contenido Web
            </a>
        </div>
        
        <hr style="margin: 25px 20px; border: none; border-top: 1px solid var(--admin-border);">
        <a href="<?php echo (isset($basePath) ? $basePath : '../') . '../../index.php'; ?>" class="nav-item">
            <i class="fas fa-home"></i> Volver al Sitio
        </a>
    </nav>
</div>
