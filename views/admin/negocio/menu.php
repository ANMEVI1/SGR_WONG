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

// Obtener estadísticas del menú
$stats = $db->fetchOne(
    "SELECT 
        COUNT(*) as total_platos,
        SUM(CASE WHEN Estado = 'Disponible' THEN 1 ELSE 0 END) as disponibles,
        SUM(CASE WHEN Estado = 'Oculto' THEN 1 ELSE 0 END) as ocultos
     FROM Plato"
);

// Obtener categorías
$categorias = $db->fetchAll(
    "SELECT CatID, Nombre, 
        (SELECT COUNT(*) FROM Plato WHERE CatID = c.CatID) as total_platos
     FROM Categoria c WHERE Tipo = 'Plato' ORDER BY Nombre"
);

// Obtener platos con información completa
$platos = $db->fetchAll(
    "SELECT p.PlatoID, p.Nombre, p.Descripcion, p.Imagen_URL, p.Estado, p.Orden,
            c.Nombre as Categoria,
            MIN(pv.Precio_Venta) as precio_min,
            MAX(pv.Precio_Venta) as precio_max,
            COUNT(pv.VarianteID) as total_variantes
     FROM Plato p
     LEFT JOIN Categoria c ON p.CatID = c.CatID
     LEFT JOIN Plato_Variante pv ON p.PlatoID = pv.PlatoID AND pv.Estado = 1
     GROUP BY p.PlatoID
     ORDER BY p.Orden ASC, p.Nombre ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Menú — Chifa Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div style="padding: 25px; border-bottom: 2px solid var(--admin-border);">
                <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-utensils" style="color: var(--admin-primary); font-size: 1.5rem;"></i>
                    Chifa Matsue
                </h3>
                <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Gestión del Negocio</small>
            </div>
            <nav class="admin-nav" style="padding: 25px 0;">
                <a href="../dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <div class="nav-section">
                    <div class="nav-section-title">GESTIÓN DEL NEGOCIO</div>
                    <a href="menu.php" class="nav-item active">
                        <i class="fas fa-utensils"></i> Menú
                    </a>
                    <a href="empleados.php" class="nav-item">
                        <i class="fas fa-user-tie"></i> Empleados
                    </a>
                    <a href="reportes.php" class="nav-item">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                    <a href="configuracion.php" class="nav-item">
                        <i class="fas fa-cogs"></i> Configuración
                    </a>
                </div>
            </nav>
        </div>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-utensils"></i> Gestión de Menú</h1>
                <p>Administra la carta, precios y disponibilidad de platos</p>
            </div>

            <!-- Estadísticas del Menú -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Platos</div>
                    <div class="metric-value"><?= $stats['total_platos'] ?></div>
                    <small>En el menú</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Disponibles</div>
                    <div class="metric-value"><?= $stats['disponibles'] ?></div>
                    <small>Activos en web</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Ocultos</div>
                    <div class="metric-value"><?= $stats['ocultos'] ?></div>
                    <small>No visibles</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Categorías</div>
                    <div class="metric-value"><?= count($categorias) ?></div>
                    <small>Secciones</small>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="nuevoPlato()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Plato
                    </button>
                    <button onclick="nuevaCategoria()" class="btn btn-outline">
                        <i class="fas fa-tags"></i> Nueva Categoría
                    </button>
                    <button onclick="importarMenu()" class="btn btn-outline">
                        <i class="fas fa-upload"></i> Importar
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroCategoria" class="form-control" style="width: 200px;">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['CatID'] ?>"><?= htmlspecialchars($cat['Nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="buscarPlato" placeholder="Buscar platos..." class="form-control" style="width: 250px;">
                </div>
            </div>

            <!-- Lista de Platos -->
            <div class="admin-table">
                <table id="tablaPlatos">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Imagen</th>
                            <th>Plato</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Variantes</th>
                            <th>Estado</th>
                            <th>Orden</th>
                            <th style="width: 120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($platos as $plato): ?>
                        <?php
                        $imgSrc = !empty($plato['Imagen_URL']) ? '../../../' . htmlspecialchars($plato['Imagen_URL']) : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Crect fill='%23f0ece4' width='50' height='50'/%3E%3Ctext fill='%23c9954a' font-size='12' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3E?%3C/text%3E%3C/svg%3E";
                        ?>
                        <tr data-categoria="<?= $plato['CatID'] ?? '' ?>">
                            <td>
                                <img src="<?= $imgSrc ?>"
                                     alt="<?= htmlspecialchars($plato['Nombre']) ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                     onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2750%27 height=%2750%27%3E%3Crect fill=%27%23f0ece4%27 width=%2750%27 height=%2750%27/%3E%3Ctext fill=%27%23c9954a%27 font-size=%2712%27 font-weight=%27bold%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dominant-baseline=%27middle%27%3E?%3C/text%3E%3C/svg%3E';">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($plato['Nombre']) ?></strong>
                                <br>
                                <small style="color: #6c757d;"><?= htmlspecialchars(substr($plato['Descripcion'], 0, 50)) ?>...</small>
                            </td>
                            <td><?= htmlspecialchars($plato['Categoria'] ?? 'Sin categoría') ?></td>
                            <td>
                                <?php if ($plato['precio_min'] && $plato['precio_max']): ?>
                                    <?php if ($plato['precio_min'] == $plato['precio_max']): ?>
                                        S/ <?= number_format($plato['precio_min'], 2) ?>
                                    <?php else: ?>
                                        S/ <?= number_format($plato['precio_min'], 2) ?> - <?= number_format($plato['precio_max'], 2) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #dc3545;">Sin precio</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= $plato['total_variantes'] ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $plato['Estado'] === 'Disponible' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $plato['Estado'] ?>
                                </span>
                            </td>
                            <td><?= $plato['Orden'] ?></td>
                            <td>
                                <button onclick="editarPlato(<?= $plato['PlatoID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleEstado(<?= $plato['PlatoID'] ?>, '<?= $plato['Estado'] ?>')" 
                                        class="btn btn-sm" style="background: <?= $plato['Estado'] === 'Disponible' ? '#dc3545' : '#28a745' ?>; color: white;">
                                    <i class="fas fa-<?= $plato['Estado'] === 'Disponible' ? 'eye-slash' : 'eye' ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Filtros
        document.getElementById('filtroCategoria').addEventListener('change', filtrarTabla);
        document.getElementById('buscarPlato').addEventListener('input', filtrarTabla);

        function filtrarTabla() {
            const categoria = document.getElementById('filtroCategoria').value;
            const busqueda = document.getElementById('buscarPlato').value.toLowerCase();
            const filas = document.querySelectorAll('#tablaPlatos tbody tr');

            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                const categoriaFila = fila.getAttribute('data-categoria');
                
                const coincideBusqueda = textoFila.includes(busqueda);
                const coincideCategoria = !categoria || categoriaFila === categoria;
                
                fila.style.display = (coincideBusqueda && coincideCategoria) ? '' : 'none';
            });
        }

        function nuevoPlato() {
            window.location.href = 'plato-form.php';
        }

        function editarPlato(id) {
            window.location.href = `plato-form.php?id=${id}`;
        }

        async function toggleEstado(id, estadoActual) {
            const nuevoEstado = estadoActual === 'Disponible' ? 'Oculto' : 'Disponible';
            
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_estado');
                formData.append('plato_id', id);
                formData.append('estado', nuevoEstado);

                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error de conexión');
            }
        }

        function nuevaCategoria() {
            const nombre = prompt('Nombre de la nueva categoría:');
            if (nombre) {
                // Implementar creación de categoría
                alert('Función en desarrollo');
            }
        }

        function importarMenu() {
            alert('Función de importación en desarrollo');
        }
    </script>
</body>
</html>