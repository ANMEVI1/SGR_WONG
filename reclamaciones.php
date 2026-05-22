<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro de Reclamaciones - Chifa Matsue</title>
    <?php include ('includes/links_head.php') ?>
</head>
<body>
    <header class="header">
        <section class="container header-container">
            <a href="index.html" class="logo">
                <img src="assets/logo_proyect.svg" alt="Logo Chifa Matsue"><span>Chifa Matsue</span>
            </a>
            <div class="header-right">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Buscar plato..." aria-label="Buscar plato">
                    <i class="fas fa-search"></i>
                    <div class="search-results" id="searchResults" role="listbox"></div>
                </div>
                <div class="cart-icon">
                    <a href="carrito.html" aria-label="Ver carrito">
                        <i class="fas fa-shopping-cart"></i><span id="cartCount">0</span>
                    </a>
                </div>
                <div class="user-icon">
                    <a href="login.html">
                        <i class="fas fa-user"></i><span class="user-text">Iniciar sesión</span>
                    </a>
                </div>
            </div>
        </section>
    </header>
    <main class="container reclamaciones-section">
        <h1 class="section-title">Libro de Reclamaciones</h1>
        <div class="reclamaciones-form">
            <form>
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" required>
                </div>
                <div class="form-group">
                    <label>DNI / CE</label>
                    <input type="text" required>
                </div>
                <div class="form-group">
                    <label>Tipo: Reclamo / Queja</label>
                    <select required>
                        <option>Reclamo</option>
                        <option>Queja</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea rows="6" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Enviar Reclamación</button>
            </form>
        </div>
    </main>
<?php include ('includes/footer.php') ?>
</body>
</html>