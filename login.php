<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Chifa Matsue</title>
    <?php include ('includes/links_head.php') ?>
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
                <h2>Iniciar Sesión</h2>
                <form  action="procesos_backend/validar_login.php"  method="POST"  id="loginForm">
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
                <p class="auth-link">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
            </div>
        </div>
    </main>
<?php include ('includes/footer.php') ?>
    <script src=""></script>
    <script src=""></script>
</body>
</html>