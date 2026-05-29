<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Verificar permisos de cajero
$userRole = $db->fetchOne(
    "SELECT tu.TipUsuID FROM Usuario u
     JOIN Tipo_Usuario tu ON u.TipUsuID = tu.TipUsuID
     WHERE u.UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

if (!in_array($userRole['TipUsuID'], [1, 2])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Obtener caja actual del empleado
$empleado = $db->fetchOne(
    "SELECT EmpleadoID FROM Empleado WHERE UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

$cajaActual = null;
if ($empleado) {
    $cajaActual = $db->fetchOne(
        "SELECT * FROM Caja WHERE EmpleadoID = :empId AND Estado = 'Abierta'",
        [':empId' => $empleado['EmpleadoID']]
    );
}

// Obtener ventas del día si hay caja abierta
$ventasHoy = [];
if ($cajaActual) {
    $ventasHoy = $db->fetchAll(
        "SELECT cp.*, mp.Nombre as MetodoPago, p.PedidoID
         FROM Comprobante_Pago cp
         JOIN Metodo_Pago mp ON cp.MetPagID = mp.MetPagID
         JOIN Pedido p ON cp.PedidoID = p.PedidoID
         WHERE DATE(cp.Fecha_Emision) = CURDATE()
         AND p.EmpleadoID = :empId
         ORDER BY cp.Fecha_Emision DESC",
        [':empId' => $empleado['EmpleadoID']]
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Caja — Chifa Matsue</title>
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
                <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Punto de Venta</small>
            </div>
            <nav class="admin-nav" style="padding: 25px 0;">
                <a href="../dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <div class="nav-section">
                    <div class="nav-section-title">PUNTO DE VENTA</div>
                    <a href="caja.php" class="nav-item active">
                        <i class="fas fa-cash-register"></i> Caja
                    </a>
                    <a href="pedidos.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                    <a href="clientes.php" class="nav-item">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                    <a href="mesas.php" class="nav-item">
                        <i class="fas fa-table"></i> Mesas
                    </a>
                </div>
            </nav>
        </div>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-cash-register"></i> Gestión de Caja</h1>
                <p>Control de apertura, cierre y movimientos de caja</p>
            </div>

            <?php if (!$cajaActual): ?>
            <!-- Caja Cerrada -->
            <div class="admin-table" style="text-align: center; padding: 60px;">
                <i class="fas fa-cash-register" style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;"></i>
                <h3>Caja Cerrada</h3>
                <p>Debes abrir la caja para comenzar a trabajar</p>
                <button onclick="abrirCaja()" class="btn btn-success" style="margin-top: 20px;">
                    <i class="fas fa-unlock"></i> Abrir Caja
                </button>
            </div>
            <?php else: ?>
            <!-- Caja Abierta -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Caja Abierta</div>
                    <div class="metric-value">S/ <?= number_format($cajaActual['Monto_Apertura'], 2) ?></div>
                    <small>Apertura: <?= date('H:i', strtotime($cajaActual['Fecha_hora_Apertura'])) ?></small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Ventas del Día</div>
                    <div class="metric-value">S/ <?= number_format(array_sum(array_column($ventasHoy, 'Total')), 2) ?></div>
                    <small><?= count($ventasHoy) ?> comprobantes</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Total en Caja</div>
                    <div class="metric-value">S/ <?= number_format($cajaActual['Monto_Apertura'] + array_sum(array_column($ventasHoy, 'Total')), 2) ?></div>
                    <small>Estimado</small>
                </div>
                
                <div class="metric-card">
                    <button onclick="cerrarCaja()" class="btn btn-danger" style="width: 100%;">
                        <i class="fas fa-lock"></i> Cerrar Caja
                    </button>
                </div>
            </div>

            <!-- Ventas del Día -->
            <div class="admin-table">
                <div style="padding: 25px 30px; border-bottom: 2px solid var(--admin-border); background: var(--admin-white);">
                    <h3 style="margin: 0; color: var(--admin-text);">
                        <i class="fas fa-receipt"></i> Ventas del Día
                    </h3>
                </div>
                <div style="background: var(--admin-white);">
                    <?php if (empty($ventasHoy)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--admin-text-light);">
                            <i class="fas fa-receipt" style="font-size: 2rem; margin-bottom: 15px;"></i>
                            <p>No hay ventas registradas hoy</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Comprobante</th>
                                    <th>Pedido</th>
                                    <th>Método</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventasHoy as $venta): ?>
                                <tr>
                                    <td><?= date('H:i', strtotime($venta['Fecha_Emision'])) ?></td>
                                    <td><?= $venta['Serie'] ?>-<?= $venta['Numero'] ?></td>
                                    <td>#<?= $venta['PedidoID'] ?></td>
                                    <td><?= $venta['MetodoPago'] ?></td>
                                    <td>S/ <?= number_format($venta['Total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Abrir Caja -->
    <div id="modalAbrirCaja" class="modal-overlay" style="display: none;">
        <div class="modal">
            <div style="padding: 30px;">
                <h3><i class="fas fa-unlock"></i> Abrir Caja</h3>
                <form id="formAbrirCaja">
                    <div class="form-group">
                        <label>Monto de Apertura (S/)</label>
                        <input type="number" step="0.01" name="monto" required class="form-control" value="200.00">
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" onclick="cerrarModal()" class="btn btn-outline">Cancelar</button>
                        <button type="submit" class="btn btn-success">Abrir Caja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirCaja() {
            document.getElementById('modalAbrirCaja').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalAbrirCaja').style.display = 'none';
        }

        document.getElementById('formAbrirCaja').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'abrir_caja');

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error de conexión');
            }
        });

        function cerrarCaja() {
            if (confirm('¿Estás seguro de cerrar la caja?')) {
                // Implementar cierre de caja
                alert('Función de cierre en desarrollo');
            }
        }
    </script>
</body>
</html>