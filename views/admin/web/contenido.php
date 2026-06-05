<?php
// views/admin/web/contenido.php — Configuración “cards” del contenido principal (Top / Promo / Menú)
session_start();

require_once __DIR__ . '/../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// CSRF básico (por seguridad mínima de UI; el API reutilizado no exige token, pero lo evitamos en POST por form normal)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/*
 * Cargar platos (con categoría y estado/flags/orden)
 * Mantener el mismo modelo/joins que api/menu.php para evitar errores de esquema:
 * - Plato JOIN Categoria JOIN Plato_Variante
 * - Filtrado base: pl.Estado='Disponible' AND pv.Estado=1 AND cat.Tipo='Plato'
 * - Sin aplicar filtro top/promo aquí: se muestran y se administran por Es_Top/Es_Promo.
 */
$platos = $db->fetchAll(
    "SELECT
        pl.PlatoID      AS PlatoID,
        pl.Nombre       AS Nombre,
        pl.Descripcion  AS Descripcion,
        pl.Imagen_URL   AS Imagen_URL,
        pl.Estado       AS Estado,
        pl.Orden        AS Orden,
        pl.Es_Top       AS Es_Top,
        pl.Es_Promo     AS Es_Promo,
        cat.Nombre      AS Categoria
     FROM Plato pl
     JOIN Categoria cat ON pl.CatID = cat.CatID
     JOIN Plato_Variante pv ON pl.PlatoID = pv.PlatoID
     WHERE pl.Estado = 'Disponible'
       AND pv.Estado = 1
       AND cat.Tipo = 'Plato'
     GROUP BY
        pl.PlatoID, pl.Nombre, pl.Descripcion, pl.Imagen_URL,
        pl.Estado, pl.Orden, pl.Es_Top, pl.Es_Promo,
        cat.Nombre
     ORDER BY pl.Orden ASC, pl.Nombre ASC"
);

