<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Verificar permisos de administrador
$userRole = $db->fetchOne(
    "SELECT tu.TipUsuID FROM Usuario u
     JOIN Tipo_Usuario tu ON u.TipUsuID = tu.TipUsuID
     WHERE u.UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

if ($userRole['TipUsuID'] != 1) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Obtener estadísticas de clientes
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_clientes,
        COUNT(CASE WHEN UsuarioID IS NOT NULL THEN 1 END) as con_cuenta_web,
        COUNT(CASE WHEN UsuarioID IS NULL THEN 1 END) as sin_cuenta_web,
        COUNT(DISTINCT CASE WHEN p.ClienteID IS NOT NULL THEN c.ClienteID END) as con_pedidos
     FROM Cliente c
     LEFT JOIN Pedido p ON c.ClienteID = p.ClienteID"
);

// Obtener clientes con información completa
$clientes = $db->fetchAll(
    "SELECT c.ClienteID, c.Nombre_Apellidos, c.Tipo_Documento, c.Num_Documento, 
            c.Telefono, c.Direccion, c.Correo, c.Fecha_Creacion,
            u.Login as Usuario_Web, u.Estado as Estado_Usuario,
            COUNT(p.PedidoID) as total_pedidos,
            COALESCE(SUM(cp.Total), 0) as total_gastado,
            MAX(p.Fecha_Hora) as ultimo_pedido
     FROM Cliente c
     LEFT JOIN Usuario u ON c.UsuarioID = u.UsuarioID
     LEFT JOIN Pedido p ON c.ClienteID = p.ClienteID
     LEFT JOIN Comprobante_Pago cp ON p.PedidoID = cp.PedidoID
     GROUP BY c.ClienteID
     ORDER BY c.Fecha_Creacion DESC"
);

