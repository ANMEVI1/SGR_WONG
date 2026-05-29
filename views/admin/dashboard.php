<?php
require_once '../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Obtener métricas del día
$today = date('Y-m-d');

// Ventas del día
$ventasHoy = $db->fetchOne(
    "SELECT COUNT(*) as total_pedidos, COALESCE(SUM(cp.Total), 0) as total_ventas
     FROM Comprobante_Pago cp 
     WHERE DATE(cp.Fecha_Emision) = :today",
    [':today' => $today]
);

// Pedidos activos
$pedidosActivos = $db->fetchAll(
    "SELECT p.PedidoID, p.Origen, p.Estado, p.Tipo_Pedido, p.Fecha_Hora,
            COALESCE(m.Codigo_Mesa, 'DELIVERY') as mesa,
            COALESCE(c.Nombre_Apellidos, 'Anónimo') as cliente
     FROM Pedido p
     LEFT JOIN Mesa m ON p.MesaID = m.MesaID
     LEFT JOIN Cliente c ON p.ClienteID = c.ClienteID
     WHERE p.Estado NOT IN ('Entregado', 'Cancelado')
     ORDER BY p.Fecha_Hora ASC"
);

// Stock crítico
$stockCritico = $db->fetchAll(
    "SELECT i.Nombre, i.Stock_Actual, i.Stock_Minimo, i.Unidad_Medida
     FROM Insumo i 
     WHERE i.Stock_Actual <= i.Stock_Minimo AND i.Estado = 'Activo'
     ORDER BY (i.Stock_Minimo - i.Stock_Actual) DESC
     LIMIT 5"
);

// Platos más vendidos del día
$topPlatos = $db->fetchAll(
    "SELECT pl.Nombre, SUM(dp.Cantidad) as total_vendido
     FROM Detalle_Pedido dp
     JOIN Plato_Variante pv ON dp.VarianteID = pv.VarianteID
     JOIN Plato pl ON pv.PlatoID = pl.PlatoID
     JOIN Pedido p ON dp.PedidoID = p.PedidoID
     WHERE DATE(p.Fecha_Hora) = :today AND p.Estado != 'Cancelado'
     GROUP BY pl.PlatoID, pl.Nombre
     ORDER BY total_vendido DESC
     LIMIT 5",
    [':today' => $today]
);

