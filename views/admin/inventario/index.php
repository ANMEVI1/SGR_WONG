<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario — Admin Chifa Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div style="padding: 25px; border-bottom: 2px solid var(--admin-border);">
                <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-utensils" style="color: var(--admin-primary); font-size: 1.5rem;"></i>
                    Chifa Matsue
                </h3>
                <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Panel de Administración</small>
            </div>
            <nav class="admin-nav" style="padding: 25px 0;">
                <a href="../dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <!-- PUNTO DE VENTA -->
                <div class="nav-section">
                    <div class="nav-section-title">PUNTO DE VENTA</div>
                    <a href="../pos/caja.php" class="nav-item">
                        <i class="fas fa-cash-register"></i> Caja
                    </a>
                    <a href="../pedidos/index.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                </div>
                
                <!-- GESTIÓN DEL NEGOCIO -->
                <div class="nav-section">
                    <div class="nav-section-title">GESTIÓN DEL NEGOCIO</div>
                    <a href="../menu/index.php" class="nav-item">
                        <i class="fas fa-utensils"></i> Menú
                    </a>
                    <a href="../usuarios/index.php" class="nav-item">
                        <i class="fas fa-user-tie"></i> Empleados
                    </a>
                    <a href="../reportes/index.php" class="nav-item">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                </div>
                
                <!-- INVENTARIO -->
                <div class="nav-section">
                    <div class="nav-section-title">INVENTARIO</div>
                    <a href="index.php" class="nav-item active">
                        <i class="fas fa-boxes"></i> Stock
                    </a>
                </div>
                
                <hr style="margin: 25px 20px; border: none; border-top: 1px solid var(--admin-border);">
                <a href="../../../index.php" class="nav-item">
                    <i class="fas fa-home"></i> Volver al Sitio
                </a>
            </nav>
        </div>
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-boxes"></i> Inventario</h1>
                <p>Próximamente podrás registrar y controlar el stock de productos.</p>
            </div>
            <div class="admin-table" style="text-align:center; padding: 60px;">
                <i class="fas fa-warehouse" style="font-size: 4rem; color: var(--admin-primary);"></i>
                <h2 style="margin-top: 20px; color: var(--admin-text);">Funcionalidad de inventario en desarrollo</h2>
                <p style="color: var(--admin-text-light); max-width: 600px; margin: 16px auto;">Esta sección ya está reservada en la navegación del panel. Puedes extenderla agregando control de stock, entradas, salidas y reportes de materiales.</p>
            </div>
        </main>
    </div>
</body>
</html>