// Obtener tipos de documento
$tiposDocumento = [
    'DNI' => 'DNI',
    'RUC' => 'RUC',
    'CE' => 'Carné de Extranjería',
    'PAS' => 'Pasaporte'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes — Chifa Matsue</title>
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
                <h1><i class="fas fa-address-book"></i> Gestión de Clientes</h1>
                <p>Administración completa de la base de clientes del restaurante</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Clientes</div>
                    <div class="metric-value"><?= $stats['total_clientes'] ?></div>
                    <small>Registrados</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Con Cuenta Web</div>
                    <div class="metric-value"><?= $stats['con_cuenta_web'] ?></div>
                    <small>Usuarios online</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Solo Presencial</div>
                    <div class="metric-value"><?= $stats['sin_cuenta_web'] ?></div>
                    <small>Sin cuenta web</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Con Pedidos</div>
                    <div class="metric-value"><?= $stats['con_pedidos'] ?></div>
                    <small>Compradores</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Cliente
                    </button>
                    <button onclick="exportarClientes()" class="btn btn-outline">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroTipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        <option value="con_web">Con cuenta web</option>
                        <option value="sin_web">Solo presencial</option>
                        <option value="con_pedidos">Con pedidos</option>
                    </select>
                    <select id="filtroDocumento" class="form-control">
                        <option value="">Todos los documentos</option>
                        <?php foreach ($tiposDocumento as $codigo => $nombre): ?>
                        <option value="<?= $codigo ?>"><?= $nombre ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="buscarCliente" placeholder="Buscar por nombre, documento o teléfono..." class="form-control" style="width: 350px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="clientes-counter">Cargando...</span>
            </div>

            <!-- Tabla de Clientes -->
            <div class="admin-table">
                <table id="tablaClientes">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Registro</th>
                            <th>Pedidos</th>
                            <th>Total Gastado</th>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr data-tipo="<?= $cliente['Usuario_Web'] ? 'con_web' : 'sin_web' ?>" 
                            data-documento="<?= $cliente['Tipo_Documento'] ?>"
                            data-pedidos="<?= $cliente['total_pedidos'] > 0 ? 'con_pedidos' : 'sin_pedidos' ?>">
                            <td>
                                <strong><?= htmlspecialchars($cliente['Nombre_Apellidos']) ?></strong>
                                <br>
                                <small style="color: #6c757d;">
                                    <?= $cliente['Usuario_Web'] ? 'Web: ' . $cliente['Usuario_Web'] : 'Solo presencial' ?>
                                </small>
                            </td>
                            <td>
                                <?= htmlspecialchars($cliente['Tipo_Documento']) ?>: <?= htmlspecialchars($cliente['Num_Documento']) ?>
                            </td>
                            <td>
                                <?php if ($cliente['Telefono']): ?>
                                    <i class="fas fa-phone" style="color: #28a745;"></i> <?= htmlspecialchars($cliente['Telefono']) ?><br>
                                <?php endif; ?>
                                <?php if ($cliente['Correo']): ?>
                                    <i class="fas fa-envelope" style="color: #17a2b8;"></i> <?= htmlspecialchars($cliente['Correo']) ?>
                                <?php endif; ?>
                                <?php if (!$cliente['Telefono'] && !$cliente['Correo']): ?>
                                    <small style="color: #6c757d;">Sin contacto</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($cliente['Fecha_Creacion'])) ?>
                                <br>
                                <small style="color: #6c757d;"><?= date('H:i', strtotime($cliente['Fecha_Creacion'])) ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $cliente['total_pedidos'] > 0 ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $cliente['total_pedidos'] ?>
                                </span>
                                <?php if ($cliente['ultimo_pedido']): ?>
                                    <br><small style="color: #6c757d;">Último: <?= date('d/m/Y', strtotime($cliente['ultimo_pedido'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>S/ <?= number_format($cliente['total_gastado'], 2) ?></td>
                            <td>
                                <?php if ($cliente['Usuario_Web']): ?>
                                    <span class="badge badge-info">Web</span>
                                    <?php if ($cliente['Estado_Usuario'] === 'Activo'): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactivo</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-warning">Presencial</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="editarCliente(<?= $cliente['ClienteID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $cliente['ClienteID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="verPedidos(<?= $cliente['ClienteID'] ?>)" class="btn btn-sm" style="background: #28a745; color: white;">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Nuevo/Editar Cliente -->
    <div id="modalCliente" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nuevo Cliente</h2>
            <form id="formCliente">
                <input type="hidden" id="clienteId" name="clienteId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="nombreApellidos">Nombre y Apellidos *</label>
                        <input type="text" id="nombreApellidos" name="nombreApellidos" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipoDocumento">Tipo de Documento *</label>
                        <select id="tipoDocumento" name="tipoDocumento" class="form-control" required>
                            <?php foreach ($tiposDocumento as $codigo => $nombre): ?>
                            <option value="<?= $codigo ?>"><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="numDocumento">Número de Documento *</label>
                        <input type="text" id="numDocumento" name="numDocumento" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono" class="form-control">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="direccion">Dirección</label>
                        <input type="text" id="direccion" name="direccion" class="form-control">
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="button" onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../js/table-filters.js"></script>
    <script>
    let clienteFilter;
    
    // Inicializar filtros cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar sistema de filtros personalizado para clientes
        clienteFilter = new TableFilter('tablaClientes', {
            searchInput: 'buscarCliente',
            filters: [
                { id: 'filtroTipo', attribute: 'data-tipo' },
                { id: 'filtroDocumento', attribute: 'data-documento' }
            ],
            onFilter: function(stats) {
                document.getElementById('clientes-counter').textContent = `${stats.visible} de ${stats.total} clientes`;
                console.log(`Clientes filtrados: ${stats.visible}/${stats.total}`);
            }
        });
        
        // Filtro especial para tipo "con_pedidos"
        document.getElementById('filtroTipo').addEventListener('change', function() {
            if (this.value === 'con_pedidos') {
                filtrarConPedidos();
            } else {
                clienteFilter.filter();
            }
        });
        
        // Mostrar contador inicial
        const stats = clienteFilter.getStats();
        document.getElementById('clientes-counter').textContent = `${stats.total} clientes registrados`;
        
        console.log('✓ Sistema de filtros de clientes inicializado');
    });

    function filtrarConPedidos() {
        const rows = document.querySelectorAll('#tablaClientes tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const pedidosBadge = row.querySelector('.badge');
            const numPedidos = parseInt(pedidosBadge.textContent) || 0;
            
            if (numPedidos > 0) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        document.getElementById('clientes-counter').textContent = `${visibleCount} clientes con pedidos`;
    }

    function limpiarFiltros() {
        if (clienteFilter) {
            clienteFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nuevo Cliente';
        document.getElementById('formCliente').reset();
        document.getElementById('clienteId').value = '';
        document.getElementById('modalCliente').style.display = 'block';
    }

    function editarCliente(id) {
        fetch(`../../../api/admin/cliente-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cliente = data.cliente;
                    document.getElementById('tituloModal').textContent = 'Editar Cliente';
                    document.getElementById('clienteId').value = cliente.ClienteID;
                    document.getElementById('nombreApellidos').value = cliente.Nombre_Apellidos;
                    document.getElementById('tipoDocumento').value = cliente.Tipo_Documento;
                    document.getElementById('numDocumento').value = cliente.Num_Documento;
                    document.getElementById('telefono').value = cliente.Telefono || '';
                    document.getElementById('correo').value = cliente.Correo || '';
                    document.getElementById('direccion').value = cliente.Direccion || '';
                    document.getElementById('modalCliente').style.display = 'block';
                } else {
                    alert('Error al cargar cliente: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/cliente-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const c = data.cliente;
                    alert(`DETALLES DEL CLIENTE\n\n` +
                          `Nombre: ${c.Nombre_Apellidos}\n` +
                          `Documento: ${c.Tipo_Documento} ${c.Num_Documento}\n` +
                          `Teléfono: ${c.Telefono || 'No registrado'}\n` +
                          `Correo: ${c.Correo || 'No registrado'}\n` +
                          `Dirección: ${c.Direccion || 'No registrada'}\n` +
                          `Registro: ${new Date(c.Fecha_Creacion).toLocaleString()}\n` +
                          `Total pedidos: ${c.total_pedidos || 0}\n` +
                          `Total gastado: S/ ${parseFloat(c.total_gastado || 0).toFixed(2)}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verPedidos(clienteId) {
        alert(`Función para ver pedidos del cliente ${clienteId} - En desarrollo`);
    }

    function exportarClientes() {
        window.open('../../../api/admin/exportar-clientes.php', '_blank');
    }

    function cerrarModal() {
        document.getElementById('modalCliente').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formCliente').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validaciones del lado cliente
        if (!data.nombreApellidos.trim()) {
            alert('El nombre es obligatorio');
            return;
        }
        
        if (!data.numDocumento.trim()) {
            alert('El número de documento es obligatorio');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch('../../../api/admin/guardar-cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cliente guardado correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('modalCliente');
        if (event.target === modal) {
            modal.style.display = 'none';
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
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: none;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        position: relative;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        position: absolute;
        right: 15px;
        top: 10px;
    }

    .close:hover {
        color: black;
    }
    </style>
</body>
</html>