// Helper para iconos visuales
function yesno($v) {
    return ((int)$v === 1) ? 1 : 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contenido Web — Admin Chifa Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--admin-border);
            padding-bottom: 12px;
        }
        .tab-btn{
            padding: 12px 24px;
            border: none;
            background: transparent;
            color: var(--admin-text-light);
            font-weight: 700;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: var(--admin-transition);
            border-radius: 0;
        }
        .tab-btn:hover { color: var(--admin-text); background: rgba(14, 1, 1, 0.05); }
        .tab-btn.active { color: var(--admin-primary); border-bottom-color: var(--admin-primary); background: rgba(14, 1, 1, 0.05); }

        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
        }

        .row-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .plato-img {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 10px;
            background: #eee;
            border: 1px solid rgba(225, 218, 218, 0.4);
        }

        .toggle-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            font-weight: 700;
            color: var(--admin-text);
        }

        .toggle-pill input { width: 18px; height: 18px; }

        .pill-danger { color: #c0392b; }
        .pill-success { color: #27ae60; }

        .muted { color: var(--admin-text-light); font-weight: 600; }

        @media (max-width: 768px) {
            .tab-btn { width: 100%; }
        }
    </style>
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
            <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Administración WEB</small>
        </div>
        <nav class="admin-nav" style="padding: 25px 0;">
            <a href="../dashboard.php" class="nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <div class="nav-section">
                <div class="nav-section-title">ADMINISTRACIÓN WEB</div>
                <a href="usuarios.php" class="nav-item">
                    <i class="fas fa-users-cog"></i> Usuarios Sistema
                </a>
                <a href="contenido.php" class="nav-item active">
                    <i class="fas fa-globe"></i> Contenido Web
                </a>
                <a href="pedidos-online.php" class="nav-item">
                    <i class="fas fa-laptop"></i> Pedidos Online
                </a>
            </div>
            <hr style="margin: 25px 20px; border: none; border-top: 1px solid var(--admin-border);">
            <a href="../../../index.php" class="nav-item">
                <i class="fas fa-home"></i> Volver al Sitio
            </a>
        </nav>
    </div>

    <main class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-globe"></i> Contenido Web (Cards)</h1>
            <p>Configura Top Ventas, Promociones y Menú visible en la página principal.</p>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom: 25px;">
            <div class="muted">
                Consejo: para crear/editar platillos y variantes (precios), usa <b>“Editar Plato”</b> o <b>“Nuevo Plato”</b>.
            </div>
            <div class="row-actions">
                <a href="../menu/form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Plato
                </a>
                <button class="btn btn-outline" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Recargar
                </button>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="tabTop" onclick="switchTab('tabTop')">
                <i class="fas fa-star"></i> Top Ventas
            </button>
            <button class="tab-btn" data-tab="tabPromo" onclick="switchTab('tabPromo')">
                <i class="fas fa-tags"></i> Promociones
            </button>
            <button class="tab-btn" data-tab="tabMenu" onclick="switchTab('tabMenu')">
                <i class="fas fa-utensils"></i> Menú
            </button>
        </div>

        <div class="grid">
            <!-- TOP -->
            <section id="tabTop" class="tab-pane">
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Plato</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Top Ventas</th>
                                <th>Promo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($platos as $p): ?>
                            <?php if (!yesno($p['Es_Top'])) continue; ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img class="plato-img"
                                             src="../../../<?= htmlspecialchars($p['Imagen_URL'] ?: 'assets/img/platos/default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                             onerror="this.src='../../../assets/img/platos/default.jpg'">
                                        <div>
                                            <strong><?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="muted">Orden: <?= (int)$p['Orden'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($p['Categoria'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= ($p['Estado'] ?? '') === 'Disponible' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= htmlspecialchars($p['Estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-pill <?= (int)$p['Es_Top'] === 1 ? 'pill-success' : 'pill-danger' ?>">
                                        <input type="checkbox"
                                               onchange="toggleTopPromo('top', <?= (int)$p['PlatoID'] ?>, this.checked)"
                                               <?= (int)$p['Es_Top'] === 1 ? 'checked' : '' ?>>
                                        <span>Activado</span>
                                    </label>
                                </td>
                                <td>
                                    <span class="badge <?= yesno($p['Es_Promo']) ? 'badge-info' : 'badge-secondary' ?>">
                                        <?= yesno($p['Es_Promo']) ? 'Sí' : 'No' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="btn btn-sm" style="background:#17a2b8; color:white; padding:5px 10px;" href="../menu/form.php?id=<?= (int)$p['PlatoID'] ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <button class="btn btn-sm" style="padding:5px 10px; background: <?= ($p['Estado'] ?? '') === 'Disponible' ? '#dc3545' : '#28a745' ?>; color:white;"
                                                onclick="toggleEstado(<?= (int)$p['PlatoID'] ?>, '<?= ($p['Estado'] ?? '') === 'Disponible' ? 'Oculto' : 'Disponible' ?>')">
                                            <i class="fas <?= ($p['Estado'] ?? '') === 'Disponible' ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty(array_filter($platos, fn($x) => yesno($x['Es_Top'])))): ?>
                            <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--admin-text-light);">
                                <i class="fas fa-star" style="font-size:2rem; opacity:0.4; margin-bottom:10px;"></i>
                                <div>No hay platos marcados como Top.</div>
                            </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- PROMO -->
            <section id="tabPromo" class="tab-pane" style="display:none;">
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Plato</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Top Ventas</th>
                                <th>Promo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($platos as $p): ?>
                            <?php if (!yesno($p['Es_Promo'])) continue; ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img class="plato-img"
                                             src="../../../<?= htmlspecialchars($p['Imagen_URL'] ?: 'assets/img/platos/default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                             onerror="this.src='../../../assets/img/platos/default.jpg'">
                                        <div>
                                            <strong><?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <div class="muted">Orden: <?= (int)$p['Orden'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($p['Categoria'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= ($p['Estado'] ?? '') === 'Disponible' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= htmlspecialchars($p['Estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= yesno($p['Es_Top']) ? 'badge-info' : 'badge-secondary' ?>">
                                        <?= yesno($p['Es_Top']) ? 'Sí' : 'No' ?>
                                    </span>
                                </td>
                                <td>
                                    <label class="toggle-pill <?= yesno($p['Es_Promo']) ? 'pill-success' : 'pill-danger' ?>">
                                        <input type="checkbox"
                                               onchange="toggleTopPromo('promo', <?= (int)$p['PlatoID'] ?>, this.checked)"
                                               <?= yesno($p['Es_Promo']) ? 'checked' : '' ?>>
                                        <span>Activado</span>
                                    </label>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="btn btn-sm" style="background:#17a2b8; color:white; padding:5px 10px;" href="../menu/form.php?id=<?= (int)$p['PlatoID'] ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <button class="btn btn-sm" style="padding:5px 10px; background: <?= ($p['Estado'] ?? '') === 'Disponible' ? '#dc3545' : '#28a745' ?>; color:white;"
                                                onclick="toggleEstado(<?= (int)$p['PlatoID'] ?>, '<?= ($p['Estado'] ?? '') === 'Disponible' ? 'Oculto' : 'Disponible' ?>')">
                                            <i class="fas <?= ($p['Estado'] ?? '') === 'Disponible' ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty(array_filter($platos, fn($x) => yesno($x['Es_Promo'])))): ?>
                            <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--admin-text-light);">
                                <i class="fas fa-tags" style="font-size:2rem; opacity:0.4; margin-bottom:10px;"></i>
                                <div>No hay platos marcados como Promo.</div>
                            </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- MENÚ (todos disponibles) -->
            <section id="tabMenu" class="tab-pane" style="display:none;">
                <div style="margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap;">
                    <div class="muted">
                        Menú visible en la web = <b>Estado Disponible</b> + variantes activas.
                    </div>
                    <div>
                        <button class="btn btn-outline" onclick="toggleAllAvailable()">
                            <i class="fas fa-toggle-on"></i> Marcar visibles (Disponible)
                        </button>
                    </div>
                </div>

                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Plato</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Orden</th>
                                <th>Top</th>
                                <th>Promo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($platos as $p): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img class="plato-img"
                                             src="../../../<?= htmlspecialchars($p['Imagen_URL'] ?: 'assets/img/platos/default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                             onerror="this.src='../../../assets/img/platos/default.jpg'">
                                        <div><strong><?= htmlspecialchars($p['Nombre'], ENT_QUOTES, 'UTF-8') ?></strong></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($p['Categoria'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= ($p['Estado'] ?? '') === 'Disponible' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= htmlspecialchars($p['Estado'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="muted"><?= (int)$p['Orden'] ?></td>
                                <td>
                                    <label class="toggle-pill">
                                        <input type="checkbox"
                                               onchange="toggleTopPromo('top', <?= (int)$p['PlatoID'] ?>, this.checked)"
                                               <?= yesno($p['Es_Top']) ? 'checked' : '' ?>>
                                        <span class="<?= yesno($p['Es_Top']) ? 'pill-success' : 'pill-danger' ?>">
                                            <?= yesno($p['Es_Top']) ? 'Sí' : 'No' ?>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <label class="toggle-pill">
                                        <input type="checkbox"
                                               onchange="toggleTopPromo('promo', <?= (int)$p['PlatoID'] ?>, this.checked)"
                                               <?= yesno($p['Es_Promo']) ? 'checked' : '' ?>>
                                        <span class="<?= yesno($p['Es_Promo']) ? 'pill-success' : 'pill-danger' ?>">
                                            <?= yesno($p['Es_Promo']) ? 'Sí' : 'No' ?>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="btn btn-sm" style="background:#17a2b8; color:white; padding:5px 10px;" href="../menu/form.php?id=<?= (int)$p['PlatoID'] ?>">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <button class="btn btn-sm" style="padding:5px 10px; background: <?= ($p['Estado'] ?? '') === 'Disponible' ? '#dc3545' : '#28a745' ?>; color:white;"
                                                onclick="toggleEstado(<?= (int)$p['PlatoID'] ?>, '<?= ($p['Estado'] ?? '') === 'Disponible' ? 'Oculto' : 'Disponible' ?>')">
                                            <i class="fas <?= ($p['Estado'] ?? '') === 'Disponible' ? 'eye-slash' : 'eye' ?>"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
        document.getElementById(tabId).style.display = 'block';

        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        const activeBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        if (activeBtn) activeBtn.classList.add('active');
    }

    async function toggleTopPromo(flag, id, checked) {
        const fd = new FormData();
        fd.append('accion', 'toggle_flag');
        fd.append('id', id);
        fd.append('flag', flag);
        fd.append('valor', checked ? 1 : 0);

        try {
            const res = await fetch('../menu/api.php', { method: 'POST', body: fd });
            const json = await res.json();
            if (!json.ok) alert('Error: ' + (json.message || 'No se pudo actualizar'));
            else location.reload();
        } catch (e) {
            alert('Error de conexión');
        }
    }

    async function toggleEstado(id, nuevoEstado) {
        const fd = new FormData();
        fd.append('accion', 'toggle_estado');
        fd.append('id', id);
        fd.append('estado', nuevoEstado);

        try {
            const res = await fetch('../menu/api.php', { method: 'POST', body: fd });
            const json = await res.json();
            if (!json.ok) alert('Error: ' + (json.message || 'No se pudo actualizar'));
            else location.reload();
        } catch (e) {
            alert('Error de conexión');
        }
    }

    async function toggleAllAvailable() {
        // Acción rápida: marcar todos los platos a Disponible reutilizando lógica existente por lote NO está implementada en API.
        // Para no romper, solo recarga indicando que use Editar Plato.
        alert('Acción rápida no disponible para lote en este módulo. Usa “Editar” en cada plato para ajustar Estado/Orden/Variantes.');
    }
</script>
</body>
</html>
