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

// Obtener estadísticas de proveedores
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_proveedores,
        SUM(CASE WHEN Estado = 'A' THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN Estado = 'I' THEN 1 ELSE 0 END) as inactivos,
        COUNT(DISTINCT CASE WHEN k.ProveedorID IS NOT NULL THEN p.ProveedorID END) as con_movimientos
     FROM Proveedor p
     LEFT JOIN Kardex k ON p.ProveedorID = k.ProveedorID"
);

// Obtener proveedores con información de movimientos
$proveedores = $db->fetchAll(
    "SELECT p.ProveedorID, p.Razon_Social, p.Ruc, p.Contacto, p.Telefono, 
            p.Direccion, p.Tipo_Producto, p.Correo, p.Estado,
            COUNT(k.KardexID) as total_movimientos,
            COALESCE(SUM(k.Cantidad * k.Precio_Unitario), 0) as total_compras,
            MAX(k.Fecha) as ultima_compra
     FROM Proveedor p
     LEFT JOIN Kardex k ON p.ProveedorID = k.ProveedorID AND k.Tipo_Movimiento = 'Entrada'
     GROUP BY p.ProveedorID
     ORDER BY p.Razon_Social ASC"
);

// Obtener tipos de productos únicos para filtros
$tiposProducto = $db->fetchAll(
    "SELECT DISTINCT Tipo_Producto 
     FROM Proveedor 
     WHERE Tipo_Producto IS NOT NULL AND Tipo_Producto != ''
     ORDER BY Tipo_Producto"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores — Chifa Matsue</title>
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
                <h1><i class="fas fa-truck"></i> Gestión de Proveedores</h1>
                <p>Administración completa de proveedores y suministros</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Proveedores</div>
                    <div class="metric-value"><?= $stats['total_proveedores'] ?></div>
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
                    <small>Suspendidos</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Con Movimientos</div>
                    <div class="metric-value"><?= $stats['con_movimientos'] ?></div>
                    <small>Activos en compras</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Proveedor
                    </button>
                    <button onclick="exportarProveedores()" class="btn btn-outline">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <button onclick="importarProveedores()" class="btn btn-outline">
                        <i class="fas fa-upload"></i> Importar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="A">Activos</option>
                        <option value="I">Inactivos</option>
                    </select>
                    <select id="filtroTipoProducto" class="form-control">
                        <option value="">Todos los productos</option>
                        <?php foreach ($tiposProducto as $tipo): ?>
                        <option value="<?= htmlspecialchars($tipo['Tipo_Producto']) ?>"><?= htmlspecialchars($tipo['Tipo_Producto']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filtroMovimientos" class="form-control">
                        <option value="">Todos</option>
                        <option value="con_movimientos">Con movimientos</option>
                        <option value="sin_movimientos">Sin movimientos</option>
                    </select>
                    <input type="text" id="buscarProveedor" placeholder="Buscar por razón social, RUC o contacto..." class="form-control" style="width: 350px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="proveedores-counter">Cargando...</span>
            </div>

            <!-- Tabla de Proveedores -->
            <div class="admin-table">
                <table id="tablaProveedores">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>RUC</th>
                            <th>Contacto</th>
                            <th>Tipo de Producto</th>
                            <th>Movimientos</th>
                            <th>Total Compras</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                        <tr data-estado="<?= $proveedor['Estado'] ?>" 
                            data-tipo-producto="<?= htmlspecialchars($proveedor['Tipo_Producto'] ?? '') ?>"
                            data-movimientos="<?= $proveedor['total_movimientos'] > 0 ? 'con_movimientos' : 'sin_movimientos' ?>">
                            <td>
                                <strong><?= htmlspecialchars($proveedor['Razon_Social']) ?></strong>
                                <br>
                                <small style="color: #6c757d;">
                                    ID: <?= $proveedor['ProveedorID'] ?>
                                    <?php if ($proveedor['Direccion']): ?>
                                        | <?= htmlspecialchars($proveedor['Direccion']) ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td><?= htmlspecialchars($proveedor['Ruc']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($proveedor['Contacto']) ?></strong>
                                <?php if ($proveedor['Telefono']): ?>
                                    <br><i class="fas fa-phone" style="color: #28a745;"></i> <?= htmlspecialchars($proveedor['Telefono']) ?>
                                <?php endif; ?>
                                <?php if ($proveedor['Correo']): ?>
                                    <br><i class="fas fa-envelope" style="color: #17a2b8;"></i> <?= htmlspecialchars($proveedor['Correo']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($proveedor['Tipo_Producto']): ?>
                                    <span class="badge badge-info"><?= htmlspecialchars($proveedor['Tipo_Producto']) ?></span>
                                <?php else: ?>
                                    <small style="color: #6c757d;">No especificado</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $proveedor['total_movimientos'] > 0 ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $proveedor['total_movimientos'] ?>
                                </span>
                                <?php if ($proveedor['ultima_compra']): ?>
                                    <br><small style="color: #6c757d;">Última: <?= date('d/m/Y', strtotime($proveedor['ultima_compra'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>S/ <?= number_format($proveedor['total_compras'], 2) ?></td>
                            <td>
                                <span class="badge <?= $proveedor['Estado'] === 'A' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $proveedor['Estado'] === 'A' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editarProveedor(<?= $proveedor['ProveedorID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $proveedor['ProveedorID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="toggleEstado(<?= $proveedor['ProveedorID'] ?>, '<?= $proveedor['Estado'] ?>')" 
                                        class="btn btn-sm" style="background: <?= $proveedor['Estado'] === 'A' ? '#dc3545' : '#28a745' ?>; color: white;">
                                    <i class="fas <?= $proveedor['Estado'] === 'A' ? 'fa-ban' : 'fa-check' ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Nuevo/Editar Proveedor -->
    <div id="modalProveedor" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nuevo Proveedor</h2>
            <form id="formProveedor">
                <input type="hidden" id="proveedorId" name="proveedorId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="razonSocial">Razón Social *</label>
                        <input type="text" id="razonSocial" name="razonSocial" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ruc">RUC *</label>
                        <input type="text" id="ruc" name="ruc" class="form-control" pattern="[0-9]{11}" maxlength="11" required>
                        <small style="color: #6c757d;">11 dígitos</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="contacto">Persona de Contacto *</label>
                        <input type="text" id="contacto" name="contacto" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono *</label>
                        <input type="text" id="telefono" name="telefono" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipoProducto">Tipo de Producto</label>
                        <input type="text" id="tipoProducto" name="tipoProducto" class="form-control" 
                               placeholder="Ej: Abarrotes, Carnes, Verduras">
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
    let proveedorFilter;
    
    // Inicializar filtros cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar sistema de filtros personalizado para proveedores
        proveedorFilter = new TableFilter('tablaProveedores', {
            searchInput: 'buscarProveedor',
            filters: [
                { id: 'filtroEstado', attribute: 'data-estado' },
                { id: 'filtroTipoProducto', attribute: 'data-tipo-producto' },
                { id: 'filtroMovimientos', attribute: 'data-movimientos' }
            ],
            onFilter: function(stats) {
                document.getElementById('proveedores-counter').textContent = `${stats.visible} de ${stats.total} proveedores`;
                console.log(`Proveedores filtrados: ${stats.visible}/${stats.total}`);
            }
        });
        
        // Mostrar contador inicial
        const stats = proveedorFilter.getStats();
        document.getElementById('proveedores-counter').textContent = `${stats.total} proveedores registrados`;
        
        console.log('✓ Sistema de filtros de proveedores inicializado');
    });

    function limpiarFiltros() {
        if (proveedorFilter) {
            proveedorFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nuevo Proveedor';
        document.getElementById('formProveedor').reset();
        document.getElementById('proveedorId').value = '';
        document.getElementById('modalProveedor').style.display = 'block';
    }

    function editarProveedor(id) {
        fetch(`../../../api/admin/proveedor-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const proveedor = data.proveedor;
                    document.getElementById('tituloModal').textContent = 'Editar Proveedor';
                    document.getElementById('proveedorId').value = proveedor.ProveedorID;
                    document.getElementById('razonSocial').value = proveedor.Razon_Social;
                    document.getElementById('ruc').value = proveedor.Ruc;
                    document.getElementById('contacto').value = proveedor.Contacto;
                    document.getElementById('telefono').value = proveedor.Telefono;
                    document.getElementById('correo').value = proveedor.Correo || '';
                    document.getElementById('tipoProducto').value = proveedor.Tipo_Producto || '';
                    document.getElementById('direccion').value = proveedor.Direccion || '';
                    document.getElementById('modalProveedor').style.display = 'block';
                } else {
                    alert('Error al cargar proveedor: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/proveedor-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const p = data.proveedor;
                    alert(`DETALLES DEL PROVEEDOR\n\n` +
                          `Razón Social: ${p.Razon_Social}\n` +
                          `RUC: ${p.Ruc}\n` +
                          `Contacto: ${p.Contacto}\n` +
                          `Teléfono: ${p.Telefono}\n` +
                          `Correo: ${p.Correo || 'No registrado'}\n` +
                          `Tipo de Producto: ${p.Tipo_Producto || 'No especificado'}\n` +
                          `Dirección: ${p.Direccion || 'No registrada'}\n` +
                          `Estado: ${p.Estado === 'A' ? 'Activo' : 'Inactivo'}\n` +
                          `Total movimientos: ${p.total_movimientos || 0}\n` +
                          `Total compras: S/ ${parseFloat(p.total_compras || 0).toFixed(2)}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function toggleEstado(id, estadoActual) {
        const nuevoEstado = estadoActual === 'A' ? 'I' : 'A';
        const accion = nuevoEstado === 'A' ? 'activar' : 'desactivar';
        
        if (confirm(`¿Está seguro de ${accion} este proveedor?`)) {
            fetch('../../../api/admin/toggle-proveedor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    proveedorId: id,
                    nuevoEstado: nuevoEstado
                })
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

    function exportarProveedores() {
        window.open('../../../api/admin/exportar-proveedores.php', '_blank');
    }

    function importarProveedores() {
        alert('Función de importación en desarrollo');
    }

    function cerrarModal() {
        document.getElementById('modalProveedor').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formProveedor').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validaciones del lado cliente
        if (!data.razonSocial.trim()) {
            alert('La razón social es obligatoria');
            return;
        }
        
        if (!/^[0-9]{11}$/.test(data.ruc)) {
            alert('El RUC debe tener exactamente 11 dígitos');
            return;
        }
        
        if (!data.contacto.trim()) {
            alert('La persona de contacto es obligatoria');
            return;
        }
        
        if (!data.telefono.trim()) {
            alert('El teléfono es obligatorio');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch('../../../api/admin/guardar-proveedor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Proveedor guardado correctamente');
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
        const modal = document.getElementById('modalProveedor');
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