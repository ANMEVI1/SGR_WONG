<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Obtener datos para reportes
$today = date('Y-m-d');
$thisMonth = date('Y-m');

// Ventas del mes
$ventasMes = $db->fetchOne(
    "SELECT COUNT(*) as total_pedidos, COALESCE(SUM(cp.Total), 0) as total_ventas
     FROM Comprobante_Pago cp 
     WHERE DATE_FORMAT(cp.Fecha_Emision, '%Y-%m') = :month",
    [':month' => $thisMonth]
);

// Top platos del mes
$topPlatosMes = $db->fetchAll(
    "SELECT pl.Nombre, SUM(dp.Cantidad) as total_vendido, SUM(dp.Subtotal) as ingresos
     FROM Detalle_Pedido dp
     JOIN Plato_Variante pv ON dp.VarianteID = pv.VarianteID
     JOIN Plato pl ON pv.PlatoID = pl.PlatoID
     JOIN Pedido p ON dp.PedidoID = p.PedidoID
     WHERE DATE_FORMAT(p.Fecha_Hora, '%Y-%m') = :month AND p.Estado != 'Cancelado'
     GROUP BY pl.PlatoID, pl.Nombre
     ORDER BY total_vendido DESC
     LIMIT 10",
    [':month' => $thisMonth]
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes — Admin Chifa Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
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
                <a href="../dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <div class="nav-section">
                    <div class="nav-section-title">PUNTO DE VENTA</div>
                    <a href="../pos/caja.php" class="nav-item">
                        <i class="fas fa-cash-register"></i> Caja
                    </a>
                    <a href="../pedidos/index.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">GESTIÓN DEL NEGOCIO</div>
                    <a href="../menu/index.php" class="nav-item">
                        <i class="fas fa-utensils"></i> Menú
                    </a>
                    <a href="../usuarios/index.php" class="nav-item">
                        <i class="fas fa-user-tie"></i> Empleados
                    </a>
                    <a href="index.php" class="nav-item active">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                </div>
                
                <hr style="margin: 25px 20px; border: none; border-top: 1px solid var(--admin-border);">
                <a href="../../../index.php" class="nav-item">
                    <i class="fas fa-home"></i> Volver al Sitio
                </a>
            </nav>
        </div>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-chart-line"></i> Reportes y Analytics</h1>
                <p>Análisis de ventas y rendimiento del restaurante</p>
            </div>

            <!-- Filtros de Fecha -->
            <div style="display: flex; gap: 15px; margin-bottom: 30px; align-items: center;">
                <select id="periodoReporte" style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-weight: 500;">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Esta Semana</option>
                    <option value="mes" selected>Este Mes</option>
                    <option value="trimestre">Este Trimestre</option>
                </select>
                <button class="btn btn-primary" onclick="generarReporte()">
                    <i class="fas fa-chart-bar"></i> Generar Reporte
                </button>
                <button class="btn btn-outline" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </div>

            <!-- Métricas del Período -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Ventas del Mes</div>
                    <div class="metric-value">S/ <?= number_format($ventasMes['total_ventas'], 2) ?></div>
                    <small><?= $ventasMes['total_pedidos'] ?> pedidos</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Promedio por Pedido</div>
                    <div class="metric-value">S/ <?= $ventasMes['total_pedidos'] > 0 ? number_format($ventasMes['total_ventas'] / $ventasMes['total_pedidos'], 2) : '0.00' ?></div>
                    <small>Ticket promedio</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Platos Vendidos</div>
                    <div class="metric-value"><?= array_sum(array_column($topPlatosMes, 'total_vendido')) ?></div>
                    <small>Unidades totales</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Plato Top</div>
                    <div class="metric-value" style="font-size: 1.2rem;">
                        <?= $topPlatosMes[0]['Nombre'] ?? 'N/A' ?>
                    </div>
                    <small><?= $topPlatosMes[0]['total_vendido'] ?? 0 ?> vendidos</small>
                </div>
            </div>

            <!-- Top Platos del Mes -->
            <div class="admin-table">
                <div style="padding: 25px 30px; border-bottom: 2px solid var(--admin-border); background: var(--admin-white);">
                    <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-trophy" style="color: #f39c12;"></i> 
                        Top 10 Platos Más Vendidos - <?= date('F Y') ?>
                    </h3>
                </div>
                <div style="background: var(--admin-white);">
                    <?php if (empty($topPlatosMes)): ?>
                        <div style="text-align: center; padding: 60px; color: var(--admin-text-light);">
                            <i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                            <h3>No hay datos de ventas</h3>
                            <p>Los reportes aparecerán cuando haya pedidos registrados</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Posición</th>
                                    <th>Plato</th>
                                    <th>Cantidad Vendida</th>
                                    <th>Ingresos Generados</th>
                                    <th>Participación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalVendido = array_sum(array_column($topPlatosMes, 'total_vendido'));
                                foreach ($topPlatosMes as $index => $plato): 
                                    $participacion = $totalVendido > 0 ? ($plato['total_vendido'] / $totalVendido) * 100 : 0;
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $index < 3 ? 'badge-warning' : 'badge-info' ?>">
                                            #<?= $index + 1 ?>
                                        </span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($plato['Nombre']) ?></strong></td>
                                    <td><?= $plato['total_vendido'] ?> unidades</td>
                                    <td><strong>S/ <?= number_format($plato['ingresos'], 2) ?></strong></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="background: var(--admin-bg); height: 8px; width: 100px; border-radius: 4px; overflow: hidden;">
                                                <div style="background: var(--admin-primary); height: 100%; width: <?= $participacion ?>%; transition: width 0.3s ease;"></div>
                                            </div>
                                            <span><?= number_format($participacion, 1) ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function generarReporte() {
            const periodo = document.getElementById('periodoReporte').value;
            showNotification(`Generando reporte para: ${periodo}`, 'info');
            
            // Aquí iría la lógica para generar el reporte
            setTimeout(() => {
                showNotification('Reporte generado exitosamente', 'success');
            }, 2000);
        }

        function exportarExcel() {
            showNotification('Exportando a Excel...', 'info');
            
            // Aquí iría la lógica para exportar
            setTimeout(() => {
                showNotification('Archivo Excel descargado', 'success');
            }, 1500);
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: var(--admin-radius);
                color: white;
                font-weight: 600;
                z-index: 10001;
                max-width: 400px;
                box-shadow: var(--admin-shadow-lg);
                animation: slideInRight 0.3s ease;
            `;
            
            const colors = {
                success: '#27ae60',
                error: '#e74c3c',
                warning: '#f39c12',
                info: '#3498db'
            };
            
            notification.style.background = colors[type] || colors.info;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

        // Agregar estilos de animación
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(400px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(400px); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    </script>
</body>
</html>