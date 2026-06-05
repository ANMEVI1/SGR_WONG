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

// Obtener estadísticas de roles
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_roles,
        COUNT(DISTINCT e.EmpleadoID) as empleados_asignados
     FROM Tipo_Rol tr
     LEFT JOIN Empleado e ON tr.RolID = e.RolID"
);

// Obtener roles con cantidad de empleados
$roles = $db->fetchAll(
    "SELECT tr.RolID, tr.Nombre, tr.Descripcion,
            COUNT(e.EmpleadoID) as total_empleados
     FROM Tipo_Rol tr
     LEFT JOIN Empleado e ON tr.RolID = e.RolID
     GROUP BY tr.RolID
     ORDER BY tr.Nombre ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles — Chifa Matsue</title>
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
                <h1><i class="fas fa-user-tag"></i> Gestión de Roles</h1>
                <p>Administración de roles y responsabilidades del personal</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Roles</div>
                    <div class="metric-value"><?= $stats['total_roles'] ?></div>
                    <small>Configurados</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Empleados Asignados</div>
                    <div class="metric-value"><?= $stats['empleados_asignados'] ?></div>
                    <small>Con rol activo</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Rol
                    </button>
                    <button onclick="window.location.reload()" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <input type="text" id="buscarRol" placeholder="Buscar rol..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="roles-counter">Cargando...</span>
            </div>

            <!-- Tabla de Roles -->
            <div class="admin-table">
                <?php if (empty($roles)): ?>
                    <div style="text-align: center; padding: 60px; color: #6c757d;">
                        <i class="fas fa-user-tag" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay roles registrados</h3>
                        <p>Comienza agregando el primer rol</p>
                        <button onclick="abrirModalNuevo()" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Agregar Primer Rol
                        </button>
                    </div>
                <?php else: ?>
                <table id="tablaRoles">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Empleados Asignados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $rol): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($rol['Nombre']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($rol['Descripcion']) ?></td>
                            <td>
                                <span class="badge <?= $rol['total_empleados'] > 0 ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $rol['total_empleados'] ?> empleado<?= $rol['total_empleados'] != 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editarRol(<?= $rol['RolID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $rol['RolID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($rol['total_empleados'] == 0): ?>
                                <button onclick="eliminarRol(<?= $rol['RolID'] ?>)" class="btn btn-sm" style="background: #dc3545; color: white;">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Detalles del Rol con Empleados -->
    <div id="modalDetalles" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <span class="close" onclick="cerrarModalDetalles()">&times;</span>
            <div id="modalDetallesContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Modal Cambio de Rol -->
    <div id="modalCambioRol" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="cerrarModalCambioRol()">&times;</span>
            <div id="modalCambioRolContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Rol -->
    <div id="modalRol" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nuevo Rol</h2>
            <form id="formRol">
                <input type="hidden" id="rolId" name="rolId">
                
                <div style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label for="nombre">Nombre del Rol *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required placeholder="Ej: Cocinero, Cajero">
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción *</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required placeholder="Describe las responsabilidades de este rol"></textarea>
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
    let rolFilter;
    
    document.addEventListener('DOMContentLoaded', function() {
        rolFilter = new TableFilter('tablaRoles', {
            searchInput: 'buscarRol',
            filters: [],
            onFilter: function(stats) {
                document.getElementById('roles-counter').textContent = `${stats.visible} de ${stats.total} roles`;
            }
        });
        
        const stats = rolFilter.getStats();
        document.getElementById('roles-counter').textContent = `${stats.total} roles registrados`;
        
        console.log('✓ Sistema de filtros inicializado');
    });

    function limpiarFiltros() {
        if (rolFilter) {
            rolFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nuevo Rol';
        document.getElementById('formRol').reset();
        document.getElementById('rolId').value = '';
        document.getElementById('modalRol').style.display = 'block';
    }

    function editarRol(id) {
        fetch(`../../../api/admin/rol-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rol = data.rol;
                    document.getElementById('tituloModal').textContent = 'Editar Rol';
                    document.getElementById('rolId').value = rol.RolID;
                    document.getElementById('nombre').value = rol.Nombre;
                    document.getElementById('descripcion').value = rol.Descripcion;
                    document.getElementById('modalRol').style.display = 'block';
                } else {
                    alert('Error al cargar rol: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/rol-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalDetalles(data.rol, data.empleados);
                } else {
                    alert('Error al cargar detalles: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function mostrarModalDetalles(rol, empleados) {
        let empleadosHtml = '';
        if (empleados && empleados.length > 0) {
            empleadosHtml = `
                <div style="margin-top: 20px;">
                    <h3 style="margin-bottom: 15px; color: #333;">
                        <i class="fas fa-users"></i> Empleados con este rol (${empleados.length})
                    </h3>
                    <div class="admin-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>DNI</th>
                                    <th>Turno</th>
                                    <th>Teléfono</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            empleados.forEach(emp => {
                empleadosHtml += `
                    <tr>
                        <td><strong>${emp.Nombre_Apellidos}</strong></td>
                        <td>${emp.DNI}</td>
                        <td><span class="badge badge-info">${emp.Turno}</span></td>
                        <td>${emp.Telefono}</td>
                        <td>
                            <button onclick="cambiarRolEmpleado(${emp.EmpleadoID}, '${emp.Nombre_Apellidos}', ${rol.RolID})" 
                                    class="btn btn-sm" style="background: #ff9800; color: white;">
                                <i class="fas fa-exchange-alt"></i> Cambiar
                            </button>
                        </td>
                    </tr>
                `;
            });
            empleadosHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } else {
            empleadosHtml = `
                <div style="margin-top: 20px; padding: 30px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #6c757d;">
                    <i class="fas fa-user-slash" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p style="margin: 0;">No hay empleados asignados a este rol</p>
                </div>
            `;
        }
        
        document.getElementById('modalDetallesContent').innerHTML = `
            <div style="padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 20px;">
                    <i class="fas fa-user-tag"></i> ${rol.Nombre}
                </h2>
                
                <div style="padding: 15px; background: #e8f5e9; border-radius: 8px; margin-bottom: 20px;">
                    <div style="color: #2e7d32; font-size: 0.85rem; margin-bottom: 5px;">
                        <i class="fas fa-info-circle"></i> Descripción
                    </div>
                    <div style="font-size: 1rem; color: #1b5e20;">
                        ${rol.Descripcion}
                    </div>
                </div>
                
                ${empleadosHtml}
                
                <div style="margin-top: 30px; text-align: right;">
                    <button onclick="cerrarModalDetalles()" class="btn btn-secondary">Cerrar</button>
                </div>
            </div>
        `;
        
        document.getElementById('modalDetalles').style.display = 'block';
    }

    function cambiarRolEmpleado(empleadoId, nombreEmpleado, rolActualId) {
        // Cargar todos los roles
        fetch('../../../api/admin/listar-roles.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalCambioRol(empleadoId, nombreEmpleado, rolActualId, data.roles);
                } else {
                    alert('Error al cargar roles');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function mostrarModalCambioRol(empleadoId, nombreEmpleado, rolActualId, roles) {
        let rolesOptions = '';
        roles.forEach(rol => {
            const selected = rol.RolID == rolActualId ? 'selected' : '';
            rolesOptions += `<option value="${rol.RolID}" ${selected}>${rol.Nombre} - ${rol.Descripcion}</option>`;
        });
        
        document.getElementById('modalCambioRolContent').innerHTML = `
            <div style="padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 20px;">
                    <i class="fas fa-exchange-alt"></i> Cambiar Rol
                </h2>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <strong><i class="fas fa-user"></i> Empleado:</strong> ${nombreEmpleado}
                </div>
                
                <form id="formCambioRol">
                    <input type="hidden" id="cambioEmpleadoId" value="${empleadoId}">
                    <input type="hidden" id="cambioRolActual" value="${rolActualId}">
                    
                    <div class="form-group">
                        <label for="nuevoRol">Seleccionar nuevo rol *</label>
                        <select id="nuevoRol" name="nuevoRol" class="form-control" required>
                            ${rolesOptions}
                        </select>
                    </div>
                    
                    <div style="margin-top: 30px; text-align: right;">
                        <button type="button" onclick="cerrarModalCambioRol()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirmar Cambio
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        document.getElementById('modalCambioRol').style.display = 'block';
        
        // Manejar envío del formulario
        document.getElementById('formCambioRol').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const empleadoId = document.getElementById('cambioEmpleadoId').value;
            const nuevoRolId = document.getElementById('nuevoRol').value;
            const rolActualId = document.getElementById('cambioRolActual').value;
            
            if (nuevoRolId == rolActualId) {
                alert('Debes seleccionar un rol diferente al actual');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
            
            fetch('../../../api/admin/cambiar-rol-empleado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    empleadoId: empleadoId,
                    nuevoRolId: nuevoRolId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Rol cambiado correctamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Cambio';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Confirmar Cambio';
            });
        });
    }

    function cerrarModalDetalles() {
        document.getElementById('modalDetalles').style.display = 'none';
    }

    function cerrarModalCambioRol() {
        document.getElementById('modalCambioRol').style.display = 'none';
    }

    function eliminarRol(id) {
        if (!confirm('¿Estás seguro de eliminar este rol?\n\nEsta acción no se puede deshacer.')) {
            return;
        }
        
        fetch('../../../api/admin/eliminar-rol.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rolId: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rol eliminado correctamente');
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

    function cerrarModal() {
        document.getElementById('modalRol').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formRol').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        if (!data.nombre.trim()) {
            alert('El nombre del rol es obligatorio');
            return;
        }
        
        if (!data.descripcion.trim()) {
            alert('La descripción es obligatoria');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch('../../../api/admin/guardar-rol.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rol guardado correctamente');
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
        const modalRol = document.getElementById('modalRol');
        const modalDetalles = document.getElementById('modalDetalles');
        const modalCambioRol = document.getElementById('modalCambioRol');
        
        if (event.target === modalRol) {
            modalRol.style.display = 'none';
        }
        if (event.target === modalDetalles) {
            modalDetalles.style.display = 'none';
        }
        if (event.target === modalCambioRol) {
            modalCambioRol.style.display = 'none';
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
