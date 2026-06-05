<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Obtener pedidos con información completa
$pedidos = $db->fetchAll(
    "SELECT p.PedidoID, p.Origen, p.Estado, p.Tipo_Pedido, p.Fecha_Hora,
            p.Observaciones,
            COALESCE(m.Codigo_Mesa, 'DELIVERY') as Mesa,
            COALESCE(c.Nombre_Apellidos, 'Cliente Anónimo') as Cliente,
            COALESCE(c.Telefono, 'Sin teléfono') as Telefono,
            COUNT(dp.DetPedID) as CantidadItems,
            COALESCE(SUM(dp.Subtotal), 0) as Total
     FROM Pedido p
     LEFT JOIN Mesa m ON p.MesaID = m.MesaID
     LEFT JOIN Cliente c ON p.ClienteID = c.ClienteID
     LEFT JOIN Detalle_Pedido dp ON p.PedidoID = dp.PedidoID
     GROUP BY p.PedidoID
     ORDER BY p.Fecha_Hora DESC
     LIMIT 100"
);

// Obtener estados disponibles
$estados = ['Pendiente', 'En Cocina', 'Listo', 'Entregado', 'Cancelado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos — Admin Chifa Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-shopping-cart"></i> Gestión de Pedidos</h1>
                <p>Administra y monitorea todos los pedidos del restaurante</p>
            </div>

            <!-- Filtros y Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <select id="filterEstado" style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-weight: 500;">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?= $estado ?>"><?= $estado ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filterOrigen" style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-weight: 500;">
                        <option value="">Todos los orígenes</option>
                        <option value="Web">Web</option>
                        <option value="Local">Local</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-outline" onclick="refreshTable()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>

            <!-- Métricas Rápidas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card info">
                    <div class="metric-label">Pedidos Hoy</div>
                    <div class="metric-value"><?= count(array_filter($pedidos, fn($p) => date('Y-m-d', strtotime($p['Fecha_Hora'])) === date('Y-m-d'))) ?></div>
                </div>
                <div class="metric-card warning">
                    <div class="metric-label">Pendientes</div>
                    <div class="metric-value"><?= count(array_filter($pedidos, fn($p) => $p['Estado'] === 'Pendiente')) ?></div>
                </div>
                <div class="metric-card success">
                    <div class="metric-label">En Cocina</div>
                    <div class="metric-value"><?= count(array_filter($pedidos, fn($p) => $p['Estado'] === 'En Cocina')) ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Listos</div>
                    <div class="metric-value"><?= count(array_filter($pedidos, fn($p) => $p['Estado'] === 'Listo')) ?></div>
                </div>
            </div>

            <!-- Tabla de Pedidos -->
            <div class="admin-table">
                <?php if (empty($pedidos)): ?>
                    <div style="text-align: center; padding: 60px; color: var(--admin-text-light);">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay pedidos registrados</h3>
                        <p>Los pedidos aparecerán aquí cuando los clientes realicen compras</p>
                    </div>
                <?php else: ?>
                <table id="pedidosTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Origen</th>
                            <th>Mesa</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr data-estado="<?= $pedido['Estado'] ?>" data-origen="<?= $pedido['Origen'] ?>">
                            <td><strong>#<?= $pedido['PedidoID'] ?></strong></td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($pedido['Cliente']) ?></strong>
                                    <br>
                                    <small style="color: var(--admin-text-light);"><?= htmlspecialchars($pedido['Telefono']) ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $pedido['Origen'] === 'Web' ? 'badge-info' : 'badge-success' ?>">
                                    <i class="fas fa-<?= $pedido['Origen'] === 'Web' ? 'globe' : 'store' ?>"></i>
                                    <?= $pedido['Origen'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($pedido['Mesa']) ?></td>
                            <td><?= $pedido['CantidadItems'] ?> items</td>
                            <td><strong>S/ <?= number_format($pedido['Total'], 2) ?></strong></td>
                            <td>
                                <span class="badge <?= 
                                    $pedido['Estado'] === 'Pendiente' ? 'badge-warning' : 
                                    ($pedido['Estado'] === 'En Cocina' ? 'badge-info' : 
                                    ($pedido['Estado'] === 'Listo' ? 'badge-success' : 
                                    ($pedido['Estado'] === 'Entregado' ? 'badge-success' : 'badge-danger'))) ?>">
                                    <?= $pedido['Estado'] ?>
                                </span>
                            </td>
                            <td>
                                <div>
                                    <?= date('d/m/Y', strtotime($pedido['Fecha_Hora'])) ?>
                                    <br>
                                    <small style="color: var(--admin-text-light);"><?= date('H:i', strtotime($pedido['Fecha_Hora'])) ?></small>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm" onclick="verDetalle(<?= $pedido['PedidoID'] ?>)" 
                                        style="padding: 5px 10px; margin-right: 5px; background: #17a2b8; color: white;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Filtros
        document.getElementById('filterEstado').addEventListener('change', filterTable);
        document.getElementById('filterOrigen').addEventListener('change', filterTable);

        function filterTable() {
            const estadoFilter = document.getElementById('filterEstado').value;
            const origenFilter = document.getElementById('filterOrigen').value;
            const rows = document.querySelectorAll('#pedidosTable tbody tr');

            rows.forEach(row => {
                const estado = row.getAttribute('data-estado');
                const origen = row.getAttribute('data-origen');
                
                const matchesEstado = !estadoFilter || estado === estadoFilter;
                const matchesOrigen = !origenFilter || origen === origenFilter;
                
                row.style.display = (matchesEstado && matchesOrigen) ? '' : 'none';
            });
        }

        function verDetalle(pedidoId) {
            alert(`Ver detalle del pedido #${pedidoId}\n\nEsta funcionalidad se implementará próximamente.`);
        }

        function refreshTable() {
            window.location.reload();
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