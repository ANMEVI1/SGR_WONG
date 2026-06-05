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

// Obtener estadísticas de turnos
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_turnos,
        COUNT(DISTINCT e.EmpleadoID) as empleados_asignados
     FROM Turno t
     LEFT JOIN Empleado e ON t.TurnoID = e.TurnoID"
);

// Obtener turnos con cantidad de empleados
$turnos = $db->fetchAll(
    "SELECT t.TurnoID, t.Descripcion, t.Hora_Inicio, t.Hora_Fin,
            COUNT(e.EmpleadoID) as total_empleados
     FROM Turno t
     LEFT JOIN Empleado e ON t.TurnoID = e.TurnoID
     GROUP BY t.TurnoID
     ORDER BY t.Hora_Inicio ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos — Chifa Matsue</title>
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
                <h1><i class="fas fa-clock"></i> Gestión de Turnos</h1>
                <p>Administración de horarios de trabajo del personal</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Turnos</div>
                    <div class="metric-value"><?= $stats['total_turnos'] ?></div>
                    <small>Configurados</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Empleados Asignados</div>
                    <div class="metric-value"><?= $stats['empleados_asignados'] ?></div>
                    <small>Con turno activo</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Turno
                    </button>
                    <button onclick="window.location.reload()" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <input type="text" id="buscarTurno" placeholder="Buscar turno..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="turnos-counter">Cargando...</span>
            </div>

            <!-- Tabla de Turnos -->
            <div class="admin-table">
                <?php if (empty($turnos)): ?>
                    <div style="text-align: center; padding: 60px; color: #6c757d;">
                        <i class="fas fa-clock" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay turnos registrados</h3>
                        <p>Comienza agregando el primer turno</p>
                        <button onclick="abrirModalNuevo()" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Agregar Primer Turno
                        </button>
                    </div>
                <?php else: ?>
                <table id="tablaTurnos">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Hora Inicio</th>
                            <th>Hora Fin</th>
                            <th>Duración</th>
                            <th>Empleados Asignados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turnos as $turno): 
                            // Calcular duración
                            $inicio = new DateTime($turno['Hora_Inicio']);
                            $fin = new DateTime($turno['Hora_Fin']);
                            $duracion = $inicio->diff($fin);
                            $horas = $duracion->h + ($duracion->days * 24);
                            $minutos = $duracion->i;
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($turno['Descripcion']) ?></strong>
                            </td>
                            <td><?= date('h:i A', strtotime($turno['Hora_Inicio'])) ?></td>
                            <td><?= date('h:i A', strtotime($turno['Hora_Fin'])) ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?= $horas ?>h <?= $minutos ?>m
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $turno['total_empleados'] > 0 ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $turno['total_empleados'] ?> empleado<?= $turno['total_empleados'] != 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editarTurno(<?= $turno['TurnoID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $turno['TurnoID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($turno['total_empleados'] == 0): ?>
                                <button onclick="eliminarTurno(<?= $turno['TurnoID'] ?>)" class="btn btn-sm" style="background: #dc3545; color: white;">
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

    <!-- Modal Detalles del Turno con Empleados -->
    <div id="modalDetalles" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <span class="close" onclick="cerrarModalDetalles()">&times;</span>
            <div id="modalDetallesContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Modal Cambio de Turno -->
    <div id="modalCambioTurno" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="cerrarModalCambioTurno()">&times;</span>
            <div id="modalCambioTurnoContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Turno -->
    <div id="modalTurno" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nuevo Turno</h2>
            <form id="formTurno">
                <input type="hidden" id="turnoId" name="turnoId">
                
                <div style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label for="descripcion">Descripción del Turno *</label>
                        <input type="text" id="descripcion" name="descripcion" class="form-control" required placeholder="Ej: Mañana, Tarde, Noche">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="horaInicio">Hora Inicio *</label>
                            <input type="time" id="horaInicio" name="horaInicio" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="horaFin">Hora Fin *</label>
                            <input type="time" id="horaFin" name="horaFin" class="form-control" required>
                        </div>
                    </div>

                    <div class="info-box" style="background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px;">
                        <i class="fas fa-info-circle" style="color: #2196F3;"></i>
                        <strong>Nota:</strong> Asegúrate de que las horas no se superpongan con otros turnos existentes.
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
    let turnoFilter;
    
    document.addEventListener('DOMContentLoaded', function() {
        turnoFilter = new TableFilter('tablaTurnos', {
            searchInput: 'buscarTurno',
            filters: [],
            onFilter: function(stats) {
                document.getElementById('turnos-counter').textContent = `${stats.visible} de ${stats.total} turnos`;
            }
        });
        
        const stats = turnoFilter.getStats();
        document.getElementById('turnos-counter').textContent = `${stats.total} turnos registrados`;
        
        console.log('✓ Sistema de filtros inicializado');
    });

    function limpiarFiltros() {
        if (turnoFilter) {
            turnoFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nuevo Turno';
        document.getElementById('formTurno').reset();
        document.getElementById('turnoId').value = '';
        document.getElementById('modalTurno').style.display = 'block';
    }

    function editarTurno(id) {
        fetch(`../../../api/admin/turno-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const turno = data.turno;
                    document.getElementById('tituloModal').textContent = 'Editar Turno';
                    document.getElementById('turnoId').value = turno.TurnoID;
                    document.getElementById('descripcion').value = turno.Descripcion;
                    document.getElementById('horaInicio').value = turno.Hora_Inicio;
                    document.getElementById('horaFin').value = turno.Hora_Fin;
                    document.getElementById('modalTurno').style.display = 'block';
                } else {
                    alert('Error al cargar turno: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/turno-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalDetalles(data.turno, data.empleados);
                } else {
                    alert('Error al cargar detalles: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function mostrarModalDetalles(turno, empleados) {
        const inicio = new Date('2000-01-01 ' + turno.Hora_Inicio);
        const fin = new Date('2000-01-01 ' + turno.Hora_Fin);
        const inicioFormatted = inicio.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit', hour12: true});
        const finFormatted = fin.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit', hour12: true});
        
        let empleadosHtml = '';
        if (empleados && empleados.length > 0) {
            empleadosHtml = `
                <div style="margin-top: 20px;">
                    <h3 style="margin-bottom: 15px; color: #333;">
                        <i class="fas fa-users"></i> Empleados en este turno (${empleados.length})
                    </h3>
                    <div class="admin-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>DNI</th>
                                    <th>Rol</th>
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
                        <td><span class="badge badge-info">${emp.Rol}</span></td>
                        <td>${emp.Telefono}</td>
                        <td>
                            <button onclick="cambiarTurnoEmpleado(${emp.EmpleadoID}, '${emp.Nombre_Apellidos}', ${turno.TurnoID})" 
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
                    <p style="margin: 0;">No hay empleados asignados a este turno</p>
                </div>
            `;
        }
        
        document.getElementById('modalDetallesContent').innerHTML = `
            <div style="padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 20px;">
                    <i class="fas fa-clock"></i> ${turno.Descripcion}
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div style="padding: 15px; background: #e3f2fd; border-radius: 8px;">
                        <div style="color: #1976d2; font-size: 0.85rem; margin-bottom: 5px;">
                            <i class="fas fa-clock"></i> Hora Inicio
                        </div>
                        <div style="font-size: 1.3rem; font-weight: bold; color: #1565c0;">
                            ${inicioFormatted}
                        </div>
                    </div>
                    
                    <div style="padding: 15px; background: #fce4ec; border-radius: 8px;">
                        <div style="color: #c2185b; font-size: 0.85rem; margin-bottom: 5px;">
                            <i class="fas fa-clock"></i> Hora Fin
                        </div>
                        <div style="font-size: 1.3rem; font-weight: bold; color: #ad1457;">
                            ${finFormatted}
                        </div>
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

    function cambiarTurnoEmpleado(empleadoId, nombreEmpleado, turnoActualId) {
        // Cargar todos los turnos
        fetch('../../../api/admin/listar-turnos.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalCambioTurno(empleadoId, nombreEmpleado, turnoActualId, data.turnos);
                } else {
                    alert('Error al cargar turnos');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function mostrarModalCambioTurno(empleadoId, nombreEmpleado, turnoActualId, turnos) {
        let turnosOptions = '';
        turnos.forEach(turno => {
            const selected = turno.TurnoID == turnoActualId ? 'selected' : '';
            const inicio = new Date('2000-01-01 ' + turno.Hora_Inicio).toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit', hour12: true});
            const fin = new Date('2000-01-01 ' + turno.Hora_Fin).toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit', hour12: true});
            turnosOptions += `<option value="${turno.TurnoID}" ${selected}>${turno.Descripcion} (${inicio} - ${fin})</option>`;
        });
        
        document.getElementById('modalCambioTurnoContent').innerHTML = `
            <div style="padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 20px;">
                    <i class="fas fa-exchange-alt"></i> Cambiar Turno
                </h2>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <strong><i class="fas fa-user"></i> Empleado:</strong> ${nombreEmpleado}
                </div>
                
                <form id="formCambioTurno">
                    <input type="hidden" id="cambioEmpleadoId" value="${empleadoId}">
                    <input type="hidden" id="cambioTurnoActual" value="${turnoActualId}">
                    
                    <div class="form-group">
                        <label for="nuevoTurno">Seleccionar nuevo turno *</label>
                        <select id="nuevoTurno" name="nuevoTurno" class="form-control" required>
                            ${turnosOptions}
                        </select>
                    </div>
                    
                    <div style="margin-top: 30px; text-align: right;">
                        <button type="button" onclick="cerrarModalCambioTurno()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirmar Cambio
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        document.getElementById('modalCambioTurno').style.display = 'block';
        
        // Manejar envío del formulario
        document.getElementById('formCambioTurno').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const empleadoId = document.getElementById('cambioEmpleadoId').value;
            const nuevoTurnoId = document.getElementById('nuevoTurno').value;
            const turnoActualId = document.getElementById('cambioTurnoActual').value;
            
            if (nuevoTurnoId == turnoActualId) {
                alert('Debes seleccionar un turno diferente al actual');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
            
            fetch('../../../api/admin/cambiar-turno-empleado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    empleadoId: empleadoId,
                    nuevoTurnoId: nuevoTurnoId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Turno cambiado correctamente');
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

    function cerrarModalCambioTurno() {
        document.getElementById('modalCambioTurno').style.display = 'none';
    }

    function eliminarTurno(id) {
        if (!confirm('¿Estás seguro de eliminar este turno?\n\nEsta acción no se puede deshacer.')) {
            return;
        }
        
        fetch('../../../api/admin/eliminar-turno.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ turnoId: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Turno eliminado correctamente');
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
        document.getElementById('modalTurno').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formTurno').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        if (!data.descripcion.trim()) {
            alert('La descripción del turno es obligatoria');
            return;
        }
        
        if (!data.horaInicio || !data.horaFin) {
            alert('Las horas de inicio y fin son obligatorias');
            return;
        }

        // Validar que hora fin sea mayor que hora inicio
        if (data.horaInicio >= data.horaFin) {
            alert('La hora de fin debe ser mayor que la hora de inicio');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch('../../../api/admin/guardar-turno.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Turno guardado correctamente');
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
        const modalTurno = document.getElementById('modalTurno');
        const modalDetalles = document.getElementById('modalDetalles');
        const modalCambioTurno = document.getElementById('modalCambioTurno');
        
        if (event.target === modalTurno) {
            modalTurno.style.display = 'none';
        }
        if (event.target === modalDetalles) {
            modalDetalles.style.display = 'none';
        }
        if (event.target === modalCambioTurno) {
            modalCambioTurno.style.display = 'none';
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

    .info-box {
        border-radius: 4px;
        font-size: 0.9rem;
    }
    </style>
</body>
</html>
