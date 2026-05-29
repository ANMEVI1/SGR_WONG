<?php
// views/admin/menu/api.php — CRUD protegido del módulo de menú
session_start();
header('Content-Type: application/json; charset=utf-8');

// Guardia: solo backoffice
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_scope'] ?? '') !== 'backoffice') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Acceso denegado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
    exit;
}

require_once __DIR__ . '/../../../config/conexion.php';

$accion = $_POST['accion'] ?? '';

// ── Helpers ──────────────────────────────────────────────────

function ok(string $msg): void {
    echo json_encode(['ok' => true, 'message' => $msg]);
    exit;
}

function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'message' => $msg]);
    exit;
}

function subirImagen(): ?string {
    if (empty($_FILES['imagen']['tmp_name'])) return null;

    $file    = $_FILES['imagen'];
    $maxSize = 2 * 1024 * 1024; // 2 MB
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];

    if ($file['size'] > $maxSize)          fail('La imagen supera los 2 MB.');
    if (!in_array($file['type'], $allowed)) fail('Formato de imagen no permitido (JPG, PNG, WEBP).');

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre   = 'plato_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destDir  = __DIR__ . '/../../../assets/img/platos/';
    $destPath = $destDir . $nombre;

    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $destPath)) fail('Error al guardar la imagen.');

    return 'assets/img/platos/' . $nombre;
}

// ── Acciones ─────────────────────────────────────────────────

switch ($accion) {

    // ── Crear plato ──────────────────────────────────────────
    case 'crear':
        $nombre   = trim($_POST['nombre']      ?? '');
        $desc     = trim($_POST['descripcion'] ?? '');
        $catID    = (int)($_POST['cat_id']     ?? 0);
        $orden    = (int)($_POST['orden']      ?? 0);
        $estado   = $_POST['estado'] === 'Oculto' ? 'Oculto' : 'Disponible';
        $esTop    = isset($_POST['es_top'])   ? 1 : 0;
        $esPromo  = isset($_POST['es_promo']) ? 1 : 0;

        if ($nombre === '' || $catID === 0) fail('Nombre y categoría son obligatorios.');

        $imagenURL = subirImagen() ?? 'assets/img/platos/default.jpg';

        $stmt = $conexion->prepare(
            "INSERT INTO Plato (Nombre, Descripcion, Imagen_URL, Estado, Orden, Es_Top, Es_Promo, CatID)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssiiii', $nombre, $desc, $imagenURL, $estado, $orden, $esTop, $esPromo, $catID);
        if (!$stmt->execute()) fail('Error al crear el plato.');
        $platoID = $conexion->insert_id;
        $stmt->close();

        // Insertar variantes
        $nombres  = $_POST['variante_nombre'] ?? [];
        $precios  = $_POST['variante_precio'] ?? [];
        $stmtV = $conexion->prepare("INSERT INTO Plato_Variante (PlatoID, Nombre, Precio_Venta) VALUES (?, ?, ?)");
        foreach ($nombres as $i => $vNombre) {
            $vNombre = trim($vNombre);
            $vPrecio = (float)($precios[$i] ?? 0);
            if ($vNombre === '' || $vPrecio <= 0) continue;
            $stmtV->bind_param('isd', $platoID, $vNombre, $vPrecio);
            $stmtV->execute();
        }
        $stmtV->close();

        ok('Plato creado correctamente.');

    // ── Editar plato ─────────────────────────────────────────
    case 'editar':
        $id      = (int)($_POST['id']          ?? 0);
        $nombre  = trim($_POST['nombre']       ?? '');
        $desc    = trim($_POST['descripcion']  ?? '');
        $catID   = (int)($_POST['cat_id']      ?? 0);
        $orden   = (int)($_POST['orden']       ?? 0);
        $estado  = $_POST['estado'] === 'Oculto' ? 'Oculto' : 'Disponible';
        $esTop   = isset($_POST['es_top'])   ? 1 : 0;
        $esPromo = isset($_POST['es_promo']) ? 1 : 0;

        if ($id === 0 || $nombre === '' || $catID === 0) fail('Datos incompletos.');

        $imagenURL = subirImagen();

        if ($imagenURL) {
            $stmt = $conexion->prepare(
                "UPDATE Plato SET Nombre=?, Descripcion=?, Imagen_URL=?, Estado=?, Orden=?, Es_Top=?, Es_Promo=?, CatID=?
                 WHERE PlatoID=?"
            );
            $stmt->bind_param('ssssiiiii', $nombre, $desc, $imagenURL, $estado, $orden, $esTop, $esPromo, $catID, $id);
        } else {
            $stmt = $conexion->prepare(
                "UPDATE Plato SET Nombre=?, Descripcion=?, Estado=?, Orden=?, Es_Top=?, Es_Promo=?, CatID=?
                 WHERE PlatoID=?"
            );
            $stmt->bind_param('sssiiiii', $nombre, $desc, $estado, $orden, $esTop, $esPromo, $catID, $id);
        }
        if (!$stmt->execute()) fail('Error al actualizar el plato.');
        $stmt->close();

        // Sincronizar variantes
        $varIDs   = $_POST['variante_id']     ?? [];
        $varNoms  = $_POST['variante_nombre'] ?? [];
        $varPrecs = $_POST['variante_precio'] ?? [];

        foreach ($varNoms as $i => $vNombre) {
            $vNombre = trim($vNombre);
            $vPrecio = (float)($varPrecs[$i] ?? 0);
            $vID     = (int)($varIDs[$i] ?? 0);
            if ($vNombre === '' || $vPrecio <= 0) continue;

            if ($vID > 0) {
                $s = $conexion->prepare("UPDATE Plato_Variante SET Nombre=?, Precio_Venta=? WHERE VarianteID=? AND PlatoID=?");
                $s->bind_param('sdii', $vNombre, $vPrecio, $vID, $id);
            } else {
                $s = $conexion->prepare("INSERT INTO Plato_Variante (PlatoID, Nombre, Precio_Venta) VALUES (?, ?, ?)");
                $s->bind_param('isd', $id, $vNombre, $vPrecio);
            }
            $s->execute();
            $s->close();
        }

        ok('Plato actualizado correctamente.');

    // ── Cambiar estado (Disponible / Oculto) ─────────────────
    case 'toggle_estado':
        $id     = (int)($_POST['id']     ?? 0);
        $estado = $_POST['estado'] === 'Oculto' ? 'Oculto' : 'Disponible';
        if ($id === 0) fail('ID inválido.');

        $stmt = $conexion->prepare("UPDATE Plato SET Estado = ? WHERE PlatoID = ?");
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute();
        $stmt->close();
        ok('Estado actualizado.');

    // ── Cambiar flag Top / Promo ──────────────────────────────
    case 'toggle_flag':
        $id    = (int)($_POST['id']    ?? 0);
        $flag  = $_POST['flag']  ?? '';
        $valor = (int)($_POST['valor'] ?? 0);

        if ($id === 0 || !in_array($flag, ['top', 'promo'])) fail('Datos inválidos.');

        $col  = $flag === 'top' ? 'Es_Top' : 'Es_Promo';
        $stmt = $conexion->prepare("UPDATE Plato SET {$col} = ? WHERE PlatoID = ?");
        $stmt->bind_param('ii', $valor, $id);
        $stmt->execute();
        $stmt->close();
        ok('Flag actualizado.');

    default:
        fail('Acción no reconocida.');
}
