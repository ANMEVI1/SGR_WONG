# 🎨 GUÍA DE USO DEL NUEVO DISEÑO MIGRADO

## 📌 RESUMEN

El diseño oscuro premium de `migracion_diseño_final` ha sido migrado exitosamente al proyecto principal. Esta guía te ayudará a usar y mantener el nuevo diseño.

---

## 🚀 INICIO RÁPIDO

### 1. Verificar que todo esté en su lugar

```bash
# Verificar archivos CSS
dir css\estilos.css
dir css\header.css

# Verificar assets
dir assets\LOGO_7.jpg

# Verificar header
type includes\header.php
```

### 2. Abrir el proyecto en el navegador

Simplemente abre `index.php` en tu navegador. Deberías ver:
- ✅ Fondo estático con LOGO_7.jpg
- ✅ Header oscuro (#111111) con navegación central
- ✅ Botones dorados (#c9954a)
- ✅ Títulos blancos con tipografía elegante
- ✅ Footer oscuro (#0a0a0a)

---

## 🎨 CLASES CSS DISPONIBLES

### Botones

```html
<!-- Botón primario (dorado) -->
<button class="btn btn-primary">Agregar al carrito</button>

<!-- Botón secundario (transparente) -->
<button class="btn btn-secondary">Ver más</button>

<!-- Botón pequeño -->
<button class="btn btn-primary btn-small">Comprar</button>

<!-- Botón ancho completo -->
<button class="btn btn-primary btn-full">Enviar</button>
```

### Títulos de Sección

```html
<!-- Título principal de sección -->
<h2 class="section-title">Nuestro Menú</h2>

<!-- Subtítulo de sección -->
<p class="section-subtitle">Descubre nuestros platos más populares</p>

<!-- Título con línea decorativa dorada -->
<h2 class="section-title">Promociones</h2>
<!-- Automáticamente incluye ::after con línea dorada -->
```

### Cards

```html
<!-- Card básica -->
<div class="menu-item">
    <img src="plato.jpg" alt="Plato">
    <div class="menu-item-content">
        <h3>Nombre del Plato</h3>
        <p>Descripción del plato</p>
        <div class="menu-item-price">S/ 25.00</div>
        <button class="btn btn-primary">Agregar</button>
    </div>
</div>
```

### Hero Section

```html
<!-- Hero con overlay -->
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-eyebrow">BIENVENIDOS</div>
        <h1>Chifa Matsue</h1>
        <p class="hero-subtitle">Auténtica comida china</p>
        <div class="hero-buttons">
            <a href="#" class="btn btn-primary">Ver Menú</a>
            <a href="#" class="btn btn-secondary">Reservar</a>
        </div>
    </div>
</section>
```

---

## 🎯 VARIABLES CSS PERSONALIZABLES

Puedes personalizar los colores editando las variables en `css/estilos.css`:

```css
:root {
    --primary: #111111;        /* Negro principal */
    --gold: #c9954a;           /* Dorado principal */
    --gold-light: #e6a860;     /* Dorado claro */
    --gold-dark: #a8773a;      /* Dorado oscuro */
    --text: #1a1a1a;           /* Color de texto */
    --text-light: #666666;     /* Texto secundario */
    --bg: #f5f5f0;             /* Fondo claro */
    --white: #ffffff;          /* Blanco */
    --border: #e0e0e0;         /* Bordes */
    --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 12px 40px rgba(0, 0, 0, 0.15);
    --transition: all 0.3s ease;
    --radius: 12px;            /* Border radius normal */
    --radius-lg: 18px;         /* Border radius grande */
}
```

---

## 📱 RESPONSIVE BREAKPOINTS

El diseño es completamente responsive con estos breakpoints:

```css
/* Desktop grande */
@media (max-width: 1200px) { /* Ajustes menores */ }

/* Tablet landscape */
@media (max-width: 992px) { /* Footer 2 columnas */ }

/* Tablet portrait */
@media (max-width: 768px) { /* Grid 1 columna */ }

/* Mobile landscape */
@media (max-width: 640px) { /* Búsqueda oculta */ }

/* Mobile portrait */
@media (max-width: 480px) { /* Elementos compactos */ }
```

---

## 🔧 COMPONENTES PRINCIPALES

### 1. Header

El header tiene tres secciones:

```html
<header class="header">
    <div class="header-container">
        <!-- Izquierda: Hamburguesa + Logo -->
        <div class="header-left">...</div>
        
        <!-- Centro: Navegación -->
        <nav class="nav-menu">...</nav>
        
        <!-- Derecha: Búsqueda + Carrito + Perfil -->
        <div class="header-right">...</div>
    </div>
</header>
```

### 2. Footer

```html
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">...</div>
            <div class="footer-links">...</div>
            <div class="footer-links">...</div>
            <div class="qr-section">...</div>
        </div>
        <div class="footer-bottom">...</div>
    </div>
</footer>
```

### 3. Sidebar

```html
<div class="sidebar">
    <div class="sidebar-header">
        <span class="logo-sidebar">Matsue</span>
        <button class="close-sidebar">×</button>
    </div>
    <nav class="sidebar-nav">
        <a href="#"><i class="fas fa-home"></i> Inicio</a>
        <a href="#"><i class="fas fa-utensils"></i> Menú</a>
        <!-- Más enlaces -->
    </nav>
</div>
<div class="overlay"></div>
```

---

## 🎨 EFECTOS HOVER

Todos los elementos interactivos tienen efectos hover:

### Botones
- **Transform**: `translateY(-2px)` (se eleva)
- **Shadow**: Sombra dorada aumentada
- **Color**: Dorado más oscuro

### Cards
- **Transform**: `translateY(-8px)` (se eleva más)
- **Shadow**: Sombra más pronunciada
- **Image**: `scale(1.06)` (zoom en imagen)

### Enlaces
- **Color**: Cambia a blanco
- **Underline**: Línea dorada animada

---

## 🖼️ IMÁGENES Y ASSETS

### Fondo Global
```css
html {
    background: url('../assets/LOGO_7.jpg') center center / cover fixed no-repeat;
}
```

### Logo
```html
<img src="assets/plato ramen.jpg" alt="Logo Matsue">
```

### Imágenes de Productos
```html
<img src="assets/productos/plato1.jpg" alt="Nombre del plato">
```

---

## 🔍 BÚSQUEDA

La barra de búsqueda tiene estilos específicos:

```html
<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Buscar plato...">
    <i class="fas fa-search"></i>
    <div class="search-results" id="searchResults"></div>
</div>
```

Los resultados aparecen en un dropdown oscuro con hover effect.

---

## 🛒 CARRITO

El icono del carrito tiene un contador:

```html
<div class="cart-icon">
    <a href="views/cliente/carrito.php">
        <i class="fas fa-shopping-cart"></i>
        <span id="cartCount">0</span>
    </a>
</div>
```

El contador es un círculo dorado con el número en negro.

---

## 👤 PERFIL DE USUARIO

### Usuario NO logueado
```html
<a href="login.php" class="profile-button">
    <i class="fas fa-user"></i>
    <span class="profile-label">Iniciar sesión</span>
</a>
```

### Usuario logueado
```html
<div class="user-logged">
    <button id="openProfile" class="profile-button">
        <i class="fas fa-user-circle"></i>
        <span class="profile-label">Mi cuenta</span>
    </button>
</div>
```

---

## 📋 FORMULARIOS

Los formularios tienen estilos consistentes:

```html
<form class="reclamaciones-form">
    <div class="form-group">
        <label>
            <i class="fas fa-user"></i>
            Nombre completo
            <span class="required">*</span>
        </label>
        <input type="text" placeholder="Ingresa tu nombre">
    </div>
    
    <div class="form-group">
        <label>Mensaje</label>
        <textarea placeholder="Escribe tu mensaje"></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary btn-full">Enviar</button>
</form>
```

---

## 🎭 ANIMACIONES

El diseño incluye animaciones suaves:

```css
/* Fade in desde abajo */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Fade in desde arriba */
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
```

Úsalas así:
```css
.mi-elemento {
    animation: fadeInUp 0.5s ease;
}
```

---

## 🚨 MENSAJES DE ESTADO

### Mensaje de éxito
```html
<div class="form-message success">
    <i class="fas fa-check-circle"></i>
    ¡Operación exitosa!
</div>
```

### Mensaje de error
```html
<div class="form-message error">
    <i class="fas fa-exclamation-circle"></i>
    Ocurrió un error
</div>
```

---

## 🎯 MEJORES PRÁCTICAS

### 1. Mantén la consistencia
- Usa siempre las clases predefinidas
- No crees estilos inline
- Respeta las variables CSS

### 2. Optimiza las imágenes
- Comprime las imágenes antes de subirlas
- Usa formatos modernos (WebP)
- Define width y height en las etiquetas img

### 3. Accesibilidad
- Usa atributos `aria-label`
- Mantén el contraste de colores
- Asegura que todo sea navegable por teclado

### 4. Performance
- Minimiza el CSS en producción
- Usa lazy loading para imágenes
- Evita animaciones pesadas

---

## 🔧 TROUBLESHOOTING

### El fondo no se muestra
```bash
# Verifica que LOGO_7.jpg existe
dir assets\LOGO_7.jpg

# Verifica la ruta en estilos.css
findstr /C:"LOGO_7.jpg" css\estilos.css
```

### Los botones no tienen el color dorado
```bash
# Verifica que estilos.css está cargado
# Abre DevTools > Network > CSS
# Busca estilos.css y verifica que carga correctamente
```

### El header no se ve oscuro
```bash
# Verifica que header.css está cargado después de estilos.css
# Orden correcto:
# 1. estilos.css
# 2. header.css
```

### La navegación central no aparece
```bash
# Verifica que header.php tiene <nav class="nav-menu">
findstr /C:"nav-menu" includes\header.php
```

---

## 📞 SOPORTE

Si tienes problemas con el diseño:

1. Verifica que todos los archivos estén en su lugar
2. Revisa la consola del navegador (F12) por errores
3. Comprueba que las rutas de los assets sean correctas
4. Asegúrate de que los archivos CSS se carguen en el orden correcto

---

## 🎉 ¡LISTO!

Ahora tienes un diseño oscuro premium completamente funcional. Disfruta creando páginas hermosas con este sistema de diseño.

**Recuerda**: La carpeta `migracion_diseño_final` puede ser eliminada ahora que la migración está completa. ✅
