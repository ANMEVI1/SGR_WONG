<?php
// views/admin/menu/form.php — Crear / Editar plato
session_start();

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_scope'] ?? '') !== 'backoffice') {
    header('Location: ../../../login.php');
    exit;
}

require_once __DIR__ . '/../../../config/conexion.php';

$platoID  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$esEditar = $platoID > 0;
$plato    = null;
$variantes = [];

if ($esEditar) {
    $stmt = $conexion->prepare(
        "SELECT pl.*, cat.Nombre AS Categoria
        FROM Plato pl JOIN Categoria cat ON pl.CatID = cat.CatID
        WHERE pl.PlatoID = ? LIMIT 1"
    );
    $stmt->bind_param('i', $platoID);
    $stmt->execute();
    $plato = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$plato) { header('Location: index.php'); exit; }

    $stmt = $conexion->prepare("SELECT * FROM Plato_Variante WHERE PlatoID = ? ORDER BY Precio_Venta ASC");
    $stmt->bind_param('i', $platoID);
    $stmt->execute();
    $variantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Categorías disponibles
$categorias = $conexion->query("SELECT CatID, Nombre FROM Categoria WHERE Tipo = 'Plato' ORDER BY Nombre")->fetch_all(MYSQLI_ASSOC);
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
                             alt="preview" onerror="this.src='../../../assets/img/platos/default.jpg'">
                    <?php else: ?>
                        <img class="preview-img" id="imgPreview"
                             src="../../../assets/img/platos/default.jpg" alt="preview">
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
