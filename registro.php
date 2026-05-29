<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Chifa Matsue</title>
    <?php include ('includes/links_head.php') ?>
</head>
<body class="auth-body">
    <section class="hero-carousel" aria-label="Fondo de bienvenida">
        <div class="carousel-container">
            <div class="carousel-slide active">
                <img src="assets/fondo_todo.jpg" alt="Chifa Matsue - Bienvenido">
            </div>
        </div>
    </section>
    
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <a href="index.php" class="logo">
                    <img src="assets/plato ramen.jpg" alt="Logo Matsue">
                    <span>Matsue</span>
                </a>
            </div>
        </div>
    </header>
    
    <main class="auth-section">
        <div class="auth-container">
            <div class="auth-form">
                <h2>Crear Cuenta</h2>
                <form action="procesos_backend/procesar_registro.php" method="POST" id="registroForm">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombre Completo</label>
                        <input type="text" name="nombre_completo" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> DNI</label>
                        <input type="text" name="nmr_documento" placeholder="Máximo 8 dígitos" maxlength="8">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="tel" name="telefono" maxlength="9" placeholder="Máximo 9 dígitos" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                        <input type="email" name="correo" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" name="pass" required>
                    </div>
                    <button type="submit" name="registrar" class="btn btn-primary btn-full">Registrarse</button>
                </form>
            </div>
            <p class="auth-link" style="color: white;">¿Ya tienes cuenta? <a href="login.php" style="color: var(--gold);">Inicia sesión</a></p>
        </div>
    </main>
<?php include ('includes/footer.php') ?>