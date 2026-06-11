<?php
require_once __DIR__ . '/../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Obtener estadísticas
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_platos,
        SUM(CASE WHEN Estado = 'Disponible' THEN 1 ELSE 0 END) as disponibles,
        SUM(CASE WHEN Es_Top = 1 THEN 1 ELSE 0 END) as top_ventas,
        SUM(CASE WHEN Es_Promo = 1 THEN 1 ELSE 0 END) as promociones
     FROM Plato"
);

// Cargar categorías tipo Plato para filtros
$categorias = $db->fetchAll(
    "SELECT CatID, Nombre FROM Categoria WHERE Tipo = 'Plato' ORDER BY Nombre"
);

// Cargar platos con información completa
$platos = $db->fetchAll(
    "SELECT pl.PlatoID, pl.Nombre, pl.Descripcion, pl.Imagen_URL,
            pl.Estado, pl.Orden, pl.Es_Top, pl.Es_Promo, pl.CatID,
            cat.Nombre AS Categoria,
            MIN(pv.Precio_Venta) AS Precio_Min,
            COUNT(pv.VarianteID) AS Total_Variantes
     FROM Plato pl
     LEFT JOIN Categoria cat ON pl.CatID = cat.CatID
     LEFT JOIN Plato_Variante pv ON pl.PlatoID = pv.PlatoID AND pv.Estado = 1
     WHERE cat.Tipo = 'Plato'
     GROUP BY pl.PlatoID, pl.Nombre, pl.Descripcion, pl.Imagen_URL,
              pl.Estado, pl.Orden, pl.Es_Top, pl.Es_Promo, pl.CatID, cat.Nombre
     ORDER BY pl.Orden ASC, pl.Nombre ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Menú — Admin Chifa Matsue</title>
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
                <h1><i class="fas fa-utensils"></i> Gestión de Menú</h1>
                <p>Administra los platos, categorías y promociones del restaurante</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Platos</div>
                    <div class="metric-value"><?= $stats['total_platos'] ?></div>
                    <small><?= $stats['disponibles'] ?> disponibles</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Top Ventas</div>
                    <div class="metric-value"><?= $stats['top_ventas'] ?></div>
                    <small>Destacados en web</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Promociones</div>
                    <div class="metric-value"><?= $stats['promociones'] ?></div>
                    <small>Ofertas activas</small>
                </div>
            </div>

            <!-- Acciones y Filtros -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <a href="form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Plato
                    </a>
                    <button class="btn btn-outline" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="Disponible">Disponibles</option>
                        <option value="Oculto">Ocultos</option>
                    </select>
                    <select id="filtroDestacado" class="form-control">
                        <option value="">Todos los platos</option>
                        <option value="top">Solo Top Ventas</option>
                        <option value="promo">Solo Promociones</option>
                        <option value="normal">Sin destacar</option>
                    </select>
                    <input type="text" id="buscarPlato" placeholder="Buscar plato..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltros()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="platos-counter">Cargando...</span>
            </div>

            <!-- Tabla de Platos -->
            <div class="admin-table">
                <?php if (empty($platos)): ?>
                    <div style="text-align: center; padding: 60px; color: #6c757d;">
                        <i class="fas fa-utensils" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                        <h3>No hay platos registrados</h3>
                        <p>Comienza agregando el primer plato al menú</p>
                        <a href="form.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Agregar Primer Plato
                        </a>
                    </div>
                <?php else: ?>
                <table id="tablaPlatos">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Imagen</th>
                            <th>Plato</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th style="text-align: center;">Top</th>
                            <th style="text-align: center;">Promo</th>
                            <th style="text-align: center; width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($platos as $p): 
                        // Determinar tipo de destacado
                        $esTop = !empty($p['Es_Top']);
                        $esPromo = !empty($p['Es_Promo']);
                        $tipoDestacado = 'normal';
                        if ($esTop) $tipoDestacado = 'top';
                        if ($esPromo) $tipoDestacado = 'promo';
                        if ($esTop && $esPromo) $tipoDestacado = 'top-promo';
                    ?>
                        <tr data-estado="<?= htmlspecialchars($p['Estado']) ?>"
                            data-destacado="<?= $tipoDestacado ?>"
                            data-top="<?= $esTop ? '1' : '0' ?>"
                            data-promo="<?= $esPromo ? '1' : '0' ?>">
                            <td>
                                <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                    <?php 
                                    $imagenPath = $p['Imagen_URL'];
                                    // Si no hay imagen, usar placeholder SVG
                                    if (empty($imagenPath)) {
                                        $imagenPath = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Crect fill='%23f0ece4' width='60' height='60'/%3E%3Ctext fill='%23c9954a' font-size='12' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3E?%3C/text%3E%3C/svg%3E";
                                    } elseif (strpos($imagenPath, 'data:') !== 0 && strpos($imagenPath, '../') !== 0 && strpos($imagenPath, 'http') !== 0) {
                                        // Asegurar que la ruta tenga ../../../ al inicio si no lo tiene y no es SVG/URL
                                        $imagenPath = '../../../' . $imagenPath;
                                    }
                                    ?>
                                    <img style="width: 100%; height: 100%; object-fit: cover;"
                                         src="<?= htmlspecialchars($imagenPath) ?>"
                                         alt="<?= htmlspecialchars($p['Nombre']) ?>"
                                         loading="lazy"
                                         onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2760%27 height=%2760%27%3E%3Crect fill=%27%23f0ece4%27 width=%2760%27 height=%2760%27/%3E%3Ctext fill=%27%23c9954a%27 font-size=%2712%27 font-weight=%27bold%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dominant-baseline=%27middle%27%3E?%3C/text%3E%3C/svg%3E';">
                                </div>
                            </td>
                            <td>
                                <strong style="display: block; margin-bottom: 4px;"><?= htmlspecialchars($p['Nombre']) ?></strong>
                                <?php if ($p['Descripcion']): ?>
                                <small style="color: #6c757d; display: block; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars($p['Descripcion']) ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge" style="background: #6c757d; color: white;">
                                    <?= htmlspecialchars($p['Categoria'] ?? 'Sin categoría') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($p['Total_Variantes'] > 0): ?>
                                    <strong style="color: #28a745;">Desde S/ <?= number_format($p['Precio_Min'], 2) ?></strong>
                                    <br><small style="color: #6c757d;"><?= (int)$p['Total_Variantes'] ?> variante(s)</small>
                                <?php else: ?>
                                    <span style="color: #dc3545; font-size: 0.85rem;">Sin variantes</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $p['Estado'] === 'Disponible' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= htmlspecialchars($p['Estado']) ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn btn-sm" 
                                        style="padding: 8px 12px; background: <?= $esTop ? '#28a745' : '#6c757d' ?>; color: white; border: none; border-radius: 4px; cursor: pointer; transition: all 0.2s;"
                                        onclick="toggleFlag(<?= (int)$p['PlatoID'] ?>, 'top', <?= $esTop ? 'false' : 'true' ?>)"
                                        title="<?= $esTop ? 'Quitar de Top Ventas' : 'Marcar como Top Ventas' ?>">
                                    <i class="fas fa-star"></i>
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn btn-sm" 
                                        style="padding: 8px 12px; background: <?= $esPromo ? '#ffc107' : '#6c757d' ?>; color: white; border: none; border-radius: 4px; cursor: pointer; transition: all 0.2s;"
                                        onclick="toggleFlag(<?= (int)$p['PlatoID'] ?>, 'promo', <?= $esPromo ? 'false' : 'true' ?>)"
                                        title="<?= $esPromo ? 'Quitar de Promociones' : 'Marcar como Promoción' ?>">
                                    <i class="fas fa-tags"></i>
                                </button>
                            </td>
                            <td style="text-align: center;">
                                <a href="form.php?id=<?= (int)$p['PlatoID'] ?>" class="btn btn-sm" 
                                   style="padding: 8px 12px; margin-right: 5px; background: #17a2b8; color: white; border: none; border-radius: 4px; text-decoration: none; display: inline-block;"
                                   title="Editar plato">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm" 
                                        style="padding: 8px 12px; background: <?= $p['Estado'] === 'Disponible' ? '#dc3545' : '#28a745' ?>; color: white; border: none; border-radius: 4px; cursor: pointer;"
                                        onclick="toggleEstado(<?= (int)$p['PlatoID'] ?>, '<?= $p['Estado'] === 'Disponible' ? 'Oculto' : 'Disponible' ?>', this)"
                                        title="<?= $p['Estado'] === 'Disponible' ? 'Ocultar plato' : 'Mostrar plato' ?>">
                                    <i class="fas fa-<?= $p['Estado'] === 'Disponible' ? 'eye-slash' : 'eye' ?>"></i>
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

<script src="../../../js/table-filters.js"></script>
<script>
let platoFilter;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando filtros de platos...');
    
    platoFilter = new TableFilter('tablaPlatos', {
        searchInput: 'buscarPlato',
        filters: [
            { 
                selectId: 'filtroEstado', 
                attribute: 'data-estado'
            },
            { 
                selectId: 'filtroDestacado',
                customMatcher: function(row, filterValue) {
                    if (!filterValue) return true;
                    
                    const destacado = row.getAttribute('data-destacado');
                    const esTop = row.getAttribute('data-top') === '1';
                    const esPromo = row.getAttribute('data-promo') === '1';
                    
                    console.log('Filtrando:', {
                        filterValue,
                        destacado,
                        esTop,
                        esPromo,
                        nombrePlato: row.querySelector('strong').textContent
                    });
                    
                    if (filterValue === 'top') {
                        return esTop;
                    }
                    if (filterValue === 'promo') {
                        return esPromo;
                    }
                    if (filterValue === 'normal') {
                        return !esTop && !esPromo;
                    }
                    
                    return true;
                }
            }
        ],
        onFilter: function(stats) {
            console.log('Stats:', stats);
            document.getElementById('platos-counter').textContent = `${stats.visible} de ${stats.total} platos`;
        }
    });
    
    const stats = platoFilter.getStats();
    document.getElementById('platos-counter').textContent = `${stats.total} platos registrados`;
    
    // Debug: Mostrar valores de data-attributes
    console.log('Platos cargados:');
    document.querySelectorAll('#tablaPlatos tbody tr').forEach((row, index) => {
        console.log(`Plato ${index + 1}:`, {
            nombre: row.querySelector('strong')?.textContent,
            estado: row.getAttribute('data-estado'),
            destacado: row.getAttribute('data-destacado'),
            top: row.getAttribute('data-top'),
            promo: row.getAttribute('data-promo')
        });
    });
});

