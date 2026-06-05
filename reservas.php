<?php
require_once 'config/conexion.php';
startSecureSession();

$currentUser = getCurrentUser();

// Cargar perfil completo si hay usuario
$profile = [
    'nombre' => '',
    'correo' => '',
    'telefono' => ''
];

if ($currentUser) {
    $db = getDB();
    $usuarioID = $currentUser['id'];
    
    // Buscar en Cliente
    $clienteData = $db->fetchOne(
        "SELECT Nombre_Apellidos, Correo, Telefono FROM Cliente WHERE UsuarioID = ?",
        [$usuarioID]
    );
    
    if ($clienteData) {
        $profile['nombre'] = $clienteData['Nombre_Apellidos'] ?? '';
        $profile['correo'] = $clienteData['Correo'] ?? '';
        $profile['telefono'] = $clienteData['Telefono'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reserva tu mesa en Chifa Matsue - Reservas online">
    <title>Reservas - Chifa Matsue</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/modal-perfil.css">
    <link rel="stylesheet" href="css/reservas.css">
    <link rel="stylesheet" href="css/modules/modal-confirmacion-reserva.css">
    <link rel="stylesheet" href="css/responsivo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/aside-rol.php'; ?>
    <?php include 'includes/aside.php'; ?>

    <main>
        <!-- Sección de Reservas -->
        <section class="reserva-page" style="padding: 100px 0 80px; min-height: calc(100vh - 72px);">
            <div class="container">
                <div class="section-header">
                    <span class="section-eyebrow">Haz tu reserva</span>
                    <h2 class="section-title" style="color: white;">Reserva tu Mesa</h2>
                    <p class="section-subtitle" style="color: white;">Completa el formulario y te confirmaremos tu reserva</p>
                </div>

                <div class="reserva-layout" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; margin-top: 50px;">
                    <!-- Formulario de Reserva -->
                    <div class="reservation-card">
                        <?php if ($currentUser): ?>
                            <!-- Usuario autenticado: Opción rápida -->
                            <div class="reserva-tipo" style="margin-bottom: 30px;">
                                <h3 style="margin-bottom: 15px;">¿Para quién es la reserva?</h3>
                                <div style="display: flex; gap: 15px;">
                                    <button type="button" class="btn-reserva-tipo active" data-tipo="yo" style="flex: 1; padding: 15px; border: 2px solid var(--gold); background: var(--gold); color: white; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                                        <i class="fas fa-user"></i> Para mí
                                    </button>
                                    <button type="button" class="btn-reserva-tipo" data-tipo="otro" style="flex: 1; padding: 15px; border: 2px solid var(--gold); background: transparent; color: var(--gold); border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.3s;">
                                        <i class="fas fa-user-friends"></i> Para otra persona
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h3>Datos de la Reserva</h3>
                        <form class="reserva-form" id="reservaForm">
                            <?php if ($currentUser): ?>
                                <!-- Campos ocultos con datos del usuario -->
                                <input type="hidden" id="clienteNombre" value="<?= htmlspecialchars($profile['nombre']) ?>">
                                <input type="hidden" id="clienteCorreo" value="<?= htmlspecialchars($profile['correo']) ?>">
                                <input type="hidden" id="clienteTelefono" value="<?= htmlspecialchars($profile['telefono']) ?>">
                            <?php endif; ?>

                            <div id="datosContacto" <?= $currentUser ? 'style="display: none;"' : '' ?>>
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-user"></i>
                                        Nombre completo
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" name="nombre" <?= !$currentUser ? 'required' : '' ?> placeholder="Ingresa tu nombre completo">
                                </div>

                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-envelope"></i>
                                        Correo electrónico
                                        <span class="required">*</span>
                                    </label>
                                    <input type="email" name="correo" <?= !$currentUser ? 'required' : '' ?> placeholder="tu@correo.com">
                                </div>

                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-phone"></i>
                                        Teléfono
                                        <span class="required">*</span>
                                    </label>
                                    <input type="tel" name="telefono" <?= !$currentUser ? 'required' : '' ?> placeholder="+51 999 999 999">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-calendar"></i>
                                        Fecha
                                        <span class="required">*</span>
                                    </label>
                                    <input type="date" name="fecha" required>
                                </div>

                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-clock"></i>
                                        Hora
                                        <span class="required">*</span>
                                    </label>
                                    <input type="time" name="hora" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-users"></i>
                                    Número de personas
                                    <span class="required">*</span>
                                </label>
                                <select name="personas" required>
                                    <option value="">Selecciona...</option>
                                    <option value="1">1 persona</option>
                                    <option value="2">2 personas</option>
                                    <option value="3">3 personas</option>
                                    <option value="4">4 personas</option>
                                    <option value="5">5 personas</option>
                                    <option value="6">6 personas</option>
                                    <option value="7">7 personas</option>
                                    <option value="8">8 personas</option>
                                    <option value="mas">Más de 8 personas</option>
                                </select>
                            </div>

                            <!-- Sección: Pre-seleccionar platos (opcional) -->
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" id="incluirPlatos" style="width: auto; margin: 0;">
                                    <span>
                                        <i class="fas fa-utensils"></i>
                                        ¿Deseas pre-ordenar platos? (Opcional)
                                    </span>
                                </label>
                                <small style="display: block; margin-top: 5px; color: #666;">
                                    Puedes seleccionar platos ahora y garantizar disponibilidad
                                </small>
                            </div>

                            <div id="selectorPlatos" style="display: none; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                                <h4 style="margin-bottom: 15px; color: var(--gold-dark);">
                                    <i class="fas fa-clipboard-list"></i> Selecciona tus platos
                                </h4>
                                <div id="listadoPlatos" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Se cargará dinámicamente via AJAX -->
                                    <p style="text-align: center; color: #999;">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando platos...
                                    </p>
                                </div>
                                <div id="resumenPlatos" style="margin-top: 15px; padding: 15px; background: white; border-radius: 8px; display: none;">
                                    <h5 style="margin-bottom: 10px;">Platos seleccionados:</h5>
                                    <ul id="listaSeleccionados" style="list-style: none; padding: 0;"></ul>
                                    <p style="margin-top: 10px; font-weight: bold; color: var(--gold-dark);">
                                        Subtotal: S/ <span id="subtotalPlatos">0.00</span>
                                    </p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-comment"></i>
                                    Comentarios adicionales
                                </label>
                                <textarea name="comentarios" rows="4" placeholder="Alguna petición especial o comentario..."></textarea>
                            </div>

                            <!-- Sección: Pago Anticipado (Señal) -->
                            <div class="pago-anticipado" style="margin-top: 25px; padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px;">
                                <h4 style="margin-bottom: 10px; color: #856404;">
                                    <i class="fas fa-credit-card"></i> Señal de Reserva
                                </h4>
                                <p style="margin-bottom: 15px; font-size: 14px; color: #856404;">
                                    Para confirmar tu reserva, solicitamos una señal de <strong>S/ 11.00</strong> por persona + <strong>30%</strong> del subtotal de platos pre-ordenados (si los hay).
                                    Este monto se descontará de tu consumo final.
                                </p>
                                <div id="montoSenal" style="padding: 15px; background: white; border-radius: 8px; text-align: center; margin-bottom: 15px;">
                                    <p style="font-size: 24px; font-weight: bold; color: var(--gold-dark); margin: 0;">
                                        Total señal: S/ <span id="totalSenal">0.00</span>
                                    </p>
                                    <div id="desgloseSenalPreview" style="margin-top: 10px; font-size: 12px; color: #666; display: none;">
                                        <div style="display: flex; justify-content: space-between; padding: 3px 20px;">
                                            <span>Base (<span id="prevPersonas">0</span> personas)</span>
                                            <span>S/ <span id="prevBase">0.00</span></span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 3px 20px;">
                                            <span>30% platos</span>
                                            <span>S/ <span id="prevPlatos">0.00</span></span>
                                        </div>
                                    </div>
                                    <small style="color: #666; display: block; margin-top: 5px;">Cálculo automático según selección</small>
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label>
                                        <i class="fas fa-wallet"></i>
                                        Método de pago para señal
                                        <span class="required">*</span>
                                    </label>
                                    <select name="metodo_pago" required style="background: white;">
                                        <option value="">Selecciona método de pago...</option>
                                        <option value="yape">Yape</option>
                                        <option value="plin">Plin</option>
                                        <option value="transferencia">Transferencia bancaria</option>
                                        <option value="tarjeta">Tarjeta de crédito/débito</option>
                                    </select>
                                </div>
                                <div id="instruccionesPago" style="margin-top: 15px; padding: 15px; background: #d1ecf1; border-radius: 8px; display: none;">
                                    <p style="margin: 0; font-size: 13px; color: #0c5460;">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Nota:</strong> Después de confirmar la reserva, te enviaremos las instrucciones de pago a tu correo.
                                    </p>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-full" style="margin-top: 25px; padding: 18px; font-size: 16px; font-weight: 700; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fas fa-check-circle"></i> <span>Confirmar Reserva y Proceder al Pago</span>
                            </button>
                            <p style="text-align: center; font-size: 12px; color: #999; margin-top: 10px;">
                                Al confirmar aceptas nuestros <a href="#" style="color: var(--gold);">términos y condiciones</a>
                            </p>
                        </form>
                    </div>

                    <!-- Información Adicional -->
                    <div>
                        <div class="reservation-card">
                            <h3>Información Importante</h3>
                            <ul style="list-style: none; padding: 0; margin: 20px 0;">
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Señal de S/ 11.00 por persona + 30% del subtotal de platos</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>La señal se descuenta de tu consumo final</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Reservas con al menos 2 horas de anticipación</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Tiempo de tolerancia: 15 minutos</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Puedes pre-ordenar platos para agilizar tu atención</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Recibirás confirmación e instrucciones de pago por correo</span>
                                </li>
                            </ul>
                        </div>

                        <div class="reservation-card" style="margin-top: 20px;">
                            <h3>Horarios de Atención</h3>
                            <div style="margin-top: 20px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--border);">
                                    <span style="font-weight: 600;">Lunes a Domingo</span>
                                    <span style="color: var(--gold-dark); font-weight: 700;">11:00 AM - 10:00 PM</span>
                                </div>
                            </div>
                        </div>

                        <div class="reservation-card" style="margin-top: 20px;">
                            <h3>Contáctanos</h3>
                            <div style="margin-top: 20px;">
                                <p style="margin-bottom: 15px;">
                                    <i class="fas fa-phone" style="color: var(--gold); margin-right: 8px;"></i>
                                    <strong>Teléfono:</strong> +51 991 183 777
                                </p>
                                <p style="margin-bottom: 15px;">
                                    <i class="fab fa-whatsapp" style="color: var(--gold); margin-right: 8px;"></i>
                                    <strong>WhatsApp:</strong> +51 991 183 777
                                </p>
                                <p>
                                    <i class="fas fa-map-marker-alt" style="color: var(--gold); margin-right: 8px;"></i>
                                    <strong>Dirección:</strong> Zorritos, Tumbes, Perú
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Botón WhatsApp Flotante -->
    <a href="https://wa.me/+51991183777?text=Hola,%20quiero%20hacer%20una%20reserva" 
       target="_blank" 
       class="whatsapp-btn" 
       aria-label="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="js/sidebar.js"></script>
    <script src="js/modal-perfil.js"></script>
    <!-- Configuración de ruta base para JavaScript -->
    <script>
        // Establecer ruta base para las APIs
        window.APP_BASE_URL = window.location.pathname.replace(/\/[^\/]*$/, '/');
    </script>
    <!-- Módulo de modal de confirmación -->
    <script src="js/modules/modal-confirmacion-reserva.js"></script>
    <!-- Módulo principal de reservas -->
    <script src="js/reservas/cliente-mejorado.js"></script>
</body>
</html>
