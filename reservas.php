<?php
require_once 'config/conexion.php';
startSecureSession();

$currentUser = getCurrentUser();
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
                        <h3>Datos de la Reserva</h3>
                        <form class="reserva-form" id="reservaForm">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-user"></i>
                                    Nombre completo
                                    <span class="required">*</span>
                                </label>
                                <input type="text" name="nombre" required placeholder="Ingresa tu nombre completo">
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-envelope"></i>
                                    Correo electrónico
                                    <span class="required">*</span>
                                </label>
                                <input type="email" name="correo" required placeholder="tu@correo.com">
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-phone"></i>
                                    Teléfono
                                    <span class="required">*</span>
                                </label>
                                <input type="tel" name="telefono" required placeholder="+51 999 999 999">
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

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-comment"></i>
                                    Comentarios adicionales
                                </label>
                                <textarea name="comentarios" rows="4" placeholder="Alguna petición especial o comentario..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-full" style="margin-top: 20px;">
                                <i class="fas fa-check"></i> Confirmar Reserva
                            </button>
                        </form>
                    </div>

                    <!-- Información Adicional -->
                    <div>
                        <div class="reservation-card">
                            <h3>Información Importante</h3>
                            <ul style="list-style: none; padding: 0; margin: 20px 0;">
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Las reservas deben hacerse con al menos 2 horas de anticipación</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Confirmaremos tu reserva por correo o WhatsApp</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Tiempo de tolerancia: 15 minutos</span>
                                </li>
                                <li style="margin-bottom: 15px; display: flex; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: var(--gold); margin-top: 3px;"></i>
                                    <span>Para grupos mayores a 8 personas, contáctanos directamente</span>
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
    <script>
        // Manejo del formulario de reservas
        document.getElementById('reservaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Aquí puedes agregar la lógica para enviar la reserva
            console.log('Datos de reserva:', data);
            
            // Mostrar mensaje de éxito
            alert('¡Reserva enviada! Te contactaremos pronto para confirmar.');
            this.reset();
        });

        // Establecer fecha mínima (hoy)
        const fechaInput = document.querySelector('input[name="fecha"]');
        const today = new Date().toISOString().split('T')[0];
        fechaInput.setAttribute('min', today);
    </script>
</body>
</html>
