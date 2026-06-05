<?php
require_once __DIR__ . '/../config/conexion.php';
startSecureSession();

$currentUser = getCurrentUser();
$usuarioID = $currentUser['id'] ?? null;
$loginName = $currentUser['login'] ?? '';
$rolUsuario = $currentUser['rol'] ?? 'Cliente web';
$scopeUsuario = $currentUser['scope'] ?? 'web';

$profile = [
    'nombre' => '',
    'correo' => filter_var($loginName, FILTER_VALIDATE_EMAIL) ? $loginName : '',
    'tipoDocumento' => 'DNI',
    'documento' => '',
    'telefono' => '',
    'direccion' => '',
];

if ($usuarioID) {
    $db = getDB();
    
    // Buscar primero en Cliente
    $clienteData = $db->fetchOne(
        "SELECT Nombre_Apellidos, Tipo_Documento, Num_Documento, Telefono, Direccion, Correo
         FROM Cliente
         WHERE UsuarioID = :userId
         LIMIT 1",
        [':userId' => $usuarioID]
    );

    if ($clienteData) {
        $profile['nombre'] = trim($clienteData['Nombre_Apellidos'] ?: '');
        $profile['tipoDocumento'] = $clienteData['Tipo_Documento'] ?: 'DNI';
        $profile['documento'] = $clienteData['Num_Documento'] ?: '';
        $profile['telefono'] = $clienteData['Telefono'] ?: '';
        $profile['direccion'] = $clienteData['Direccion'] ?: '';
        $profile['correo'] = $clienteData['Correo'] ?: $profile['correo'];
    } else {
        // Si no es cliente, buscar en Empleado
        $empleadoData = $db->fetchOne(
            "SELECT Nombre_Apellidos, DNI, Telefono
             FROM Empleado
             WHERE UsuarioID = :userId
             LIMIT 1",
            [':userId' => $usuarioID]
        );
        
        if ($empleadoData) {
            $profile['nombre'] = trim($empleadoData['Nombre_Apellidos'] ?: '');
            $profile['tipoDocumento'] = 'DNI';
            $profile['documento'] = $empleadoData['DNI'] ?: '';
            $profile['telefono'] = $empleadoData['Telefono'] ?: '';
            $profile['direccion'] = 'Empleado del sistema';
            // El correo ya está en $loginName
        }
    }
}

if ($profile['correo'] === '' && filter_var($loginName, FILTER_VALIDATE_EMAIL)) {
    $profile['correo'] = $loginName;
}

$displayName = $profile['nombre'] !== '' ? $profile['nombre'] : $loginName;
$firstName = 'Usuario';
if ($displayName !== '') {
    $parts = preg_split('/\s+/', trim($displayName));
    $firstName = $parts[0] ?? 'Usuario';
}

$initials = 'US';
if ($displayName !== '') {
    $words = preg_split('/\s+/', strtoupper(trim($displayName)));
    $initials = substr($words[0] ?? 'U', 0, 1);
    if (!empty($words[1])) {
        $initials .= substr($words[1], 0, 1);
    }
}

// Determinar menú según el scope del usuario
$isAdmin = ($scopeUsuario === 'backoffice');

$menuByRole = [
    'admin' => [
        ['section' => 'datos', 'label' => 'Mis datos'],
        ['section' => 'pedidos', 'label' => 'Mis pedidos'],
        ['section' => 'reservas', 'label' => 'Mis reservas'],
        ['section' => 'password', 'label' => 'Contraseña'],
        ['section' => 'admin', 'label' => 'Administrar', 'admin_only' => true],
        ['section' => 'logout', 'label' => 'Cerrar sesión'],
    ],
    'cliente' => [
        ['section' => 'datos', 'label' => 'Mis datos'],
        ['section' => 'pedidos', 'label' => 'Mis pedidos'],
        ['section' => 'reservas', 'label' => 'Mis reservas'],
        ['section' => 'direcciones', 'label' => 'Direcciones'],
        ['section' => 'password', 'label' => 'Contraseña'],
        ['section' => 'logout', 'label' => 'Cerrar sesión'],
    ],
];

$menuItems = $isAdmin ? $menuByRole['admin'] : $menuByRole['cliente'];
?>

<div id="modalOverlay" class="modal-overlay" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <aside class="side">
            <div class="profile">
                <div class="avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                <div>
                    <div class="hi">¡Hola,</div>
                    <div class="name" id="modalTitle"><?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>!</div>
                    <?php if (!empty($profile['correo'])): ?>
                        <div class="user-email"><?= htmlspecialchars($profile['correo'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <span class="status"><span class="status-dot"></span> Sesión activa</span>

            <nav class="modal-menu">
                <?php foreach ($menuItems as $item): ?>
                    <button class="modal-menu-item<?= $item['section'] === 'datos' ? ' active' : '' ?>" data-section="<?= htmlspecialchars($item['section'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($item['section'] === 'datos'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 21a8 8 0 0 1 16 0"/>
                            </svg>
                        <?php elseif ($item['section'] === 'pedidos'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                <path d="M3 6h18"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                        <?php elseif ($item['section'] === 'direcciones'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        <?php elseif ($item['section'] === 'reservas'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18"/>
                                <circle cx="12" cy="15" r="2"/>
                            </svg>
                        <?php elseif ($item['section'] === 'password'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m21 2-9.6 9.6"/>
                                <circle cx="7.5" cy="15.5" r="5.5"/>
                                <path d="m15 6 3 3"/>
                            </svg>
                        <?php elseif ($item['section'] === 'admin'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                <path d="M2 17l10 5 10-5"/>
                                <path d="M2 12l10 5 10-5"/>
                            </svg>
                        <?php elseif ($item['section'] === 'usuarios'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="8" r="4"/>
                                <path d="M17 11a3 3 0 1 0 0-6"/>
                                <path d="M2 21a7 7 0 0 1 14 0"/>
                            </svg>
                        <?php elseif ($item['section'] === 'reportes'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19V5"/>
                                <path d="M4 19h16"/>
                                <path d="M8 16v-5"/>
                                <path d="M12 16V8"/>
                                <path d="M16 16v-3"/>
                            </svg>
                        <?php elseif ($item['section'] === 'logout'): ?>
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <path d="m16 17 5-5-5-5"/>
                                <path d="M21 12H9"/>
                            </svg>
                        <?php endif; ?>

                        <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>

                        <svg class="chev" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 6 6 6-6 6"/>
                        </svg>
                    </button>
                <?php endforeach; ?>
            </nav>

            <div class="progress-wrap">
                <div class="progress"><div class="progress-bar"></div></div>
                <small>50% completado</small>
            </div>
        </aside>

        <section class="content" id="content"></section>

        <button class="close" id="closeModal" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<div id="toast" class="toast" role="status" aria-live="polite"></div>

<script>
window.USER_PROFILE = <?= json_encode($profile, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
window.USER_ROLE = <?= json_encode($rolUsuario, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
window.USER_MENU = <?= json_encode($menuItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
</script>