// Determinar si es admin (todos los usuarios backoffice tienen acceso completo por ahora)
$isAdmin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Admin Chifa Matsue</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div style="padding: 25px; border-bottom: 2px solid var(--admin-border);">
                <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-utensils" style="color: var(--admin-primary); font-size: 1.5rem;"></i>
                    Chifa Matsue
                </h3>
                <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Panel de Administración</small>
            </div>
            <nav class="admin-nav" style="padding: 25px 0;">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <!-- PUNTO DE VENTA -->
                <div class="nav-section">
                    <div class="nav-section-title">PUNTO DE VENTA</div>
                    <a href="pos/caja.php" class="nav-item">
                        <i class="fas fa-cash-register"></i> Caja
                    </a>
                    <a href="pedidos/index.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                    <a href="pos/clientes.php" class="nav-item">
                        <i class="fas fa-address-book"></i> Clientes
                    </a>
                    <a href="pos/mesas.php" class="nav-item">
                        <i class="fas fa-table"></i> Mesas
                    </a>
                </div>
                
                <!-- GESTIÓN DEL NEGOCIO -->
                <div class="nav-section">
                    <div class="nav-section-title">GESTIÓN DEL NEGOCIO</div>
                    <a href="menu/index.php" class="nav-item">
                        <i class="fas fa-utensils"></i> Menú
                    </a>
                    <a href="personal/empleados.php" class="nav-item">
                        <i class="fas fa-id-badge"></i> Personal
                    </a>
                    <a href="negocio/reportes.php" class="nav-item">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                    <a href="negocio/configuracion.php" class="nav-item">
                        <i class="fas fa-cogs"></i> Configuración
                    </a>
                </div>
                
                <!-- INVENTARIO -->
                <div class="nav-section">
                    <div class="nav-section-title">INVENTARIO</div>
                    <a href="inventario/index.php" class="nav-item">
                        <i class="fas fa-boxes"></i> Stock
                    </a>
                    <a href="inventario/movimientos.php" class="nav-item">
                        <i class="fas fa-exchange-alt"></i> Movimientos
                    </a>
                    <a href="inventario/proveedores.php" class="nav-item">
                        <i class="fas fa-truck"></i> Proveedores
                    </a>
                </div>
                
                <!-- ADMINISTRACIÓN WEB -->
                <div class="nav-section">
                    <div class="nav-section-title">ADMINISTRACIÓN WEB</div>
                    <a href="web/usuarios.php" class="nav-item">
                        <i class="fas fa-user-cog"></i> Usuarios Sistema
                    </a>
                    <a href="web/contenido.php" class="nav-item">
                        <i class="fas fa-globe"></i> Contenido Web
                    </a>
                    <a href="web/pedidos-online.php" class="nav-item">
                        <i class="fas fa-laptop"></i> Pedidos Online
                    </a>
                </div>
                
                <hr style="margin: 25px 20px; border: none; border-top: 1px solid var(--admin-border);">
                <a href="../../index.php" class="nav-item">
                    <i class="fas fa-home"></i> Volver al Sitio
                </a>
            </nav>
        </div>
        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Bienvenido, <?= htmlspecialchars($currentUser['login']) ?> - <span class="badge badge-info">Administrador</span></p>
            </div>
            
            <!-- Acciones Rápidas -->
            <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
                <a href="pedidos/index.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Gestionar Pedidos
                </a>
                <a href="menu/index.php" class="btn btn-primary">
                    <i class="fas fa-utensils"></i> Gestionar Menú
                </a>
                <a href="usuarios/index.php" class="btn btn-warning">
                    <i class="fas fa-users"></i> Gestionar Usuarios
                </a>
                <button class="btn btn-outline" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i> Actualizar Datos
                </button>
            </div>
            
            <!-- Métricas del Día -->
            <div class="dashboard-grid">
                <div class="metric-card success">
                    <div class="metric-label">Ventas del Día</div>
                    <div class="metric-value">S/ <?= number_format($ventasHoy['total_ventas'], 2) ?></div>
                    <small><?= $ventasHoy['total_pedidos'] ?> pedidos</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Pedidos Activos</div>
                    <div class="metric-value"><?= count($pedidosActivos) ?></div>
                    <small>En proceso</small>
                </div>
                
                <div class="metric-card <?= count($stockCritico) > 0 ? 'warning' : 'success' ?>">
                    <div class="metric-label">Stock Crítico</div>
                    <div class="metric-value"><?= count($stockCritico) ?></div>
                    <small>Productos por agotar</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Plato Más Vendido</div>
                    <div class="metric-value" style="font-size: 1.2rem;">
                        <?= $topPlatos[0]['Nombre'] ?? 'N/A' ?>
                    </div>
                    <small><?= $topPlatos[0]['total_vendido'] ?? 0 ?> unidades</small>
                </div>
            </div>
            
            <!-- Pedidos Activos -->
            <div class="admin-table" style="margin-top: 30px;">
                <div style="padding: 25px 30px; border-bottom: 2px solid var(--admin-border); background: var(--admin-white);">
                    <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-clock" style="color: var(--admin-primary);"></i> 
                        Pedidos Activos
                    </h3>
                </div>
                <div style="background: var(--admin-white); padding: 20px 30px;">
                    <?php if (empty($pedidosActivos)): ?>
                        <div style="text-align: center; color: var(--admin-text-light); padding: 40px;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 15px; color: #27ae60;"></i><br>
                            <h4 style="margin: 10px 0; color: var(--admin-text);">¡Todo al día!</h4>
                            <p>No hay pedidos activos en este momento</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($pedidosActivos as $pedido): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid rgba(225, 218, 218, 0.3);">
                                <div style="flex: 1;">
                                    <strong style="color: var(--admin-text); font-size: 1.1rem;">#<?= $pedido['PedidoID'] ?></strong> 
                                    <span style="color: var(--admin-text-light);">— <?= $pedido['cliente'] ?></span>
                                    <br>
                                    <small style="color: var(--admin-text-light); margin-top: 5px; display: block;">
                                        <i class="fas fa-<?= $pedido['Origen'] === 'Web' ? 'globe' : 'store' ?>"></i>
                                        <?= $pedido['Origen'] ?> | <?= $pedido['mesa'] ?> | 
                                        <?= date('H:i', strtotime($pedido['Fecha_Hora'])) ?>
                                    </small>
                                </div>
                                <span class="badge <?= $pedido['Estado'] === 'Pendiente' ? 'badge-warning' : ($pedido['Estado'] === 'En Cocina' ? 'badge-info' : 'badge-success') ?>">
                                    <?= $pedido['Estado'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Alertas de Stock -->
            <?php if (!empty($stockCritico)): ?>
            <div class="admin-table" style="margin-top: 25px;">
                <div style="padding: 25px 30px; border-bottom: 2px solid var(--admin-border); background: var(--admin-white); border-left: 5px solid #f39c12;">
                    <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-triangle" style="color: #f39c12;"></i> 
                        Alertas de Stock Crítico
                    </h3>
                </div>
                <div style="background: var(--admin-white); padding: 20px 30px;">
                    <?php foreach ($stockCritico as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px solid rgba(225, 218, 218, 0.3);">
                            <div style="flex: 1;">
                                <strong style="color: var(--admin-text); font-size: 1.1rem;"><?= htmlspecialchars($item['Nombre']) ?></strong>
                                <br>
                                <small style="color: var(--admin-text-light); margin-top: 5px; display: block;">
                                    Stock actual: <strong><?= $item['Stock_Actual'] ?> <?= $item['Unidad_Medida'] ?></strong> |
                                    Mínimo: <strong><?= $item['Stock_Minimo'] ?> <?= $item['Unidad_Medida'] ?></strong>
                                </small>
                            </div>
                            <span class="badge badge-warning">
                                Crítico
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        // Función para actualizar dashboard
        function refreshDashboard() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            btn.disabled = true;
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
        
        // Auto-refresh cada 2 minutos para datos en tiempo real
        let autoRefreshInterval = setInterval(() => {
            // Solo actualizar si la página está visible
            if (!document.hidden) {
                window.location.reload();
            }
        }, 120000);
        
        // Pausar auto-refresh cuando la página no está visible
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(autoRefreshInterval);
            } else {
                autoRefreshInterval = setInterval(() => {
                    if (!document.hidden) {
                        window.location.reload();
                    }
                }, 120000);
            }
        });
        
        // Mostrar hora actual
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-PE', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('es-PE', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            // Agregar reloj si no existe
            if (!document.getElementById('live-clock')) {
                const clockDiv = document.createElement('div');
                clockDiv.id = 'live-clock';
                clockDiv.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: var(--admin-white);
                    padding: 15px 20px;
                    border-radius: var(--admin-radius);
                    box-shadow: var(--admin-shadow);
                    font-weight: 600;
                    color: var(--admin-text);
                    z-index: 1000;
                    border-left: 4px solid var(--admin-primary);
                `;
                document.body.appendChild(clockDiv);
            }
            
            document.getElementById('live-clock').innerHTML = `
                <div style="font-size: 1.1rem;">${timeString}</div>
                <div style="font-size: 0.8rem; color: var(--admin-text-light); margin-top: 2px;">${dateString}</div>
            `;
        }
        
        // Actualizar reloj cada segundo
        updateClock();
        setInterval(updateClock, 1000);
        
        console.log('Dashboard cargado - Pedidos activos:', <?= count($pedidosActivos) ?>);
        console.log('Stock crítico:', <?= count($stockCritico) ?>);
    </script>
</body>
</html>