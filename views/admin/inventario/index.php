<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Verificar permisos (administrador o almacenero)
$userRole = $db->fetchOne(
    "SELECT tu.TipUsuID FROM Usuario u
     JOIN Tipo_Usuario tu ON u.TipUsuID = tu.TipUsuID
     WHERE u.UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

if (!in_array($userRole['TipUsuID'], [1, 4])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Obtener estadísticas de insumos
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_insumos,
        SUM(CASE WHEN Estado = 'Activo' THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN Stock_Actual <= Stock_Minimo THEN 1 ELSE 0 END) as stock_critico,
        SUM(Stock_Actual * Precio_Costo) as valor_inventario
     FROM Insumo"
);

// Obtener insumos con información completa
$insumos = $db->fetchAll(
    "SELECT i.InsumoID, i.Nombre, i.Descripcion, i.Precio_Costo, 
            i.Stock_Actual, i.Stock_Minimo, i.Unidad_Medida, i.Estado,
            c.Nombre as Categoria
     FROM Insumo i
     JOIN Categoria c ON i.CatID = c.CatID
     WHERE c.Tipo = 'Insumo'
     ORDER BY i.Nombre ASC"
);

// Obtener categorías de tipo Insumo para formularios
$categorias = $db->fetchAll(
    "SELECT CatID, Nombre FROM Categoria WHERE Tipo = 'Insumo' ORDER BY Nombre"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Insumos — Chifa Matsue</title>
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
                <h1><i class="fas fa-boxes"></i> Gestión de Insumos</h1>
                <p>Control de inventario y stock de insumos del restaurante</p>
                <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 10px 15px; margin-top: 15px; border-radius: 4px; font-size: 0.85rem;">
                    <i class="fas fa-exclamation-triangle" style="color: #721c24;"></i> 
                    <strong>Alerta:</strong> Los insumos con stock crítico aparecen marcados en rojo. Revísalos regularmente para evitar desabastecimiento.
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Insumos</div>
                    <div class="metric-value"><?= $stats['total_insumos'] ?></div>
                    <small><?= $stats['activos'] ?> activos</small>
                </div>
                
                <div class="metric-card danger">
                    <div class="metric-label">Stock Crítico</div>
                    <div class="metric-value"><?= $stats['stock_critico'] ?></div>
                    <small>Por debajo del mínimo</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Valor Inventario</div>
                    <div class="metric-value">S/ <?= number_format($stats['valor_inventario'], 2) ?></div>
                    <small>Total en stock</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Insumo
                    </button>
                    <button onclick="window.location.reload()" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroCategoria" class="form-control">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['Nombre'] ?>"><?= $cat['Nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="Activo">Activos</option>
                        <option value="Inactivo">Inactivos</option>
                    </select>
                    <select id="filtroStock" class="form-control">
                        <option value="">Todo el stock</option>
                        <option value="critico">Stock crítico</option>
                        <option value="normal">Stock normal</option>
                    </select>
                    <input type="text" id="buscarInsumo" placeholder="Buscar insumo..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="insumos-counter">Cargando...</span>
            </div>

            <!-- Tabla de Insumos -->
            <div class="admin-table">
                <?php if (empty($insumos)): ?>
                    <div style="text-align: center; padding: 60px; color: #6c757d;">
                        <i class="fas fa-boxes" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay insumos registrados</h3>
                        <p>Comienza agregando el primer insumo</p>
                        <button onclick="abrirModalNuevo()" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Agregar Primer Insumo
                        </button>
                    </div>
                <?php else: ?>
                <table id="tablaInsumos">
                    <thead>
                        <tr>
                            <th>Insumo</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Precio Costo</th>
                            <th>Valor Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($insumos as $insumo): 
                            $stockCritico = $insumo['Stock_Actual'] <= $insumo['Stock_Minimo'];
                            $valorStock = $insumo['Stock_Actual'] * $insumo['Precio_Costo'];
                        ?>
                        <tr data-categoria="<?= htmlspecialchars($insumo['Categoria']) ?>" 
                            data-estado="<?= $insumo['Estado'] ?>"
                            data-stock="<?= $stockCritico ? 'critico' : 'normal' ?>">
                            <td>
                                <strong><?= htmlspecialchars($insumo['Nombre']) ?></strong>
                                <?php if ($insumo['Descripcion']): ?>
                                <br><small style="color: #6c757d;"><?= htmlspecialchars($insumo['Descripcion']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($insumo['Categoria']) ?></td>
                            <td>
                                <span class="badge <?= $stockCritico ? 'badge-danger' : 'badge-success' ?>">
                                    <?= number_format($insumo['Stock_Actual'], 2) ?> <?= $insumo['Unidad_Medida'] ?>
                                </span>
                            </td>
                            <td><?= number_format($insumo['Stock_Minimo'], 2) ?> <?= $insumo['Unidad_Medida'] ?></td>
                            <td>S/ <?= number_format($insumo['Precio_Costo'], 2) ?></td>
                            <td>S/ <?= number_format($valorStock, 2) ?></td>
                            <td>
                                <span class="badge <?= $insumo['Estado'] === 'Activo' ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $insumo['Estado'] ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="editarInsumo(<?= $insumo['InsumoID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $insumo['InsumoID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="toggleEstado(<?= $insumo['InsumoID'] ?>, '<?= $insumo['Estado'] ?>')" 
                                        class="btn btn-sm" 
                                        style="background: <?= $insumo['Estado'] === 'Activo' ? '#dc3545' : '#28a745' ?>; color: white;">
                                    <i class="fas fa-power-off"></i>
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

    <!-- Modal Ver Detalles Insumo -->
    <div id="modalDetalles" class="modal" style="display: none;">
        <div class="modal-content modal-detalles" style="max-width: 700px; max-height: 85vh; overflow-y: auto;">
            <span class="close" onclick="cerrarModalDetalles()">&times;</span>
            <div id="detallesContenido"></div>
            <div style="margin-top: 30px; text-align: right;">
                <button onclick="cerrarModalDetalles()" class="btn btn-secondary">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Insumo -->
    <div id="modalInsumo" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nuevo Insumo</h2>
            <form id="formInsumo">
                <input type="hidden" id="insumoId" name="insumoId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="nombre">Nombre del Insumo *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required
                               title="Nombre descriptivo del insumo">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoriaId">Categoría *</label>
                        <select id="categoriaId" name="categoriaId" class="form-control" required
                                title="Categoría a la que pertenece el insumo">
                            <option value="">Seleccionar categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['CatID'] ?>"><?= $cat['Nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="2"
                                  placeholder="Descripción opcional del insumo"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="precioCosto">Precio de Costo (S/) *</label>
                        <input type="number" id="precioCosto" name="precioCosto" class="form-control" 
                               step="0.01" min="0" required
                               title="Precio de costo unitario del insumo">
                        <small style="color: #6c757d;">💡 Precio promedio de compra</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="unidadMedida">Unidad de Medida *</label>
                        <select id="unidadMedida" name="unidadMedida" class="form-control" required
                                title="Unidad en la que se mide el insumo">
                            <option value="">Seleccionar unidad</option>
                            <option value="kg">Kilogramo (kg)</option>
                            <option value="lt">Litro (lt)</option>
                            <option value="unidad">Unidad</option>
                            <option value="bolsa">Bolsa</option>
                            <option value="caja">Caja</option>
                            <option value="paquete">Paquete</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="stockActual">Stock Actual *</label>
                        <input type="number" id="stockActual" name="stockActual" class="form-control" 
                               step="0.01" min="0" required
                               title="Cantidad disponible actualmente en almacén">
                        <small style="color: #6c757d;">💡 Cantidad disponible en almacén</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="stockMinimo">Stock Mínimo *</label>
                        <input type="number" id="stockMinimo" name="stockMinimo" class="form-control" 
                               step="0.01" min="0" required
                               title="Cantidad mínima antes de alerta de reabastecimiento">
                        <small style="color: #6c757d;">💡 Alerta cuando stock baje de este valor</small>
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: right; padding-top: 20px; border-top: 1px solid #dee2e6;">
                    <button type="button" onclick="cerrarModal()" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarInsumo">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../js/table-filters.js"></script>
    <script>
    let insumoFilter;
    
    document.addEventListener('DOMContentLoaded', function() {
        insumoFilter = new TableFilter('tablaInsumos', {
            searchInput: 'buscarInsumo',
            filters: [
                { selectId: 'filtroCategoria', attribute: 'data-categoria' },
                { selectId: 'filtroEstado', attribute: 'data-estado' },
                { selectId: 'filtroStock', attribute: 'data-stock' }
            ],
            onFilter: function(stats) {
                document.getElementById('insumos-counter').textContent = `${stats.visible} de ${stats.total} insumos`;
            }
        });
        
        const stats = insumoFilter.getStats();
        document.getElementById('insumos-counter').textContent = `${stats.total} insumos registrados`;
        
        console.log('✓ Sistema de filtros inicializado');
    });

    function limpiarFiltros() {
        if (insumoFilter) {
            insumoFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nuevo Insumo';
        document.getElementById('formInsumo').reset();
        document.getElementById('insumoId').value = '';
        document.getElementById('modalInsumo').style.display = 'block';
    }

    function editarInsumo(id) {
        fetch(`../../../api/admin/insumo-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ins = data.insumo;
                    document.getElementById('tituloModal').textContent = 'Editar Insumo';
                    document.getElementById('insumoId').value = ins.InsumoID;
                    document.getElementById('nombre').value = ins.Nombre;
                    document.getElementById('descripcion').value = ins.Descripcion || '';
                    document.getElementById('categoriaId').value = ins.CatID;
                    document.getElementById('precioCosto').value = ins.Precio_Costo;
                    document.getElementById('unidadMedida').value = ins.Unidad_Medida;
                    document.getElementById('stockActual').value = ins.Stock_Actual;
                    document.getElementById('stockMinimo').value = ins.Stock_Minimo;
                    document.getElementById('modalInsumo').style.display = 'block';
                } else {
                    alert('Error al cargar insumo: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/insumo-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const ins = data.insumo;
                    mostrarModalDetalles(ins);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function mostrarModalDetalles(insumo) {
        const modal = document.getElementById('modalDetalles');
        const contenido = document.getElementById('detallesContenido');
        
        const estadoBadge = insumo.Estado === 'Activo' 
            ? '<span class="badge badge-success">Activo</span>' 
            : '<span class="badge badge-secondary">Inactivo</span>';
        
        const stockCritico = parseFloat(insumo.Stock_Actual) <= parseFloat(insumo.Stock_Minimo);
        const stockBadge = stockCritico
            ? '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Stock Crítico</span>'
            : '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Stock Normal</span>';
        
        const valorStock = (parseFloat(insumo.Stock_Actual) * parseFloat(insumo.Precio_Costo)).toFixed(2);
        
        // Calcular porcentaje de stock en base a 100%
        // Si stock actual >= stock mínimo → porcentaje proporcional hasta 100%
        // Si stock actual < stock mínimo → porcentaje bajo (crítico)
        let porcentajeStock;
        let porcentajeTexto;
        
        if (parseFloat(insumo.Stock_Actual) >= parseFloat(insumo.Stock_Minimo)) {
            // Stock normal: calcular excedente sobre el mínimo
            const excedente = parseFloat(insumo.Stock_Actual) - parseFloat(insumo.Stock_Minimo);
            const rangoOptimo = parseFloat(insumo.Stock_Minimo) * 2; // 2x el mínimo = 100%
            porcentajeStock = Math.min(((parseFloat(insumo.Stock_Actual) / rangoOptimo) * 100), 100);
            porcentajeTexto = porcentajeStock.toFixed(0) + '%';
        } else {
            // Stock crítico: porcentaje respecto al mínimo
            porcentajeStock = (parseFloat(insumo.Stock_Actual) / parseFloat(insumo.Stock_Minimo)) * 100;
            porcentajeTexto = porcentajeStock.toFixed(0) + '% (Bajo mínimo)';
        }
        
        contenido.innerHTML = `
            <div class="detalle-header">
                <h3>${insumo.Nombre}</h3>
                <div style="display: flex; gap: 8px;">
                    ${estadoBadge}
                    ${stockBadge}
                </div>
            </div>
            
            <div class="detalle-seccion">
                <div class="detalle-titulo"><i class="fas fa-info-circle"></i> Información General</div>
                <div class="detalle-grid">
                    <div class="detalle-item">
                        <span class="detalle-label">Categoría:</span>
                        <span class="detalle-valor">${insumo.Categoria}</span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Unidad de Medida:</span>
                        <span class="detalle-valor">${insumo.Unidad_Medida}</span>
                    </div>
                    <div class="detalle-item" style="grid-column: span 2;">
                        <span class="detalle-label">Descripción:</span>
                        <span class="detalle-valor">${insumo.Descripcion || '<em style="color: #999;">Sin descripción</em>'}</span>
                    </div>
                </div>
            </div>
            
            <div class="detalle-seccion">
                <div class="detalle-titulo"><i class="fas fa-boxes"></i> Control de Stock</div>
                <div class="detalle-grid">
                    <div class="detalle-item">
                        <span class="detalle-label">Stock Actual:</span>
                        <span class="detalle-valor" style="font-weight: 600; color: ${stockCritico ? '#dc3545' : '#28a745'};">
                            ${parseFloat(insumo.Stock_Actual).toFixed(2)} ${insumo.Unidad_Medida}
                        </span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Stock Mínimo:</span>
                        <span class="detalle-valor">${parseFloat(insumo.Stock_Minimo).toFixed(2)} ${insumo.Unidad_Medida}</span>
                    </div>
                </div>
                
                <!-- Barra de progreso de stock -->
                <div style="margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <small style="color: #6c757d; font-weight: 500;">Nivel de Stock</small>
                        <small style="color: #6c757d; font-weight: 500;">${porcentajeTexto}</small>
                    </div>
                    <div style="background: #e9ecef; height: 12px; border-radius: 6px; overflow: hidden;">
                        <div style="width: ${porcentajeStock}%; height: 100%; background: ${stockCritico ? '#dc3545' : '#28a745'}; transition: width 0.3s ease;"></div>
                    </div>
                    ${stockCritico ? '<small style="color: #dc3545; margin-top: 5px; display: block;"><i class="fas fa-exclamation-circle"></i> ¡Requiere reabastecimiento!</small>' : '<small style="color: #28a745; margin-top: 5px; display: block;"><i class="fas fa-check-circle"></i> Stock en nivel aceptable</small>'}
                </div>
            </div>
            
            <div class="detalle-seccion">
                <div class="detalle-titulo"><i class="fas fa-dollar-sign"></i> Información de Costos</div>
                <div class="detalle-stats">
                    <div class="stat-box">
                        <div class="stat-valor">S/ ${parseFloat(insumo.Precio_Costo).toFixed(2)}</div>
                        <div class="stat-label">Precio de Costo Unitario</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-valor">S/ ${valorStock}</div>
                        <div class="stat-label">Valor Total en Stock</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-valor">${parseFloat(insumo.Stock_Actual).toFixed(2)}</div>
                        <div class="stat-label">Unidades Disponibles</div>
                    </div>
                </div>
            </div>
        `;
        
        modal.style.display = 'block';
    }

    function cerrarModalDetalles() {
        document.getElementById('modalDetalles').style.display = 'none';
    }

    function toggleEstado(id, estadoActual) {
        const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
        const accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
        
        if (!confirm(`¿Estás seguro de ${accion} este insumo?`)) {
            return;
        }
        
        fetch('../../../api/admin/toggle-estado-insumo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ insumoId: id, estado: nuevoEstado })
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
        document.getElementById('modalInsumo').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formInsumo').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Validaciones del lado cliente
        if (!data.nombre || !data.nombre.trim()) {
            alert('El nombre del insumo es obligatorio');
            return;
        }
        
        if (!data.categoriaId || data.categoriaId === '') {
            alert('Debe seleccionar una categoría');
            return;
        }
        
        if (!data.unidadMedida || data.unidadMedida === '') {
            alert('Debe seleccionar una unidad de medida');
            return;
        }
        
        if (parseFloat(data.precioCosto) < 0) {
            alert('El precio de costo debe ser mayor o igual a 0');
            return;
        }
        
        if (parseFloat(data.stockActual) < 0) {
            alert('El stock actual no puede ser negativo');
            return;
        }
        
        if (parseFloat(data.stockMinimo) < 0) {
            alert('El stock mínimo no puede ser negativo');
            return;
        }
        
        const submitBtn = document.getElementById('btnGuardarInsumo');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        console.log('Enviando datos de insumo:', data);
        
        fetch('../../../api/admin/guardar-insumo.php', {
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
                alert('Insumo guardado correctamente');
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

    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        const modalInsumo = document.getElementById('modalInsumo');
        const modalDetalles = document.getElementById('modalDetalles');
        
        if (event.target === modalInsumo) {
            modalInsumo.style.display = 'none';
        }
        if (event.target === modalDetalles) {
            modalDetalles.style.display = 'none';
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
        /* Asegura scroll cuando el contenido sea largo */
        overflow-y: auto;
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

    /* Estilos para modal de detalles */
    .modal-detalles {
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .detalle-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 25px;
    }

    .detalle-header h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.5rem;
    }

    .detalle-seccion {
        margin-bottom: 25px;
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }

    .detalle-titulo {
        font-weight: 600;
        color: #495057;
        font-size: 1rem;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #dee2e6;
    }

    .detalle-titulo i {
        margin-right: 8px;
        color: #6c757d;
    }

    .detalle-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .detalle-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .detalle-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }

    .detalle-valor {
        font-size: 0.95rem;
        color: #212529;
        font-weight: 400;
    }

    .detalle-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .stat-valor {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
    }
    </style>
</body>
</html>
