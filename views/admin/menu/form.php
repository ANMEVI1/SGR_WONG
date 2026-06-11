<?php
// views/admin/menu/form.php — Crear / Editar plato
session_start();

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_scope'] ?? '') !== 'backoffice') {
    header('Location: ../../../login.php');
    exit;
}

require_once __DIR__ . '/../../../config/conexion.php';

$db = getDB();
$platoID  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$esEditar = $platoID > 0;
$plato    = null;
$variantes = [];

if ($esEditar) {
    $plato = $db->fetchOne(
        "SELECT pl.*, cat.Nombre AS Categoria
        FROM Plato pl JOIN Categoria cat ON pl.CatID = cat.CatID
        WHERE pl.PlatoID = :id LIMIT 1",
        [':id' => $platoID]
    );

    if (!$plato) { header('Location: index.php'); exit; }

    $variantes = $db->fetchAll(
        "SELECT * FROM Plato_Variante WHERE PlatoID = :id AND Estado = 1 ORDER BY Precio_Venta ASC",
        [':id' => $platoID]
    );
    
    $variantesInactivas = $db->fetchAll(
        "SELECT * FROM Plato_Variante WHERE PlatoID = :id AND Estado = 0 ORDER BY Precio_Venta ASC",
        [':id' => $platoID]
    );
}

// Categorías disponibles
$categorias = $db->fetchAll(
    "SELECT CatID, Nombre FROM Categoria WHERE Tipo = 'Plato' ORDER BY Nombre"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $esEditar ? 'Editar' : 'Nuevo' ?> Plato — Admin Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: .85rem; font-weight: 600; color: var(--admin-text); }
        input, select, textarea {
            padding: 10px 12px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius);
            font-size: .9rem; width: 100%; transition: border-color .2s; font-family: inherit;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--admin-primary); }
        textarea { resize: vertical; min-height: 120px; }
        .section-sep { grid-column: 1/-1; border: none; border-top: 1px solid var(--admin-border); margin: 12px 0; }
        .variante-row { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; }
        .variante-row input { flex: 1; }
        .btn-icon { background: none; border: none; cursor: pointer; color: var(--admin-primary); font-size: 1rem; padding: 4px; transition: color .2s; }
        .btn-icon:hover { color: #e74c3c; }
        .preview-img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 2px solid var(--admin-border); margin-top: 8px; }
        .checkbox-group { display: flex; align-items: center; gap: 12px; }
        .checkbox-group label { display: flex; align-items: center; gap: 6px; cursor: pointer; margin: 0; }
        .checkbox-group input[type="checkbox"] { width: auto; margin: 0; }
    </style>