function limpiarFiltros() {
    if (platoFilter) {
        platoFilter.clearFilters();
    }
}
</script>

<script>
async function toggleFlag(id, flag, valor) {
    const valorNumerico = valor === true || valor === 'true' ? 1 : 0;
    const fd = new FormData();
    fd.append('accion', 'toggle_flag');
    fd.append('id', id);
    fd.append('flag', flag);
    fd.append('valor', valorNumerico);

    try {
        const res = await fetch('api.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (!json.ok) {
            alert('Error al actualizar: ' + json.message);
        } else {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

async function toggleEstado(id, nuevoEstado, btn) {
    // Deshabilitar botón mientras se procesa
    btn.disabled = true;
    const iconOriginal = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    const fd = new FormData();
    fd.append('accion', 'toggle_estado');
    fd.append('id', id);
    fd.append('estado', nuevoEstado);

    try {
        const res = await fetch('api.php', { method: 'POST', body: fd });
        const json = await res.json();

        if (json.ok) {
            // Actualizar badge de estado
            const row = btn.closest('tr');
            const badge = row.querySelector('.badge');
            
            if (nuevoEstado === 'Disponible') {
                badge.className = 'badge badge-success';
                badge.textContent = 'Disponible';
                btn.style.background = '#dc3545';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                btn.onclick = function() { toggleEstado(id, 'Oculto', this); };
            } else {
                badge.className = 'badge badge-danger';
                badge.textContent = 'Oculto';
                btn.style.background = '#28a745';
                btn.innerHTML = '<i class="fas fa-eye"></i>';
                btn.onclick = function() { toggleEstado(id, 'Disponible', this); };
            }
            
            btn.disabled = false;
        } else {
            alert('Error: ' + json.message);
            btn.innerHTML = iconOriginal;
            btn.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
        btn.innerHTML = iconOriginal;
        btn.disabled = false;
    }
}
</script>
</body>
</html>
