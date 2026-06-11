<?php
session_start();
require_once __DIR__ . '/../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$db = getDB();

$stats = [
    'total_platos' => $db->fetchOne("SELECT COUNT(*) as c FROM Plato WHERE Estado = 'Disponible'")['c'],
    'top_ventas' => $db->fetchOne("SELECT COUNT(*) as c FROM Plato WHERE Estado = 'Disponible' AND Es_Top = 1")['c'],
    'promociones' => $db->fetchOne("SELECT COUNT(*) as c FROM Plato WHERE Estado = 'Disponible' AND Es_Promo = 1")['c']
];

$platosTop = $db->fetchAll("SELECT * FROM Plato WHERE Estado = 'Disponible' AND Es_Top = 1 ORDER BY Orden, Nombre");
$platosPromo = $db->fetchAll("SELECT * FROM Plato WHERE Estado = 'Disponible' AND Es_Promo = 1 ORDER BY Orden, Nombre");
$todoPlatos = $db->fetchAll("SELECT * FROM Plato WHERE Estado = 'Disponible' ORDER BY Orden, Nombre");
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
        .plato-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; background: #f8f9fa; border: 1px solid #ddd; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid #ddd; }
        .tab-btn { padding: 14px 28px; border: none; background: transparent; color: #666; font-weight: 600; cursor: pointer; border-bottom: 3px solid transparent; transition: 0.2s; }
        .tab-btn.active { color: #007bff; border-bottom-color: #007bff; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .toggle-switch { position: relative; display: inline-block; width: 50px; height: 24px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.3s; border-radius: 24px; }
        .toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
        input:checked + .toggle-slider { background-color: #28a745; }
        input:checked + .toggle-slider:before { transform: translateX(26px); }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include '../components/sidebar.php'; ?>

    <main class="admin-content">
        <div class="admin-header">
            <h1><i class="fas fa-globe"></i> Contenido Web</h1>
            <p>Gestiona platos destacados y menú del día</p>
        </div>

        <div class="dashboard-grid" style="margin-bottom: 30px; grid-template-columns: repeat(3, 1fr);">
            <div class="metric-card success">
                <div class="metric-label">Total Platos</div>
                <div class="metric-value"><?= $stats['total_platos'] ?></div>
            </div>
            <div class="metric-card warning">
                <div class="metric-label">Top Ventas</div>
                <div class="metric-value"><?= $stats['top_ventas'] ?></div>
            </div>
            <div class="metric-card info">
                <div class="metric-label">Promociones</div>
                <div class="metric-value"><?= $stats['promociones'] ?></div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('top')"><i class="fas fa-star"></i> Top Ventas</button>
            <button class="tab-btn" onclick="switchTab('promo')"><i class="fas fa-tags"></i> Promociones</button>
            <button class="tab-btn" onclick="switchTab('todos')"><i class="fas fa-list"></i> Todos</button>
        </div>

        <!-- TOP VENTAS -->
        <div id="top" class="tab-pane active">
            <div class="admin-table">
                <table>
                    <thead><tr><th>Plato</th><th>Top</th><th>Promo</th><th>Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($platosTop as $p): ?>
                        <?php include 'render_fila.php'; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PROMOCIONES -->
        <div id="promo" class="tab-pane">
            <div class="admin-table">
                <table>
                    <thead><tr><th>Plato</th><th>Top</th><th>Promo</th><th>Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($platosPromo as $p): ?>
                        <?php include 'render_fila.php'; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TODOS -->
        <div id="todos" class="tab-pane">
            <div class="admin-table">
                <table>
                    <thead><tr><th>Plato</th><th>Top</th><th>Promo</th><th>Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($todoPlatos as $p): ?>
                        <?php include 'render_fila.php'; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
function switchTab(id) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}

async function toggleFlag(flag, id, checked) {
    const fd = new FormData();
    fd.append('accion', 'toggle_flag');
    fd.append('id', id);
    fd.append('flag', flag);
    fd.append('valor', checked ? 1 : 0);
    
    const res = await fetch('../menu/api.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) alert('Error: ' + json.message);
}
</script>
</body>
</html>
