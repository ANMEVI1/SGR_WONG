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

// Obtener estadísticas de métodos de pago
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_metodos,
        SUM(CASE WHEN Estado = 1 THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN Estado = 0 THEN 1 ELSE 0 END) as inactivos,
        SUM(CASE WHEN Requiere_Referencia = 1 THEN 1 ELSE 0 END) as con_referencia
     FROM Metodo_Pago"
);

// Obtener métodos de pago con uso en comprobantes
$metodos = $db->fetchAll(
    "SELECT mp.MetPagID, mp.Nombre, mp.Icono, mp.Requiere_Referencia, mp.Estado,
            COUNT(DISTINCT cp.ComPagID) as total_usos,
            COALESCE(SUM(cp.Total), 0) as total_ventas
     FROM Metodo_Pago mp
     LEFT JOIN Comprobante_Pago cp ON mp.MetPagID = cp.MetPagID
     GROUP BY mp.MetPagID
     ORDER BY mp.Estado DESC, mp.Nombre ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Métodos de Pago — Chifa Matsue</title>
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
                <h1><i class="fas fa-credit-card"></i> Gestión de Métodos de Pago</h1>
                <p>Administración de métodos de pago aceptados en el restaurante</p>
                <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 12px 15px; margin-top: 15px; border-radius: 4px; font-size: 0.9rem;">
                    <i class="fas fa-info-circle" style="color: #0066cc;"></i> 
                    <strong>Nota sobre Iconos:</strong> La columna "Icono" muestra el NOMBRE del archivo de imagen. 
                    Para que se visualicen correctamente, las imágenes deben estar en la carpeta <code>assets/img/metodos-pago/</code> del servidor.
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Métodos</div>
                    <div class="metric-value"><?= $stats['total_metodos'] ?></div>
                    <small>Registrados</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Activos</div>
                    <div class="metric-value"><?= $stats['activos'] ?></div>
                    <small>Disponibles</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Inactivos</div>
                    <div class="metric-value"><?= $stats['inactivos'] ?></div>
                    <small>Deshabilitados</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Con Referencia</div>
                    <div class="metric-value"><?= $stats['con_referencia'] ?></div>
                    <small>Requieren N° Op.</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Método de Pago
                    </button>
                    <button onclick="window.location.reload()" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                    <input type="text" id="buscarMetodo" placeholder="Buscar método..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="metodos-counter">Cargando...</span>
            </div>

            <!-- Tabla de Métodos de Pago -->
            <div class="admin-table">
                <?php if (empty($metodos)): ?>
                    <div style="text-align: center; padding: 60px; color: #6c757d;">
                        <i class="fas fa-credit-card" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay métodos de pago registrados</h3>
                        <p>Comienza agregando el primer método de pago</p>
                        <button onclick="abrirModalNuevo()" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Agregar Primer Método
                        </button>
                    </div>
                <?php else: ?>
                <table id="tablaMetodos">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Icono</th>
                            <th>Requiere Referencia</th>
                            <th>Total Usos</th>
                            <th>Total Ventas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metodos as $metodo): ?>
                        <tr data-estado="<?= $metodo['Estado'] ?>">
                            <td><strong><?= htmlspecialchars($metodo['Nombre']) ?></strong></td>
                            <td>
                                <?php if ($metodo['Icono']): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-image" style="color: #17a2b8;"></i>
                                        <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;"><?= htmlspecialchars($metodo['Icono']) ?></code>
                                    </span>
                                <?php else: ?>
                                    <small style="color: #6c757d;"><i class="fas fa-ban"></i> Sin icono</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($metodo['Requiere_Referencia']): ?>
                                    <span class="badge badge-info">Sí</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($metodo['total_usos']) ?></td>
                            <td>S/ <?= number_format($metodo['total_ventas'], 2) ?></td>
                            <td>
                                <span class="badge <?= $metodo['Estado'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $metodo['Estado'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editarMetodo(<?= $metodo['MetPagID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $metodo['MetPagID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="toggleEstado(<?= $metodo['MetPagID'] ?>, <?= $metodo['Estado'] ?>)" 
                                        class="btn btn-sm" style="background: <?= $metodo['Estado'] ? '#dc3545' : '#28a745' ?>; color: white;">
                                    <i class="fas fa-<?= $metodo['Estado'] ? 'ban' : 'check' ?>"></i>
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

    <!-- Modal Nuevo/Editar Método de Pago -->
    <div id="modalMetodo" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nuevo Método de Pago</h2>
            <form id="formMetodo">
                <input type="hidden" id="metodoId" name="metodoId">
                
                <div style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label for="nombre">Nombre del Método *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required placeholder="Ej: Efectivo, Yape, Tarjeta">
                    </div>
                    
                    <div class="form-group">
                        <label for="icono">Nombre del Archivo de Icono</label>
                        <input type="text" id="icono" name="icono" class="form-control" 
                               placeholder="Ej: efectivo.png, yape.png, tarjeta.png"
                               title="Solo ingresa el NOMBRE del archivo de imagen, no subas el archivo aquí">
                        <small style="color: #6c757d;">
                            💡 <strong>Importante:</strong> Solo ingresa el NOMBRE del archivo (ej: <code>yape.png</code>). 
                            Las imágenes deben estar en <code>assets/img/metodos-pago/</code> del servidor.
                            <br>Este campo NO sube imágenes, solo guarda la referencia.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" id="requiereReferencia" name="requiereReferencia" value="1" style="width: auto;">
                            <span>Requiere Número de Referencia/Operación</span>
                        </label>
                        <small style="color: #6c757d; margin-left: 30px;">
                            💡 Marcar si el cajero debe ingresar número de operación al cobrar (para Yape, Plin, transferencias)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado">Estado *</label>
                        <select id="estado" name="estado" class="form-control" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
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
    let metodoFilter;
    
    document.addEventListener('DOMContentLoaded', function() {
        metodoFilter = new TableFilter('tablaMetodos', {
            searchInput: 'buscarMetodo',
            filters: [
                { id: 'filtroEstado', attribute: 'data-estado' }
            ],
            onFilter: function(stats) {
                document.getElementById('metodos-counter').textContent = `${stats.visible} de ${stats.total} métodos de pago`;
            }
        });
        
        const stats = metodoFilter.getStats();
        document.getElementById('metodos-counter').textContent = `${stats.total} métodos de pago registrados`;
        
        console.log('✓ Sistema de filtros inicializado');
    });

    function limpiarFiltros() {
        if (metodoFilter) {
            metodoFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nuevo Método de Pago';
        document.getElementById('formMetodo').reset();
        document.getElementById('metodoId').value = '';
        document.getElementById('modalMetodo').style.display = 'block';
    }

    function editarMetodo(id) {
        console.log('Editando método ID:', id);
        fetch(`../../../api/admin/metodo-pago-detalle.php?id=${id}`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data recibida:', data);
                if (data.success) {
                    const metodo = data.metodo;
                    document.getElementById('tituloModal').textContent = 'Editar Método de Pago';
                    document.getElementById('metodoId').value = metodo.MetPagID;
                    document.getElementById('nombre').value = metodo.Nombre;
                    document.getElementById('icono').value = metodo.Icono || '';
                    document.getElementById('requiereReferencia').checked = metodo.Requiere_Referencia == 1;
                    document.getElementById('estado').value = metodo.Estado;
                    document.getElementById('modalMetodo').style.display = 'block';
                } else {
                    alert('Error al cargar método: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                alert('Error de conexión: ' + error.message);
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/metodo-pago-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const m = data.metodo;
                    alert(`DETALLES DEL MÉTODO DE PAGO\n\n` +
                          `Nombre: ${m.Nombre}\n` +
                          `Icono: ${m.Icono || 'Sin icono'}\n` +
                          `Requiere referencia: ${m.Requiere_Referencia ? 'Sí' : 'No'}\n` +
                          `Estado: ${m.Estado ? 'Activo' : 'Inactivo'}\n` +
                          `Total usos: ${m.total_usos}\n` +
                          `Total ventas: S/ ${parseFloat(m.total_ventas).toFixed(2)}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function toggleEstado(id, estadoActual) {
        const nuevoEstado = estadoActual ? 0 : 1;
        const accion = nuevoEstado ? 'activar' : 'desactivar';
        
        if (!confirm(`¿Está seguro de ${accion} este método de pago?`)) {
            return;
        }
        
        fetch('../../../api/admin/toggle-estado-metodo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ metodoId: id, estado: nuevoEstado })
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

    function cerrarModal() {
        document.getElementById('modalMetodo').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formMetodo').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            metodoId: formData.get('metodoId'),
            nombre: formData.get('nombre'),
            icono: formData.get('icono'),
            requiereReferencia: formData.get('requiereReferencia') ? 1 : 0,
            estado: formData.get('estado')
        };
        
        if (!data.nombre.trim()) {
            alert('El nombre del método es obligatorio');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        console.log('Enviando datos:', data);
        fetch('../../../api/admin/guardar-metodo-pago.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                alert('Método de pago guardado correctamente');
                location.reload();
            } else {
                alert('Error: ' + data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert('Error de conexión: ' + error.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('modalMetodo');
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
