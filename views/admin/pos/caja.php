<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Verificar permisos (administrador o cajero)
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

// Obtener empleado actual
$empleado = $db->fetchOne(
    "SELECT EmpleadoID, Nombre_Apellidos FROM Empleado WHERE UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

// Obtener caja actual del empleado
$cajaActual = null;
if ($empleado) {
    $cajaActual = $db->fetchOne(
        "SELECT * FROM Caja WHERE EmpleadoID = :empId AND Estado = 'Abierta' ORDER BY CajaID DESC LIMIT 1",
        [':empId' => $empleado['EmpleadoID']]
    );
}

// Obtener ventas del día si hay caja abierta
$ventasHoy = [];
$ventasPorMetodo = [];
if ($cajaActual) {
    // Ventas detalladas
    $ventasHoy = $db->fetchAll(
        "SELECT cp.*, mp.Nombre as MetodoPago, p.PedidoID, p.Tipo_Pedido,
                c.Nombre_Apellidos as Cliente
         FROM Comprobante_Pago cp
         JOIN Metodo_Pago mp ON cp.MetPagID = mp.MetPagID
         JOIN Pedido p ON cp.PedidoID = p.PedidoID
         LEFT JOIN Cliente c ON cp.ClienteID = c.ClienteID
         WHERE DATE(cp.Fecha_Emision) = CURDATE()
         AND p.EmpleadoID = :empId
         ORDER BY cp.Fecha_Emision DESC",
        [':empId' => $empleado['EmpleadoID']]
    );
    
    // Ventas agrupadas por método de pago
    $ventasPorMetodo = $db->fetchAll(
        "SELECT mp.Nombre as Metodo, mp.Icono,
                COUNT(cp.ComPagID) as Cantidad,
                SUM(cp.Total) as Total
         FROM Comprobante_Pago cp
         JOIN Metodo_Pago mp ON cp.MetPagID = mp.MetPagID
         JOIN Pedido p ON cp.PedidoID = p.PedidoID
         WHERE DATE(cp.Fecha_Emision) = CURDATE()
         AND p.EmpleadoID = :empId
         GROUP BY mp.MetPagID
         ORDER BY Total DESC",
        [':empId' => $empleado['EmpleadoID']]
    );
}

// Obtener métodos de pago activos para el arqueo
$metodosPago = $db->fetchAll(
    "SELECT MetPagID, Nombre, Icono FROM Metodo_Pago WHERE Estado = 1 ORDER BY Nombre"
);
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
        <?php include '../components/sidebar.php'; ?>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-cash-register"></i> Gestión de Caja</h1>
                <p>Control de apertura, cierre y movimientos de efectivo</p>
            </div>

            <?php if (!$cajaActual): ?>
            <!-- ============ CAJA CERRADA ============ -->
            <div style="max-width: 600px; margin: 60px auto;">
                <div style="background: white; padding: 60px 40px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center;">
                    <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 30px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-cash-register" style="font-size: 3.5rem; color: white;"></i>
                    </div>
                    <h2 style="color: #2c3e50; margin-bottom: 15px;">Caja Cerrada</h2>
                    <p style="color: #6c757d; margin-bottom: 10px;">
                        <strong><?= htmlspecialchars($empleado['Nombre_Apellidos']) ?></strong>
                    </p>
                    <p style="color: #6c757d; margin-bottom: 30px;">Debes abrir la caja para comenzar a operar</p>
                    
                    <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: left;">
                        <p style="margin: 0; color: #1976d2; font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Importante:</strong> Verifica el monto de apertura antes de iniciar operaciones.
                        </p>
                    </div>
                    
                    <button onclick="abrirModalApertura()" class="btn btn-success" style="font-size: 1.1rem; padding: 15px 40px;">
                        <i class="fas fa-unlock"></i> Abrir Caja del Día
                    </button>
                </div>
            </div>
            
            <?php else: 
                $totalVentas = array_sum(array_column($ventasHoy, 'Total'));
                $totalEsperado = $cajaActual['Monto_Apertura'] + $totalVentas;
                $horasAbierta = (strtotime('now') - strtotime($cajaActual['Fecha_hora_Apertura'])) / 3600;
            ?>
            <!-- ============ CAJA ABIERTA ============ -->
            
            <!-- Información del cajero y hora de apertura -->
            <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 25px; border-radius: 8px; margin-bottom: 30px; color: white; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0 0 8px 0; color: white;">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($empleado['Nombre_Apellidos']) ?>
                    </h3>
                    <p style="margin: 0; opacity: 0.9;">
                        <i class="fas fa-clock"></i> Caja abierta desde: 
                        <strong><?= date('H:i A', strtotime($cajaActual['Fecha_hora_Apertura'])) ?></strong>
                        (<?= number_format($horasAbierta, 1) ?> horas)
                    </p>
                </div>
                <div style="text-align: right;">
                    <button onclick="abrirModalCierre()" class="btn btn-danger" style="font-size: 1rem; padding: 12px 30px;">
                        <i class="fas fa-lock"></i> Cerrar Caja
                    </button>
                </div>
            </div>

            <!-- Estadísticas principales -->
            <div class="dashboard-grid" style="margin-bottom: 30px; grid-template-columns: repeat(4, 1fr);">
                <div class="metric-card success">
                    <div class="metric-label">Apertura</div>
                    <div class="metric-value">S/ <?= number_format($cajaActual['Monto_Apertura'], 2) ?></div>
                    <small>Monto inicial</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Ventas del Día</div>
                    <div class="metric-value">S/ <?= number_format($totalVentas, 2) ?></div>
                    <small><?= count($ventasHoy) ?> comprobantes</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Total Esperado</div>
                    <div class="metric-value">S/ <?= number_format($totalEsperado, 2) ?></div>
                    <small>En caja</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Promedio/Venta</div>
                    <div class="metric-value">S/ <?= count($ventasHoy) > 0 ? number_format($totalVentas / count($ventasHoy), 2) : '0.00' ?></div>
                    <small>Por comprobante</small>
                </div>
            </div>

            <!-- Ventas por método de pago -->
            <div style="background: white; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 20px 0; color: #2c3e50;">
                    <i class="fas fa-chart-pie"></i> Ventas por Método de Pago
                </h3>
                
                <?php if (empty($ventasPorMetodo)): ?>
                    <div style="text-align: center; padding: 30px; color: #6c757d;">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p>No hay ventas registradas aún</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <?php foreach ($ventasPorMetodo as $metodo): ?>
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; color: white;">
                            <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 5px;">
                                <?= htmlspecialchars($metodo['Metodo']) ?>
                            </div>
                            <div style="font-size: 1.8rem; font-weight: bold; margin-bottom: 5px;">
                                S/ <?= number_format($metodo['Total'], 2) ?>
                            </div>
                            <div style="font-size: 0.8rem; opacity: 0.8;">
                                <?= $metodo['Cantidad'] ?> operación<?= $metodo['Cantidad'] != 1 ? 'es' : '' ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Detalle de ventas -->
            <div class="admin-table">
                <div style="padding: 25px 30px; border-bottom: 2px solid var(--admin-border); background: var(--admin-white); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: var(--admin-text);">
                        <i class="fas fa-receipt"></i> Detalle de Ventas
                    </h3>
                    <button onclick="imprimirReporte()" class="btn btn-outline">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
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
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Método</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventasHoy as $venta): ?>
                                <tr>
                                    <td><?= date('H:i', strtotime($venta['Fecha_Emision'])) ?></td>
                                    <td>
                                        <strong><?= $venta['Tipo_Comprobante'] ?></strong><br>
                                        <small><?= $venta['Serie'] ?>-<?= $venta['Numero'] ?></small>
                                    </td>
                                    <td>#<?= $venta['PedidoID'] ?></td>
                                    <td><?= $venta['Cliente'] ?: 'Público general' ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= $venta['Tipo_Pedido'] ?></span>
                                    </td>
                                    <td><?= $venta['MetodoPago'] ?></td>
                                    <td><strong>S/ <?= number_format($venta['Total'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8f9fa; font-weight: bold;">
                                    <td colspan="6" style="text-align: right; padding-right: 20px;">TOTAL DEL DÍA:</td>
                                    <td><strong>S/ <?= number_format($totalVentas, 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Apertura de Caja -->
    <div id="modalApertura" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close" onclick="cerrarModalApertura()">&times;</span>
            <h2 style="color: #28a745;">
                <i class="fas fa-unlock"></i> Abrir Caja del Día
            </h2>
            <form id="formApertura">
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <strong><i class="fas fa-exclamation-triangle"></i> Importante:</strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem;">
                        Verifica el monto antes de abrir. Este será el dinero inicial para dar vuelto.
                    </p>
                </div>
                
                <div class="form-group">
                    <label for="montoApertura">Monto de Apertura (S/) *</label>
                    <input type="number" id="montoApertura" name="montoApertura" class="form-control" 
                           step="0.01" min="0" required value="200.00" 
                           style="font-size: 1.3rem; padding: 15px;">
                </div>
                
                <div class="form-group">
                    <label for="observacionesApertura">Observaciones</label>
                    <textarea id="observacionesApertura" name="observacionesApertura" 
                              class="form-control" rows="3" 
                              placeholder="Detalles opcionales sobre la apertura"></textarea>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" onclick="cerrarModalApertura()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Apertura
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cierre de Caja (Arqueo Detallado) -->
    <div id="modalCierre" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 850px;">
            <span class="close" onclick="cerrarModalCierre()">&times;</span>
            <h2 style="color: #dc3545;">
                <i class="fas fa-lock"></i> Cerrar Caja - Arqueo Detallado
            </h2>
            
            <form id="formCierre">
                <!-- Resumen de la jornada -->
                <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                    <h4 style="margin: 0 0 15px 0; color: #1976d2;">
                        <i class="fas fa-calculator"></i> Resumen de Jornada
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div>
                            <div style="font-size: 0.85rem; color: #666;">Apertura:</div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: #28a745;">
                                S/ <?= number_format($cajaActual['Monto_Apertura'] ?? 0, 2) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: #666;">Ventas:</div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: #17a2b8;">
                                S/ <?= number_format($totalVentas ?? 0, 2) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: #666;">Esperado:</div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: #2c3e50;">
                                S/ <span id="totalEsperadoDisplay"><?= number_format($totalEsperado ?? 0, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ARQUEO DETALLADO DE BILLETES Y MONEDAS -->
                <h4 style="margin: 0 0 15px 0; color: #2c3e50;">
                    <i class="fas fa-money-bill-wave"></i> Conteo de Billetes y Monedas
                </h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- BILLETES -->
                    <div>
                        <h5 style="margin: 0 0 12px 0; color: #28a745; font-size: 1rem;">
                            <i class="fas fa-money-bill"></i> Billetes
                        </h5>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                            <!-- S/ 200 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 200</label>
                                <input type="number" class="arqueo-input" data-valor="200" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 100 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 100</label>
                                <input type="number" class="arqueo-input" data-valor="100" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 50 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 50</label>
                                <input type="number" class="arqueo-input" data-valor="50" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 20 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 20</label>
                                <input type="number" class="arqueo-input" data-valor="20" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 10 -->
                            <div style="display: flex; align-items: center;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 10</label>
                                <input type="number" class="arqueo-input" data-valor="10" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- MONEDAS -->
                    <div>
                        <h5 style="margin: 0 0 12px 0; color: #ffc107; font-size: 1rem;">
                            <i class="fas fa-coins"></i> Monedas
                        </h5>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                            <!-- S/ 5 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 5</label>
                                <input type="number" class="arqueo-input" data-valor="5" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 2 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 2</label>
                                <input type="number" class="arqueo-input" data-valor="2" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 1 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 1</label>
                                <input type="number" class="arqueo-input" data-valor="1" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 0.50 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 0.50</label>
                                <input type="number" class="arqueo-input" data-valor="0.50" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 0.20 -->
                            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 0.20</label>
                                <input type="number" class="arqueo-input" data-valor="0.20" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                            <!-- S/ 0.10 -->
                            <div style="display: flex; align-items: center;">
                                <label style="flex: 1; margin: 0; font-weight: 500;">S/ 0.10</label>
                                <input type="number" class="arqueo-input" data-valor="0.10" min="0" value="0" 
                                       style="width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center;"
                                       onkeyup="calcularArqueo()">
                                <span class="arqueo-subtotal" style="width: 100px; text-align: right; margin-left: 10px; font-weight: bold;">S/ 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOTAL CONTADO -->
                <div style="background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 5px;">Total Contado:</div>
                            <div style="font-size: 2rem; font-weight: bold;" id="totalContadoDisplay">S/ 0.00</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 5px;">Diferencia:</div>
                            <div style="font-size: 1.5rem; font-weight: bold;" id="diferenciaDisplay">S/ 0.00</div>
                            <div style="font-size: 0.85rem; margin-top: 5px;" id="mensajeDiferencia"></div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="montoCierre" name="montoCierre" value="0">
                <input type="hidden" id="detalleArqueo" name="detalleArqueo" value="">
                
                <div class="form-group">
                    <label for="observacionesCierre">Observaciones del Cierre</label>
                    <textarea id="observacionesCierre" name="observacionesCierre" 
                              class="form-control" rows="3" 
                              placeholder="Novedades, faltantes, sobrantes, etc."></textarea>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" onclick="cerrarModalCierre()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-lock"></i> Confirmar Cierre
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const totalEsperado = <?= $totalEsperado ?? 0 ?>;

    function abrirModalApertura() {
        document.getElementById('modalApertura').style.display = 'block';
    }

    function cerrarModalApertura() {
        document.getElementById('modalApertura').style.display = 'none';
    }

    function abrirModalCierre() {
        document.getElementById('modalCierre').style.display = 'block';
    }

    function cerrarModalCierre() {
        document.getElementById('modalCierre').style.display = 'none';
    }

    function calcularArqueo() {
        let totalContado = 0;
        const detalleArqueo = [];
        
        // Recorrer todos los inputs de arqueo
        document.querySelectorAll('.arqueo-input').forEach(input => {
            const cantidad = parseInt(input.value) || 0;
            const valor = parseFloat(input.dataset.valor);
            const subtotal = cantidad * valor;
            
            // Actualizar subtotal en la interfaz
            const subtotalSpan = input.parentElement.querySelector('.arqueo-subtotal');
            subtotalSpan.textContent = `S/ ${subtotal.toFixed(2)}`;
            
            // Sumar al total
            totalContado += subtotal;
            
            // Guardar detalle si hay cantidad
            if (cantidad > 0) {
                detalleArqueo.push({
                    denominacion: valor,
                    cantidad: cantidad,
                    subtotal: subtotal
                });
            }
        });
        
        // Actualizar el total contado
        document.getElementById('totalContadoDisplay').textContent = `S/ ${totalContado.toFixed(2)}`;
        document.getElementById('montoCierre').value = totalContado.toFixed(2);
        document.getElementById('detalleArqueo').value = JSON.stringify(detalleArqueo);
        
        // Calcular diferencia
        const diferencia = totalContado - totalEsperado;
        const diferenciaDisplay = document.getElementById('diferenciaDisplay');
        const mensajeDiv = document.getElementById('mensajeDiferencia');
        
        diferenciaDisplay.textContent = `S/ ${Math.abs(diferencia).toFixed(2)}`;
        
        if (diferencia > 0) {
            diferenciaDisplay.style.color = '#28a745';
            mensajeDiv.innerHTML = '<i class="fas fa-arrow-up"></i> Sobrante';
            mensajeDiv.style.color = '#28a745';
        } else if (diferencia < 0) {
            diferenciaDisplay.style.color = '#dc3545';
            mensajeDiv.innerHTML = '<i class="fas fa-arrow-down"></i> Faltante';
            mensajeDiv.style.color = '#dc3545';
        } else {
            diferenciaDisplay.style.color = '#17a2b8';
            mensajeDiv.innerHTML = '<i class="fas fa-check"></i> Cuadra exacto';
            mensajeDiv.style.color = '#17a2b8';
        }
    }

    // Envío del formulario de apertura
    document.getElementById('formApertura').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        if (parseFloat(data.montoApertura) < 0) {
            alert('El monto de apertura no puede ser negativo');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Abriendo caja...';
        
        fetch('../../../api/admin/abrir-caja.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Caja abierta correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Apertura';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Apertura';
        });
    });

    // Envío del formulario de cierre
    document.getElementById('formCierre')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        if (parseFloat(data.montoCierre) < 0) {
            alert('El monto de cierre no puede ser negativo');
            return;
        }
        
        if (!confirm('¿Estás seguro de cerrar la caja?\n\nEsta acción no se puede deshacer.')) {
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cerrando caja...';
        
        fetch('../../../api/admin/cerrar-caja.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Caja cerrada correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Confirmar Cierre';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock"></i> Confirmar Cierre';
        });
    });

    function imprimirReporte() {
        window.print();
    }

    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        const modalApertura = document.getElementById('modalApertura');
        const modalCierre = document.getElementById('modalCierre');
        
        if (event.target === modalApertura) {
            modalApertura.style.display = 'none';
        }
        if (event.target === modalCierre) {
            modalCierre.style.display = 'none';
        }
    }
    </script>

    <style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: #fefefe;
        padding: 30px;
        border: none;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    .close {
        color: #aaa;
        position: absolute;
        right: 20px;
        top: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    @media print {
        .admin-sidebar, .admin-header, button, .close {
            display: none !important;
        }
        
        .admin-content {
            margin: 0 !important;
        }
    }
    </style>
</body>
</html>
