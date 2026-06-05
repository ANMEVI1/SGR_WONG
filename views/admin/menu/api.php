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
$db = getDB();

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

        try {
            $db->beginTransaction();
            
            $db->execute(
                "INSERT INTO Plato (Nombre, Descripcion, Imagen_URL, Estado, Orden, Es_Top, Es_Promo, CatID)
                 VALUES (:nombre, :desc, :imagen, :estado, :orden, :top, :promo, :catID)",
                [
                    ':nombre' => $nombre,
                    ':desc' => $desc,
                    ':imagen' => $imagenURL,
                    ':estado' => $estado,
                    ':orden' => $orden,
                    ':top' => $esTop,
                    ':promo' => $esPromo,
                    ':catID' => $catID
                ]
            );
            $platoID = (int)$db->lastInsertId();

            // Insertar variantes
            $nombres  = $_POST['variante_nombre'] ?? [];
            $precios  = $_POST['variante_precio'] ?? [];
            
            if (empty($nombres) || empty($precios)) {
                $db->rollback();
                fail('Debe agregar al menos una variante con precio.');
            }
            
            foreach ($nombres as $i => $vNombre) {
                $vNombre = trim($vNombre);
                $vPrecio = (float)($precios[$i] ?? 0);
                if ($vNombre === '' || $vPrecio <= 0) continue;
                
                $db->execute(
                    "INSERT INTO Plato_Variante (PlatoID, Nombre, Precio_Venta) VALUES (:platoID, :nombre, :precio)",
                    [':platoID' => $platoID, ':nombre' => $vNombre, ':precio' => $vPrecio]
                );
            }

            $db->commit();
            ok('Plato creado correctamente.');
            
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            error_log('Error crear plato: ' . $e->getMessage());
            fail('Error al crear el plato: ' . $e->getMessage());
        }

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

        try {
            if ($imagenURL) {
                $db->execute(
                    "UPDATE Plato SET Nombre=:nombre, Descripcion=:desc, Imagen_URL=:imagen, Estado=:estado, 
                     Orden=:orden, Es_Top=:top, Es_Promo=:promo, CatID=:catID WHERE PlatoID=:id",
                    [
                        ':nombre' => $nombre, ':desc' => $desc, ':imagen' => $imagenURL,
                        ':estado' => $estado, ':orden' => $orden, ':top' => $esTop,
                        ':promo' => $esPromo, ':catID' => $catID, ':id' => $id
                    ]
                );
            } else {
                $db->execute(
                    "UPDATE Plato SET Nombre=:nombre, Descripcion=:desc, Estado=:estado, 
                     Orden=:orden, Es_Top=:top, Es_Promo=:promo, CatID=:catID WHERE PlatoID=:id",
                    [
                        ':nombre' => $nombre, ':desc' => $desc, ':estado' => $estado,
                        ':orden' => $orden, ':top' => $esTop, ':promo' => $esPromo,
                        ':catID' => $catID, ':id' => $id
                    ]
                );
            }

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
                    $db->execute(
                        "UPDATE Plato_Variante SET Nombre=:nombre, Precio_Venta=:precio 
                         WHERE VarianteID=:vID AND PlatoID=:platoID",
                        [':nombre' => $vNombre, ':precio' => $vPrecio, ':vID' => $vID, ':platoID' => $id]
                    );
                } else {
                    $db->execute(
                        "INSERT INTO Plato_Variante (PlatoID, Nombre, Precio_Venta) VALUES (:platoID, :nombre, :precio)",
                        [':platoID' => $id, ':nombre' => $vNombre, ':precio' => $vPrecio]
                    );
                }
            }

            ok('Plato actualizado correctamente.');
        } catch (Exception $e) {
            error_log('Error editar plato: ' . $e->getMessage());
            fail('Error al actualizar el plato.');
        }

    // ── Cambiar estado (Disponible / Oculto) ─────────────────
    case 'toggle_estado':
        $id     = (int)($_POST['id']     ?? 0);
        $estado = $_POST['estado'] === 'Oculto' ? 'Oculto' : 'Disponible';
        if ($id === 0) fail('ID inválido.');

        try {
            $db->execute(
                "UPDATE Plato SET Estado = :estado WHERE PlatoID = :id",
                [':estado' => $estado, ':id' => $id]
            );
            ok('Estado actualizado.');
        } catch (Exception $e) {
            error_log('Error toggle_estado: ' . $e->getMessage());
            fail('Error al actualizar estado.');
        }

    // ── Cambiar flag Top / Promo ──────────────────────────────
    case 'toggle_flag':
        $id    = (int)($_POST['id']    ?? 0);
        $flag  = $_POST['flag']  ?? '';
        $valor = (int)($_POST['valor'] ?? 0);

        if ($id === 0 || !in_array($flag, ['top', 'promo'])) fail('Datos inválidos.');

        $col  = $flag === 'top' ? 'Es_Top' : 'Es_Promo';
        
        try {
            $db->execute(
                "UPDATE Plato SET {$col} = :valor WHERE PlatoID = :id",
                [':valor' => $valor, ':id' => $id]
            );
            ok('Flag actualizado.');
        } catch (Exception $e) {
            error_log('Error toggle_flag: ' . $e->getMessage());
            fail('Error al actualizar flag.');
        }

    default:
        fail('Acción no reconocida.');
}
