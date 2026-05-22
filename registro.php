<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Chifa Matsue</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="css/login-registro.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/responsivo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="auth-body">
    <header class="header">
        <section class="container header-container">
            <a href="index.php" class="logo">
                <img src="assets/LOGO1.jpg" alt="Logo Chifa Matsue"><span>Chifa Matsue</span>
            </a>
        </section>
    </header>
    <section class="hero-carousel" aria-label="Promociones destacadas">
        <div class="carousel-container">
            <div class="carousel-slide active">
                <img src="assets/logo_chifa_matsue.png" alt="Chifa Matsue - Promoción especial">
            </div>
            <div class="carousel-slide">
                <img src="assets/plato ramen.jpg" alt="Chifa Matsue - Plato de ramen">
            </div>
            <div class="carousel-slide">
                <img src="assets/ramen_IVI5301-scaled.jpg" alt="Chifa Matsue - Novedades del menú">
            </div>
        </div>
    </section>
    <main class="auth-section">
        <div class="auth-container">
            <div class="auth-form">
                <h2>Crear Cuenta</h2>
                <form action="procesos_backend/procesar_registro.php" method="POST" id="registroForm">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre_completo" required>
                    </div>
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" name="nmr_documento" placeholder="Máximo 8 dígitos aceptados" >
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" maxlength=9  placeholder="Máximo 9 dígitos aceptados" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" placeholder="Campo obligatorio" required>   
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="pass" placeholder="Campo obligatorio" required>
                    </div>
                    <button type="submit"  name="registrar" class="btn btn-primary btn-full">Registrarse</button>
                </form>
                <p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </div>
    </main>
<footer class="footer">
        <section  class="container footer-grid"> 
            <div class="footer-brand">
                <div class="footer-logo-box">
                    <img src="assets/logo_proyect.svg" alt="Logo Chifa Matsue">
                    <span>Chifa Matsue</span>
                </div>
                <p>La mejor comida rápida de la ciudad, preparada con ingredientes frescos y mucho amor. ¡Siente el sabor!</p>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <div class="footer-links">
                <h3>Atención al Cliente</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> +51 999 999 999</li>
                    <li><i class="fab fa-whatsapp"></i> +51 999 999 999</li>
                    <li><a href="#"><i class="fas fa-comment-dots"></i> Encuesta de satisfacción</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h3>Enlaces Útiles</h3>
                <ul>
                    <li><a href="reclamaciones.html"><i class="fas fa-file-alt"></i> Libro de Reclamaciones</a></li>
                </ul>
            </div>

            <div class="qr-section">
                <h3>¡Pide Rápido!</h3>
                <p>Escanea el código QR</p>
                <img src="assets/qr.png" alt="Código QR para pedidos rápidos">
            </div>
        </section>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Chifa Matsue. Todos los derechos reservados.</p>
            <p class="footer-dev">Desarrollado por: RAMOS, RICHARD Y ANDRE </p>
        </div>
    </footer>
    <script src=""></script>
    <script src=""></script>
</body>
</html>