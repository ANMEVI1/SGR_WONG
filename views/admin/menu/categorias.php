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

// Obtener estadísticas de categorías
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_categorias,
        SUM(CASE WHEN Tipo = 'Plato' THEN 1 ELSE 0 END) as tipo_plato,
        SUM(CASE WHEN Tipo = 'Insumo' THEN 1 ELSE 0 END) as tipo_insumo
     FROM Categoria"
);

// Contar platos e insumos por categoría
$categorias = $db->fetchAll(
    "SELECT c.CatID, c.Nombre, c.Descripcion, c.Tipo,
            COUNT(DISTINCT CASE WHEN c.Tipo = 'Plato' THEN p.PlatoID END) as total_platos,
            COUNT(DISTINCT CASE WHEN c.Tipo = 'Insumo' THEN i.InsumoID END) as total_insumos
     FROM Categoria c
     LEFT JOIN Plato p ON c.CatID = p.CatID
     LEFT JOIN Insumo i ON c.CatID = i.CatID
     GROUP BY c.CatID
     ORDER BY c.Tipo, c.Nombre ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías — Chifa Matsue</title>
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
                <h1><i class="fas fa-tags"></i> Gestión de Categorías</h1>
                <p>Administración de categorías para platos e insumos del restaurante</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Categorías</div>
                    <div class="metric-value"><?= $stats['total_categorias'] ?></div>
                    <small>Registradas</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Categorías de Platos</div>
                    <div class="metric-value"><?= $stats['tipo_plato'] ?></div>
                    <small>Para carta</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Categorías de Insumos</div>
                    <div class="metric-value"><?= $stats['tipo_insumo'] ?></div>
                    <small>Para inventario</small>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="abrirModalNuevo()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                    <button onclick="window.location.reload()" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroTipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        <option value="Plato">Platos</option>
                        <option value="Insumo">Insumos</option>
                    </select>
                    <input type="text" id="buscarCategoria" placeholder="Buscar por nombre o descripción..." class="form-control" style="width: 350px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="categorias-counter">Cargando...</span>
            </div>

            <!-- Tabla de Categorías -->
            <div class="admin-table">
                <?php if (empty($categorias)): ?>
                    <div style="text-align: center; padding: 60px; color: #6c757d;">
                        <i class="fas fa-tags" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay categorías registradas</h3>
                        <p>Comienza agregando la primera categoría</p>
                        <button onclick="abrirModalNuevo()" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Agregar Primera Categoría
                        </button>
                    </div>
                <?php else: ?>
                <table id="tablaCategorias">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Elementos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $cat): ?>
                        <tr data-tipo="<?= htmlspecialchars($cat['Tipo']) ?>">
                            <td><?= $cat['CatID'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($cat['Nombre']) ?></strong>
                            </td>
                            <td>
                                <?php if ($cat['Descripcion']): ?>
                                    <?= htmlspecialchars($cat['Descripcion']) ?>
                                <?php else: ?>
                                    <small style="color: #6c757d;">Sin descripción</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $cat['Tipo'] === 'Plato' ? 'badge-info' : 'badge-warning' ?>">
                                    <?= htmlspecialchars($cat['Tipo']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($cat['Tipo'] === 'Plato'): ?>
                                    <span class="badge badge-success"><?= $cat['total_platos'] ?> platos</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?= $cat['total_insumos'] ?> insumos</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="editarCategoria(<?= $cat['CatID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="verDetalles(<?= $cat['CatID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white;">
                                    <i class="fas fa-eye"></i>
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

    <!-- Modal Detalles de Categoría con Elementos -->
    <div id="modalDetalles" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <span class="close" onclick="cerrarModalDetalles()">&times;</span>
            <div id="modalDetallesContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Modal Cambio de Categoría -->
    <div id="modalCambioCategoria" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="cerrarModalCambioCategoria()">&times;</span>
            <div id="modalCambioCategoriaContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Categoría -->
    <div id="modalCategoria" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="tituloModal">Nueva Categoría</h2>
            <form id="formCategoria">
                <input type="hidden" id="categoriaId" name="categoriaId">
                
                <div style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required placeholder="Ej: Arroces y Chaufas">
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Descripción opcional de la categoría"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo">Tipo *</label>
                        <select id="tipo" name="tipo" class="form-control" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="Plato">Plato (para carta del restaurante)</option>
                            <option value="Insumo">Insumo (para inventario)</option>
                        </select>
                        <small style="color: #6c757d; margin-top: 5px; display: block;">
                            <i class="fas fa-info-circle"></i> Plato = categorías de menú | Insumo = categorías de almacén
                        </small>
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
    let categoriaFilter;
    
    document.addEventListener('DOMContentLoaded', function() {
        categoriaFilter = new TableFilter('tablaCategorias', {
            searchInput: 'buscarCategoria',
            filters: [
                { id: 'filtroTipo', attribute: 'data-tipo' }
            ],
            onFilter: function(stats) {
                document.getElementById('categorias-counter').textContent = `${stats.visible} de ${stats.total} categorías`;
            }
        });
        
        const stats = categoriaFilter.getStats();
        document.getElementById('categorias-counter').textContent = `${stats.total} categorías registradas`;
        
        console.log('✓ Sistema de filtros de categorías inicializado');
    });

    function limpiarFiltros() {
        if (categoriaFilter) {
            categoriaFilter.clearFilters();
        }
    }

    function abrirModalNuevo() {
        document.getElementById('tituloModal').textContent = 'Nueva Categoría';
        document.getElementById('formCategoria').reset();
        document.getElementById('categoriaId').value = '';
        document.getElementById('modalCategoria').style.display = 'block';
    }

    function editarCategoria(id) {
        fetch(`../../../api/admin/categoria-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cat = data.categoria;
                    document.getElementById('tituloModal').textContent = 'Editar Categoría';
                    document.getElementById('categoriaId').value = cat.CatID;
                    document.getElementById('nombre').value = cat.Nombre;
                    document.getElementById('descripcion').value = cat.Descripcion || '';
                    document.getElementById('tipo').value = cat.Tipo;
                    document.getElementById('modalCategoria').style.display = 'block';
                } else {
                    alert('Error al cargar categoría: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function verDetalles(id) {
        fetch(`../../../api/admin/categoria-detalle.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalDetalles(data.categoria, data.elementos);
                } else {
                    alert('Error al cargar detalles: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function mostrarModalDetalles(categoria, elementos) {
        const esPlato = categoria.Tipo === 'Plato';
        const tipoElemento = esPlato ? 'platos' : 'insumos';
        const iconoElemento = esPlato ? 'utensils' : 'boxes';
        
        let elementosHtml = '';
        if (elementos && elementos.length > 0) {
            elementosHtml = `
                <div style="margin-top: 20px;">
                    <h3 style="margin-bottom: 15px; color: #333;">
                        <i class="fas fa-${iconoElemento}"></i> ${esPlato ? 'Platos' : 'Insumos'} en esta categoría (${elementos.length})
                    </h3>
                    <div class="admin-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    ${esPlato ? '<th>Estado</th>' : '<th>Stock</th>'}
                                    ${esPlato ? '<th>Variantes</th>' : '<th>Unidad</th>'}
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            elementos.forEach(elem => {
                elementosHtml += `
                    <tr>
                        <td><strong>${elem.Nombre}</strong></td>
                        ${esPlato 
                            ? `<td><span class="badge badge-${elem.Estado === 'Disponible' ? 'success' : 'secondary'}">${elem.Estado}</span></td>`
                            : `<td><span class="badge badge-info">${elem.Stock_Actual} ${elem.Unidad_Medida}</span></td>`
                        }
                        ${esPlato 
                            ? `<td><span class="badge badge-info">${elem.total_variantes} variantes</span></td>`
                            : `<td>${elem.Unidad_Medida}</td>`
                        }
                        <td>
                            <button onclick="cambiarCategoria${esPlato ? 'Plato' : 'Insumo'}(${esPlato ? elem.PlatoID : elem.InsumoID}, '${elem.Nombre.replace(/'/g, "\\'") }', ${categoria.CatID})" 
                                    class="btn btn-sm" style="background: #ff9800; color: white;">
                                <i class="fas fa-exchange-alt"></i> Cambiar
                            </button>
                        </td>
                    </tr>
                `;
            });
            elementosHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } else {
            elementosHtml = `
                <div style="margin-top: 20px; padding: 30px; background: #f8f9fa; border-radius: 8px; text-align: center; color: #6c757d;">
                    <i class="fas fa-${iconoElemento}" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p style="margin: 0;">No hay ${tipoElemento} asignados a esta categoría</p>
                </div>
            `;
        }
        
        document.getElementById('modalDetallesContent').innerHTML = `
            <div style="padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 20px;">
                    <i class="fas fa-tag"></i> ${categoria.Nombre}
                </h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div style="padding: 15px; background: #e3f2fd; border-radius: 8px;">
                        <div style="color: #1976d2; font-size: 0.85rem; margin-bottom: 5px;">
                            <i class="fas fa-tag"></i> Tipo
                        </div>
                        <div style="font-size: 1.2rem; font-weight: bold; color: #1565c0;">
                            ${categoria.Tipo}
                        </div>
                    </div>
                    
                    <div style="padding: 15px; background: #e8f5e9; border-radius: 8px;">
                        <div style="color: #2e7d32; font-size: 0.85rem; margin-bottom: 5px;">
                            <i class="fas fa-${iconoElemento}"></i> ${esPlato ? 'Platos' : 'Insumos'}
                        </div>
                        <div style="font-size: 1.2rem; font-weight: bold; color: #1b5e20;">
                            ${elementos ? elementos.length : 0}
                        </div>
                    </div>
                </div>
                
                ${categoria.Descripcion ? `
                <div style="padding: 15px; background: #fff3cd; border-radius: 8px; margin-bottom: 20px;">
                    <div style="color: #856404; font-size: 0.85rem; margin-bottom: 5px;">
                        <i class="fas fa-info-circle"></i> Descripción
                    </div>
                    <div style="color: #856404;">${categoria.Descripcion}</div>
                </div>
                ` : ''}
                
                ${elementosHtml}
                
                <div style="margin-top: 30px; text-align: right;">
                    <button onclick="cerrarModalDetalles()" class="btn btn-secondary">Cerrar</button>
                </div>
            </div>
        `;
        
        document.getElementById('modalDetalles').style.display = 'block';
    }

    function cambiarCategoriaPlato(platoId, nombrePlato, categoriaActualId) {
        // Cargar todas las categorías de tipo Plato
        fetch('../../../api/admin/listar-categorias.php?tipo=Plato')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalCambioCategoria(platoId, nombrePlato, categoriaActualId, data.categorias, 'Plato');
                } else {
                    alert('Error al cargar categorías');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function cambiarCategoriaInsumo(insumoId, nombreInsumo, categoriaActualId) {
        // Cargar todas las categorías de tipo Insumo
        fetch('../../../api/admin/listar-categorias.php?tipo=Insumo')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarModalCambioCategoria(insumoId, nombreInsumo, categoriaActualId, data.categorias, 'Insumo');
                } else {
                    alert('Error al cargar categorías');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function mostrarModalCambioCategoria(elementoId, nombreElemento, categoriaActualId, categorias, tipo) {
        let categoriasOptions = '';
        categorias.forEach(cat => {
            const selected = cat.CatID == categoriaActualId ? 'selected' : '';
            categoriasOptions += `<option value="${cat.CatID}" ${selected}>${cat.Nombre}${cat.Descripcion ? ' - ' + cat.Descripcion : ''}</option>`;
        });
        
        document.getElementById('modalCambioCategoriaContent').innerHTML = `
            <div style="padding: 20px;">
                <h2 style="color: #2c3e50; margin-bottom: 20px;">
                    <i class="fas fa-exchange-alt"></i> Cambiar Categoría
                </h2>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <strong><i class="fas fa-${tipo === 'Plato' ? 'utensils' : 'boxes'}"></i> ${tipo}:</strong> ${nombreElemento}
                </div>
                
                <form id="formCambioCategoria">
                    <input type="hidden" id="cambioElementoId" value="${elementoId}">
                    <input type="hidden" id="cambioTipo" value="${tipo}">
                    <input type="hidden" id="cambioCategoriaActual" value="${categoriaActualId}">
                    
                    <div class="form-group">
                        <label for="nuevaCategoria">Seleccionar nueva categoría *</label>
                        <select id="nuevaCategoria" name="nuevaCategoria" class="form-control" required>
                            ${categoriasOptions}
                        </select>
                    </div>
                    
                    <div style="margin-top: 30px; text-align: right;">
                        <button type="button" onclick="cerrarModalCambioCategoria()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirmar Cambio
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        document.getElementById('modalCambioCategoria').style.display = 'block';
        
        // Manejar envío del formulario
        document.getElementById('formCambioCategoria').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const elementoId = document.getElementById('cambioElementoId').value;
            const tipo = document.getElementById('cambioTipo').value;
            const nuevaCategoriaId = document.getElementById('nuevaCategoria').value;
            const categoriaActualId = document.getElementById('cambioCategoriaActual').value;
            
            if (nuevaCategoriaId == categoriaActualId) {
                alert('Debes seleccionar una categoría diferente a la actual');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
            
            fetch('../../../api/admin/cambiar-categoria-elemento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    elementoId: elementoId,
                    tipo: tipo,
                    nuevaCategoriaId: nuevaCategoriaId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Categoría cambiada correctamente');
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

    function cerrarModalCambioCategoria() {
        document.getElementById('modalCambioCategoria').style.display = 'none';
    }

    function cerrarModal() {
        document.getElementById('modalCategoria').style.display = 'none';
    }

    // Envío del formulario
    document.getElementById('formCategoria').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        if (!data.nombre.trim()) {
            alert('El nombre es obligatorio');
            return;
        }
        
        if (!data.tipo) {
            alert('Debe seleccionar un tipo');
            return;
        }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;
        
        fetch('../../../api/admin/guardar-categoria.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Categoría guardada correctamente');
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
        const modalCategoria = document.getElementById('modalCategoria');
        const modalDetalles = document.getElementById('modalDetalles');
        const modalCambioCategoria = document.getElementById('modalCambioCategoria');
        
        if (event.target === modalCategoria) {
            modalCategoria.style.display = 'none';
        }
        if (event.target === modalDetalles) {
            modalDetalles.style.display = 'none';
        }
        if (event.target === modalCambioCategoria) {
            modalCambioCategoria.style.display = 'none';
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
