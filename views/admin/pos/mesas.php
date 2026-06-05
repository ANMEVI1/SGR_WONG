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

// Obtener estadísticas de mesas
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_mesas,
        SUM(CASE WHEN Estado = 'Disponible' THEN 1 ELSE 0 END) as disponibles,
        SUM(CASE WHEN Estado = 'Ocupada' THEN 1 ELSE 0 END) as ocupadas,
        SUM(CASE WHEN Estado = 'Reservada' THEN 1 ELSE 0 END) as reservadas,
        SUM(Capacidad) as capacidad_total
     FROM Mesa"
);

// Obtener mesas con pedidos activos
$mesas = $db->fetchAll(
    "SELECT m.MesaID, m.Codigo_Mesa, m.Numero_Mesa, m.Capacidad, m.Estado,
            COUNT(DISTINCT p.PedidoID) as total_pedidos,
            MAX(p.Fecha_Hora) as ultimo_pedido
     FROM Mesa m
     LEFT JOIN Pedido p ON m.MesaID = p.MesaID AND p.Estado NOT IN ('Pagado', 'Cancelado')
     GROUP BY m.MesaID
     ORDER BY m.Numero_Mesa ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Mesas — Chifa Matsue</title>
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
                <h1><i class="fas fa-table"></i> Gestión de Mesas</h1>
                <p>Administración de mesas y control de ocupación del restaurante</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Mesas</div>
                    <div class="metric-value"><?= $stats['total_mesas'] ?></div>
                    <small>Registradas</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Disponibles</div>
                    <div class="metric-value"><?= $stats['disponibles'] ?></div>
                    <small>Libres</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Ocupadas</div>
                    <div class="metric-value"><?= $stats['ocupadas'] ?></div>
                    <small>En uso</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Capacidad Total</div>
                    <div class="metric-value"><?= $stats['capacidad_total'] ?></div>
                    <small>Comensales</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Mesa
                    </button>
                    <button onclick="window.location.reload()" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="Disponible">Disponibles</option>
                        <option value="Ocupada">Ocupadas</option>
                        <option value="Reservada">Reservadas</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>
                    <input type="text" id="buscarMesa" placeholder="Buscar por código o número..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="mesas-counter">Cargando...</span>
            </div>

            <!-- Tabla de Mesas -->
            <div class="admin-table">
                <?php if (empty($mesas)): ?>
                    <div style="text-align: center; padding: 60px; color: #6c757d;">
                        <i class="fas fa-table" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay mesas registradas</h3>
                        <p>Comienza agregando la primera mesa</p>
                        <button onclick="abrirModalNuevo()" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Agregar Primera Mesa
                        </button>
                    </div>
                <?php else: ?>
                <table id="tablaMesas">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Número</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Pedidos Activos</th>
                            <th>Último Uso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesas as $mesa): ?>
                        <tr data-estado="<?= htmlspecialchars($mesa['Estado']) ?>">
                            <td><strong><?= htmlspecialchars($mesa['Codigo_Mesa']) ?></strong></td>
                            <td><?= $mesa['Numero_Mesa'] ?></td>
                            <td>
                                <i class="fas fa-user"></i> <?= $mesa['Capacidad'] ?> personas
                            </td>
                            <td>
                                <span class="badge 
                                    <?= $mesa['Estado'] === 'Disponible' ? 'badge-success' : '' ?>
                                    <?= $mesa['Estado'] === 'Ocupada' ? 'badge-danger' : '' ?>
                                    <?= $mesa['Estado'] === 'Reservada' ? 'badge-warning' : '' ?>
                                    <?= $mesa['Estado'] === 'Mantenimiento' ? 'badge-secondary' : '' ?>">
                                    <?= htmlspecialchars($mesa['Estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($mesa['total_pedidos'] > 0): ?>
                                    <span class="badge badge-info"><?= $mesa['total_pedidos'] ?></span>
                                <?php else: ?>
                                    <small style="color: #6c757d;">Sin pedidos</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($mesa['ultimo_pedido']): ?>
                                    <?= date('d/m/Y H:i', strtotime($mesa['ultimo_pedido'])) ?>
                                <?php else: ?>
                                    <small style="color: #6c757d;">Nunca usada</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="editarMesa(<?= $mesa['MesaID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $mesa['MesaID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="cambiarEstado(<?= $mesa['MesaID'] ?>, '<?= $mesa['Estado'] ?>')" 
                                        class="btn btn-sm" style="background: #28a745; color: white;">
                                    <i class="fas fa-exchange-alt"></i>
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

    <!-- Modal Nuevo/Editar Mesa -->
    <div id="modalMesa" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nueva Mesa</h2>
            <form id="formMesa">
                <input type="hidden" id="mesaId" name="mesaId">
                
                <div style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label for="codigoMesa">Código de Mesa *</label>
                        <input type="text" id="codigoMesa" name="codigoMesa" class="form-control" required placeholder="Ej: MESA-01, BARRA-01">
                        <small style="color: #6c757d;">Código único identificador</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="numeroMesa">Número de Mesa *</label>
                        <input type="number" id="numeroMesa" name="numeroMesa" class="form-control" min="1" required placeholder="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="capacidad">Capacidad (personas) *</label>
                        <input type="number" id="capacidad" name="capacidad" class="form-control" min="1" max="20" required placeholder="4">
                        <small style="color: #6c757d;">Número máximo de comensales</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado *</label>
                        <select id="estado" name="estado" class="form-control" required>
                            <option value="Disponible">Disponible</option>
                            <option value="Ocupada">Ocupada</option>
                            <option value="Reservada">Reservada</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                        </select>
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
    let mesaFilter;
    
    document.addEventListener('DOMContentLoaded', function() {
        mesaFilter = new TableFilter('tablaMesas', {
            searchInput: 'buscarMesa',
            filters: [
                { id: 'filtroEstado', attribute: 'data-estado' }
            ],
            onFilter: function(stats) {
                document.getElementById('mesas-counter').textContent = `${stats.visible} de ${stats.total} mesas`;
            }
        });
        
        const stats = mesaFilter.getStats();
        document.getElementById('mesas-counter').textContent = `${stats.total} mesas registradas`;
        
        console.log('✓ Sistema de filtros de mesas inicializado');
    });

    function limpiarFiltros() {
        if (mesaFilter) {
            mesaFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nueva Mesa';
        document.getElementById('formMesa').reset();
        document.getElementById('mesaId').value = '';
        document.getElementById('modalMesa').style.display = 'block';
    }

    function editarMesa(id) {
        fetch(`../../../api/admin/mesa-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const mesa = data.mesa;
                    document.getElementById('tituloModal').textContent = 'Editar Mesa';
                    document.getElementById('mesaId').value = mesa.MesaID;
                    document.getElementById('codigoMesa').value = mesa.Codigo_Mesa;
                    document.getElementById('numeroMesa').value = mesa.Numero_Mesa;
                    document.getElementById('capacidad').value = mesa.Capacidad;
                    document.getElementById('estado').value = mesa.Estado;
                    document.getElementById('modalMesa').style.display = 'block';
                } else {
                    alert('Error al cargar mesa: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/mesa-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const m = data.mesa;
                    alert(`DETALLES DE LA MESA\n\n` +
                          `Código: ${m.Codigo_Mesa}\n` +
                          `Número: ${m.Numero_Mesa}\n` +
                          `Capacidad: ${m.Capacidad} personas\n` +
                          `Estado: ${m.Estado}\n` +
                          `Pedidos activos: ${m.total_pedidos || 0}\n` +
                          `Último uso: ${m.ultimo_pedido || 'Nunca'}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function cambiarEstado(id, estadoActual) {
        const opciones = ['Disponible', 'Ocupada', 'Reservada', 'Mantenimiento'];
        let mensaje = 'Seleccione nuevo estado:\n\n';
        opciones.forEach((op, i) => {
            mensaje += `${i + 1}. ${op}${op === estadoActual ? ' (actual)' : ''}\n`;
        });
        
        const seleccion = prompt(mensaje, '1');
        if (seleccion && seleccion >= 1 && seleccion <= 4) {
            const nuevoEstado = opciones[seleccion - 1];
            
            fetch('../../../api/admin/cambiar-estado-mesa.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mesaId: id, estado: nuevoEstado })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }
    }

    function cerrarModal() {
        document.getElementById('modalMesa').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formMesa').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        if (!data.codigoMesa.trim()) {
            alert('El código de mesa es obligatorio');
            return;
        }
        
        if (!data.numeroMesa || data.numeroMesa < 1) {
            alert('El número de mesa debe ser mayor a 0');
            return;
        }
        
        if (!data.capacidad || data.capacidad < 1) {
            alert('La capacidad debe ser mayor a 0');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch('../../../api/admin/guardar-mesa.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Mesa guardada correctamente');
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
        const modal = document.getElementById('modalMesa');
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
