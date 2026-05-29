<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Chifa Matsue</title>
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
                <h2>Iniciar Sesión</h2>
                <form action="procesos_backend/validar_login.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                        <input type="email" name="correo" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" name="pass" required>
                    </div>
                    <button type="submit" name="ingresar" class="btn btn-primary btn-full">Iniciar Sesión</button>
                </form>
            </div>
            <p class="auth-link" style="color: white;">¿No tienes cuenta? <a href="registro.php" style="color: var(--gold);">Regístrate aquí</a></p>
        </div>
    </main>
<?php include ('includes/footer.php') ?>
    <script src=""></script>
    <script src=""></script>
</body>
</html>