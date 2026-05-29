<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libro de Reclamaciones - Chifa Matsue</title>
    <link rel="stylesheet" href="../../css/estilos.css">
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/reclamaciones.css">
    <link rel="stylesheet" href="../../css/responsivo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include ('../../includes/header.php'); ?>
    <?php include ('../../includes/aside.php'); ?>
    
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
<?php include ('../../includes/footer.php') ?>
</body>
</html>