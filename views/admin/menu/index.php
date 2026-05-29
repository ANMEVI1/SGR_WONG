<?php
require_once __DIR__ . '/../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Cargar categorías para el filtro
$categorias = $db->fetchAll(
    "SELECT CatID, Nombre FROM categoria ORDER BY Nombre"
);

// Cargar platos con su categoría
$platos = $db->fetchAll(
    "SELECT pl.PlatoID, pl.Nombre, pl.Descripcion, pl.Imagen_URL,
            pl.Estado, pl.Orden, pl.CatID,
            cat.Nombre AS Categoria
     FROM plato pl
     LEFT JOIN categoria cat ON pl.CatID = cat.CatID
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
        <div class="admin-sidebar">
            <div style="padding: 25px; border-bottom: 2px solid var(--admin-border);">
                <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-utensils" style="color: var(--admin-primary); font-size: 1.5rem;"></i>
                    Chifa Matsue
                </h3>
                <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Panel de Administración</small>
            </div>
            <nav class="admin-nav" style="padding: 25px 0;">
                <a href="../dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <!-- PUNTO DE VENTA -->
                <div class="nav-section">
                    <div class="nav-section-title">PUNTO DE VENTA</div>
                    <a href="../pos/caja.php" class="nav-item">
                        <i class="fas fa-cash-register"></i> Caja
                    </a>
                    <a href="../pedidos/index.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                    </a>
                </div>
                
                <!-- GESTIÓN DEL NEGOCIO -->
                <div class="nav-section">
                    <div class="nav-section-title">GESTIÓN DEL NEGOCIO</div>
                    <a href="index.php" class="nav-item active">
                        <i class="fas fa-utensils"></i> Menú
                    </a>
                    <a href="../usuarios/index.php" class="nav-item">
                        <i class="fas fa-user-tie"></i> Empleados
                    </a>
                    <a href="../reportes/index.php" class="nav-item">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                </div>
                
                <!-- INVENTARIO -->
                <div class="nav-section">
                    <div class="nav-section-title">INVENTARIO</div>
                    <a href="../inventario/index.php" class="nav-item">
                        <i class="fas fa-boxes"></i> Stock
                    </a>
                </div>
                
                <!-- ADMINISTRACIÓN WEB -->
                <div class="nav-section">
                    <div class="nav-section-title">ADMINISTRACIÓN WEB</div>
                    <a href="../web/usuarios.php" class="nav-item">
                        <i class="fas fa-users-cog"></i> Usuarios Web
                    </a>
                </div>
                
                <hr style="margin: 25px 20px; border: none; border-top: 1px solid var(--admin-border);">
                <a href="../../../index.php" class="nav-item">
                    <i class="fas fa-home"></i> Volver al Sitio
                </a>
            </nav>
        </div>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-utensils"></i> Gestión de Menú</h1>
                <p>Administra los platos, categorías y promociones del restaurante</p>
            </div>

            <!-- Acciones Rápidas -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <a href="form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Plato
                    </a>
                    <button class="btn btn-outline" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
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
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio desde</th>
                            <th>Estado</th>
                            <th>Top</th>
                            <th>Promo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($platos as $p): ?>
                        <tr>
                            <td>
                                <img style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; background: #eee;"
                                     src="../../../<?= htmlspecialchars($p['Imagen_URL'] ?: 'assets/img/platos/default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                     onerror="this.src='../../../assets/img/platos/default.jpg'">
                            </td>
                            <td><strong><?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($p['Categoria'] ?? 'Sin categoría', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>S/ <span style="color: #6c757d;">Ver variantes</span></td>
                            <td>
                                <span class="badge <?= $p['Estado'] === 'Disponible' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= htmlspecialchars($p['Estado'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: #6c757d; font-size: 0.9em;">N/A</span>
                            </td>
                            <td>
                                <span style="color: #6c757d; font-size: 0.9em;">N/A</span>
                            </td>
                            <td>
                                <a href="form.php?id=<?= (int)$p['PlatoID'] ?>" class="btn btn-sm" 
                                   style="padding: 5px 10px; margin-right: 5px; background: #17a2b8; color: white;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm" 
                                        style="padding: 5px 10px; background: <?= $p['Estado'] === 'Disponible' ? '#dc3545' : '#28a745' ?>; color: white;"
                                        onclick="toggleEstado(<?= (int)$p['PlatoID'] ?>, '<?= $p['Estado'] === 'Disponible' ? 'Oculto' : 'Disponible' ?>', this)">
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

<script>
async function toggleFlag(id, flag, valor) {
    const fd = new FormData();
    fd.append('accion', 'toggle_flag');
    fd.append('id', id);
    fd.append('flag', flag);
    fd.append('valor', valor ? 1 : 0);

    const res  = await fetch('api.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) alert('Error al actualizar: ' + json.message);
}

async function toggleEstado(id, nuevoEstado, btn) {
    const fd = new FormData();
    fd.append('accion', 'toggle_estado');
    fd.append('id', id);
    fd.append('estado', nuevoEstado);

    const res  = await fetch('api.php', { method: 'POST', body: fd });
    const json = await res.json();

    if (json.ok) {
        location.reload();
    } else {
        alert('Error: ' + json.message);
    }
}
</script>
</body>
</html>