</head>
<body>
<div class="admin-container">
    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>

    <main class="admin-content">
        <div class="card">
            <h1><?= $esEditar ? 'Editar plato' : 'Nuevo plato' ?></h1>

            <form class="form-grid" id="platoForm" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="<?= $esEditar ? 'editar' : 'crear' ?>">
                <?php if ($esEditar): ?>
                    <input type="hidden" name="id" value="<?= $platoID ?>">
                <?php endif; ?>

                <div class="field full">
                    <label>Nombre del plato *</label>
                    <input type="text" name="nombre" required
                           value="<?= htmlspecialchars($plato['Nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="field full">
                    <label>Descripción</label>
                    <textarea name="descripcion"><?= htmlspecialchars($plato['Descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="field">
                    <label>Categoría *</label>
                    <select name="cat_id" required>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['CatID'] ?>"
                                <?= ($plato['CatID'] ?? '') == $cat['CatID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['Nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Orden de aparición</label>
                    <input type="number" name="orden" min="0"
                           value="<?= (int)($plato['Orden'] ?? 0) ?>">
                </div>

                <div class="field">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="Disponible" <?= ($plato['Estado'] ?? '') === 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="Oculto"     <?= ($plato['Estado'] ?? '') === 'Oculto'     ? 'selected' : '' ?>>Oculto</option>
                    </select>
                </div>

                <div class="field" style="flex-direction:row;align-items:center;gap:16px;">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" name="es_top" value="1" style="width:auto;"
                               <?= !empty($plato['Es_Top']) ? 'checked' : '' ?>>
                        Top Ventas
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" name="es_promo" value="1" style="width:auto;"
                               <?= !empty($plato['Es_Promo']) ? 'checked' : '' ?>>
                        Promoción
                    </label>
                </div>



                <hr class="section-sep">

                <div class="field full">
                    <label>Imagen del plato</label>
                    <?php if (!empty($plato['Imagen_URL'])): ?>
                        <img class="preview-img" id="imgPreview"
                             src="../../../<?= htmlspecialchars($plato['Imagen_URL'], ENT_QUOTES, 'UTF-8') ?>" 
                             alt="preview" onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Crect fill=%27%23f0ece4%27 width=%27100%27 height=%27100%27/%3E%3Ctext fill=%27%23c9954a%27 font-size=%2714%27 font-weight=%27bold%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dominant-baseline=%27middle%27%3E?%3C/text%3E%3C/svg%3E';">
                    <?php else: ?>
                        <img class="preview-img" id="imgPreview"
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Crect fill='%23f0ece4' width='100' height='100'/%3E%3Ctext fill='%23c9954a' font-size='14' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3E?%3C/text%3E%3C/svg%3E" alt="preview">
                    <?php endif; ?>
                    <input type="file" name="imagen" accept="image/*" id="inputImagen" style="margin-top:8px;">
                    <small style="color:#999;margin-top:4px;">JPG/PNG/WEBP. Máx 2MB. Deja vacío para mantener la actual.</small>
                </div>

                <hr class="section-sep">

                <!-- Variantes de precio -->
                <div class="variantes-list">
                    <label style="display:block;margin-bottom:10px;">Variantes y precios *</label>
                    <div id="variantesContainer">
                        <?php if (!empty($variantes)): ?>
                            <?php foreach ($variantes as $v): ?>
                            <div class="variante-row">
                                <input type="hidden" name="variante_id[]" value="<?= (int)$v['VarianteID'] ?>">
                                <input type="text"   name="variante_nombre[]" placeholder="Ej: Personal"
                                       value="<?= htmlspecialchars($v['Nombre'], ENT_QUOTES, 'UTF-8') ?>" required>
                                <input type="number" name="variante_precio[]" placeholder="Precio S/"
                                       step="0.01" min="0"
                                       value="<?= number_format((float)$v['Precio_Venta'], 2) ?>" required>
                                <button type="button" class="btn-icon" onclick="quitarVariante(this)" title="Eliminar variante">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="variante-row">
                                <input type="hidden" name="variante_id[]" value="">
                                <input type="text"   name="variante_nombre[]" placeholder="Ej: Personal" required>
                                <input type="number" name="variante_precio[]" placeholder="Precio S/" step="0.01" min="0" required>
                                <button type="button" class="btn-icon" onclick="quitarVariante(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-ghost" style="margin-top:8px;" onclick="agregarVariante()">
                        <i class="fas fa-plus"></i> Agregar variante
                    </button>
                    <?php if (!empty($variantesInactivas)): ?>
                        <button type="button" class="btn btn-ghost" style="margin-top:8px;background:#f0f0f0;" onclick="mostrarInactivas()">
                            <i class="fas fa-redo"></i> Reactivar (<?= count($variantesInactivas) ?>)
                        </button>
                    <?php endif; ?>
                </div>

                <div class="actions full">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $esEditar ? 'Guardar cambios' : 'Crear plato' ?>
                    </button>
                    <a href="index.php" class="btn btn-ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Preview de imagen
document.getElementById('inputImagen').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('imgPreview').src = e.target.result;
        reader.readAsDataURL(file);
    }
});

function agregarVariante() {
    const row = document.createElement('div');
    row.className = 'variante-row';
    row.innerHTML = `
        <input type="hidden" name="variante_id[]" value="">
        <input type="text"   name="variante_nombre[]" placeholder="Ej: Para dos" required>
        <input type="number" name="variante_precio[]" placeholder="Precio S/" step="0.01" min="0" required>
        <button type="button" class="btn-icon" onclick="quitarVariante(this)">
            <i class="fas fa-trash"></i>
        </button>`;
    document.getElementById('variantesContainer').appendChild(row);
}

function quitarVariante(btn) {
    const rows = document.querySelectorAll('.variante-row');
    if (rows.length <= 1) { alert('Debe haber al menos una variante.'); return; }
    btn.closest('.variante-row').remove();
}

function mostrarInactivas() {
    const inactivas = <?= json_encode($variantesInactivas ?? []) ?>;
    if (!inactivas || inactivas.length === 0) return;
    
    const msg = inactivas.map(v => `${v.Nombre} - S/ ${parseFloat(v.Precio_Venta).toFixed(2)}`).join('\n');
    if (!confirm(`Variantes desactivadas:\n\n${msg}\n\n¿Reactivar alguna?`)) return;
    
    inactivas.forEach(v => {
        const row = document.createElement('div');
        row.className = 'variante-row';
        row.innerHTML = `
            <input type="hidden" name="variante_id[]" value="${v.VarianteID}">
            <input type="text" name="variante_nombre[]" value="${v.Nombre.replace(/"/g, '&quot;')}" required>
            <input type="number" name="variante_precio[]" step="0.01" min="0" value="${parseFloat(v.Precio_Venta).toFixed(2)}" required>
            <button type="button" class="btn-icon" onclick="quitarVariante(this)"><i class="fas fa-trash"></i></button>`;
        document.getElementById('variantesContainer').appendChild(row);
    });
    
    alert('Variantes reactivadas. Guarda cambios para confirmar.');
    event.target.remove();
}

document.getElementById('platoForm').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = e.target.querySelector('[type=submit]');
    btn.disabled = true;

    const fd = new FormData(e.target);
    const res  = await fetch('api.php', { method: 'POST', body: fd });
    const json = await res.json();

    if (json.ok) {
        window.location.href = 'index.php?msg=' + encodeURIComponent(json.message);
    } else {
        alert('Error: ' + json.message);
        btn.disabled = false;
    }
});
</script>
</body>
</html>
