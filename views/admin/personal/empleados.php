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

// Obtener estadísticas de empleados
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_empleados,
        SUM(CASE WHEN Estado = 'Activo' THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN Estado = 'Inactivo' THEN 1 ELSE 0 END) as inactivos,
        AVG(Sueldo) as sueldo_promedio
     FROM Empleado"
);

// Obtener empleados con información completa
$empleados = $db->fetchAll(
    "SELECT e.EmpleadoID, e.Nombre_Apellidos, e.DNI, e.Telefono, e.Sueldo, 
            e.Estado, e.Fecha_Contratacion,
            r.Nombre as Rol, t.Descripcion as Turno,
            u.Login as Usuario_Sistema,
            DATEDIFF(CURDATE(), e.Fecha_Contratacion) as dias_trabajando
     FROM Empleado e
     JOIN Tipo_Rol r ON e.RolID = r.RolID
     JOIN Turno t ON e.TurnoID = t.TurnoID
     LEFT JOIN Usuario u ON e.UsuarioID = u.UsuarioID
     ORDER BY e.Fecha_Contratacion DESC"
);

// Obtener roles y turnos para formularios
$roles = $db->fetchAll("SELECT RolID, Nombre FROM Tipo_Rol ORDER BY Nombre");
$turnos = $db->fetchAll("SELECT TurnoID, Descripcion FROM Turno ORDER BY Descripcion");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Personal — Chifa Matsue</title>
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
                <h1><i class="fas fa-id-badge"></i> Gestión de Personal</h1>
                <p>Administración completa del personal del restaurante</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Empleados</div>
                    <div class="metric-value"><?= $stats['total_empleados'] ?></div>
                    <small>Registrados</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Activos</div>
                    <div class="metric-value"><?= $stats['activos'] ?></div>
                    <small>Trabajando</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Inactivos</div>
                    <div class="metric-value"><?= $stats['inactivos'] ?></div>
                    <small>Cesados</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Sueldo Promedio</div>
                    <div class="metric-value">S/ <?= number_format($stats['sueldo_promedio'], 0) ?></div>
                    <small>Mensual</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Empleado
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroRol" class="form-control">
                        <option value="">Todos los roles</option>
                        <?php foreach ($roles as $rol): ?>
                        <option value="<?= $rol['Nombre'] ?>"><?= $rol['Nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="Activo">Activos</option>
                        <option value="Inactivo">Inactivos</option>
                    </select>
                    <input type="text" id="buscarEmpleado" placeholder="Buscar por nombre, DNI o teléfono..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="empleados-counter">Cargando...</span>
            </div>

            <!-- Tabla de Empleados -->
            <div class="admin-table">
                <table id="tablaEmpleados">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>DNI</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Turno</th>
                            <th>Sueldo</th>
                            <th>Antigüedad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleados as $empleado): ?>
                        <tr data-rol="<?= $empleado['Rol'] ?>" data-estado="<?= $empleado['Estado'] ?>">
                            <td>
                                <strong><?= htmlspecialchars($empleado['Nombre_Apellidos']) ?></strong>
                                <br>
                                <small style="color: #6c757d;">
                                    <?= $empleado['Usuario_Sistema'] ? 'Usuario: ' . $empleado['Usuario_Sistema'] : 'Sin acceso al sistema' ?>
                                </small>
                            </td>
                            <td><?= htmlspecialchars($empleado['DNI']) ?></td>
                            <td><?= htmlspecialchars($empleado['Telefono']) ?></td>
                            <td>
                                <span class="badge badge-info"><?= htmlspecialchars($empleado['Rol']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($empleado['Turno']) ?></td>
                            <td>S/ <?= number_format($empleado['Sueldo'], 2) ?></td>
                            <td>
                                <?php 
                                $anos = floor($empleado['dias_trabajando'] / 365);
                                $meses = floor(($empleado['dias_trabajando'] % 365) / 30);
                                echo $anos > 0 ? "{$anos} año" . ($anos > 1 ? 's' : '') : '';
                                echo $anos > 0 && $meses > 0 ? ', ' : '';
                                echo $meses > 0 ? "{$meses} mes" . ($meses > 1 ? 'es' : '') : '';
                                echo $anos == 0 && $meses == 0 ? 'Nuevo' : '';
                                ?>
                            </td>
                            <td>
                                <span class="badge <?= $empleado['Estado'] === 'Activo' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $empleado['Estado'] ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editarEmpleado(<?= $empleado['EmpleadoID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $empleado['EmpleadoID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Empleado -->
    <div id="modalEmpleado" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nuevo Empleado</h2>
            <form id="formEmpleado">
                <input type="hidden" id="empleadoId" name="empleadoId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="nombreApellidos">Nombre y Apellidos *</label>
                        <input type="text" id="nombreApellidos" name="nombreApellidos" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dni">DNI *</label>
                        <input type="text" id="dni" name="dni" class="form-control" pattern="[0-9]{8}" maxlength="8" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono *</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sueldo">Sueldo *</label>
                        <input type="number" id="sueldo" name="sueldo" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rolId">Rol *</label>
                        <select id="rolId" name="rolId" class="form-control" required>
                            <option value="">Seleccionar rol</option>
                            <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['RolID'] ?>"><?= $rol['Nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="turnoId">Turno *</label>
                        <select id="turnoId" name="turnoId" class="form-control" required>
                            <option value="">Seleccionar turno</option>
                            <?php foreach ($turnos as $turno): ?>
                            <option value="<?= $turno['TurnoID'] ?>"><?= $turno['Descripcion'] ?></option>
                            <?php endforeach; ?>
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
    let empleadoFilter;
    
    // Inicializar filtros cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar sistema de filtros optimizado
        empleadoFilter = initEmployeeFilters('tablaEmpleados');
        
        // Mostrar contador inicial
        const stats = empleadoFilter.getStats();
        document.getElementById('empleados-counter').textContent = `${stats.total} empleados registrados`;
        
        console.log('✓ Sistema de filtros de empleados inicializado');
    });

    function limpiarFiltros() {
        if (empleadoFilter) {
            empleadoFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nuevo Empleado';
        document.getElementById('formEmpleado').reset();
        document.getElementById('empleadoId').value = '';
        document.getElementById('modalEmpleado').style.display = 'block';
    }

    function editarEmpleado(id) {
        fetch(`../../../api/admin/empleado-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const emp = data.empleado;
                    document.getElementById('tituloModal').textContent = 'Editar Empleado';
                    document.getElementById('empleadoId').value = emp.EmpleadoID;
                    document.getElementById('nombreApellidos').value = emp.Nombre_Apellidos;
                    document.getElementById('dni').value = emp.DNI;
                    document.getElementById('telefono').value = emp.Telefono;
                    document.getElementById('sueldo').value = emp.Sueldo;
                    document.getElementById('rolId').value = emp.RolID;
                    document.getElementById('turnoId').value = emp.TurnoID;
                    document.getElementById('modalEmpleado').style.display = 'block';
                } else {
                    alert('Error al cargar empleado: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/empleado-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const emp = data.empleado;
                    const antiguedad = calcularAntiguedad(emp.Fecha_Contratacion);
                    alert(`DETALLES DEL EMPLEADO\n\n` +
                          `Nombre: ${emp.Nombre_Apellidos}\n` +
                          `DNI: ${emp.DNI}\n` +
                          `Teléfono: ${emp.Telefono}\n` +
                          `Rol: ${emp.Rol}\n` +
                          `Turno: ${emp.Turno}\n` +
                          `Sueldo: S/ ${parseFloat(emp.Sueldo).toFixed(2)}\n` +
                          `Antigüedad: ${antiguedad}\n` +
                          `Estado: ${emp.Estado}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }
    
    function calcularAntiguedad(fechaContratacion) {
        const hoy = new Date();
        const contratacion = new Date(fechaContratacion);
        const diffTime = Math.abs(hoy - contratacion);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        const anos = Math.floor(diffDays / 365);
        const meses = Math.floor((diffDays % 365) / 30);
        
        if (anos > 0) {
            return `${anos} año${anos > 1 ? 's' : ''}${meses > 0 ? `, ${meses} mes${meses > 1 ? 'es' : ''}` : ''}`;
        } else if (meses > 0) {
            return `${meses} mes${meses > 1 ? 'es' : ''}`;
        } else {
            return 'Menos de un mes';
        }
    }

    function cerrarModal() {
        document.getElementById('modalEmpleado').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formEmpleado').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validaciones del lado cliente
        if (!data.nombreApellidos.trim()) {
            alert('El nombre es obligatorio');
            return;
        }
        
        if (!/^[0-9]{8}$/.test(data.dni)) {
            alert('El DNI debe tener exactamente 8 dígitos');
            return;
        }
        
        if (parseFloat(data.sueldo) <= 0) {
            alert('El sueldo debe ser mayor a 0');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch('../../../api/admin/guardar-empleado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Empleado guardado correctamente');
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
        const modal = document.getElementById('modalEmpleado');
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