# 💻 EJEMPLOS DE CÓDIGO - DISEÑO OSCURO PREMIUM

## 🎨 SECCIÓN CON TÍTULO Y SUBTÍTULO

```html
<section class="container">
    <h2 class="section-title">Nuestros Platos Especiales</h2>
    <p class="section-subtitle">Descubre los sabores auténticos de la cocina oriental</p>
    
    <!-- Contenido de la sección -->
</section>
```

## 🔘 BOTONES

### Botón Primario (Dorado)
```html
<!-- Botón normal -->
<button class="btn btn-primary">Agregar al Carrito</button>

<!-- Botón pequeño -->
<button class="btn btn-primary btn-small">Comprar</button>

<!-- Botón ancho completo -->
<button class="btn btn-primary btn-full">Enviar Pedido</button>

<!-- Botón con icono -->
<button class="btn btn-primary">
    <i class="fas fa-shopping-cart"></i>
    Agregar al Carrito
</button>
```

### Botón Secundario (Transparente)
```html
<button class="btn btn-secondary">Ver Más</button>

<a href="menu.php" class="btn btn-secondary">
    <i class="fas fa-utensils"></i>
    Ver Menú Completo
</a>
```

### Botón Deshabilitado
```html
<button class="btn btn-primary btn-stock-disabled" disabled>
    Agotado
</button>
```

## 📦 CARD DE PRODUCTO

```html
<div class="menu-item">
    <img src="assets/img/platos/chaufa.jpg" alt="Chaufa Especial">
    
    <div class="menu-item-content">
        <h3>
            Chaufa Especial
            <span class="promo-tag">PROMO</span>
        </h3>
        
        <p>Arroz frito con pollo, cerdo, camarones y vegetales frescos</p>
        
        <div class="stock-info">
            <strong>Stock disponible:</strong> 15 unidades
        </div>
        
        <div class="menu-item-price">S/ 25.00</div>
        
        <button class="btn btn-primary btn-full">
            <i class="fas fa-cart-plus"></i>
            Agregar al Carrito
        </button>
    </div>
</div>
```

## 🎯 GRID DE PRODUCTOS

```html
<section class="container">
    <h2 class="section-title">Menú del Día</h2>
    <p class="section-subtitle">Platos preparados con ingredientes frescos</p>
    
    <div class="menu-grid">
        <!-- Card 1 -->
        <div class="menu-item">...</div>
        
        <!-- Card 2 -->
        <div class="menu-item">...</div>
        
        <!-- Card 3 -->
        <div class="menu-item">...</div>
    </div>
</section>
```

## 🎠 MINI CARRUSEL

```html
<section class="container">
    <h2 class="section-title">Promociones</h2>
    
    <div class="mini-carousel">
        <div class="mini-item">
            <img src="assets/img/promo1.jpg" alt="Promo 1">
            <p>Combo Familiar <span class="promo-tag">-20%</span></p>
            <span>S/ 45.00</span>
        </div>
        
        <div class="mini-item">
            <img src="assets/img/promo2.jpg" alt="Promo 2">
            <p>Menú Ejecutivo</p>
            <span>S/ 18.00</span>
        </div>
        
        <!-- Más items... -->
    </div>
</section>
```

## 📍 SECCIÓN DE UBICACIÓN

```html
<section class="location-section">
    <div class="location-container">
        <div class="location-header">
            <h2 class="section-title">Encuéntranos</h2>
            <p class="section-subtitle">Visítanos en nuestra ubicación</p>
        </div>
        
        <div class="location-content">
            <div class="map-wrapper">
                <iframe src="https://maps.google.com/..." 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
            </div>
            
            <div class="location-info">
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Dirección</h4>
                        <p>Av. Principal 123, Lima, Perú</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Teléfono</h4>
                        <p>+51 999 888 777</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Horario</h4>
                        <p>Lun - Dom: 11:00 AM - 10:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
```

## 📝 FORMULARIO CON ESTILO OSCURO

```html
<section class="reclamaciones-section">
    <div class="container">
        <h2 class="section-title">Libro de Reclamaciones</h2>
        
        <div class="reclamaciones-form">
            <form>
                <div class="form-row">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i>
                            Nombre Completo
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               placeholder="Ingrese su nombre" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-envelope"></i>
                            Correo Electrónico
                            <span class="required">*</span>
                        </label>
                        <input type="email" 
                               placeholder="correo@ejemplo.com" 
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-comment"></i>
                        Mensaje
                        <span class="required">*</span>
                    </label>
                    <textarea placeholder="Describa su reclamo..." 
                              required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Reclamo
                </button>
            </form>
        </div>
    </div>
</section>
```

## 🎨 SOBRE NOSOTROS

```html
<section class="about">
    <div class="container">
        <h2 class="section-title">¿Por Qué Elegirnos?</h2>
        
        <div class="about-grid">
            <div class="about-card">
                <div class="about-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3>Ingredientes Frescos</h3>
                <p>Utilizamos solo los mejores ingredientes seleccionados diariamente</p>
            </div>
            
            <div class="about-card">
                <div class="about-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Entrega Rápida</h3>
                <p>Tu pedido llega caliente en menos de 30 minutos</p>
            </div>
            
            <div class="about-card">
                <div class="about-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Calidad Garantizada</h3>
                <p>Más de 10 años de experiencia en cocina oriental</p>
            </div>
        </div>
    </div>
</section>
```

## 🦶 FOOTER PERSONALIZADO

```html
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Columna 1: Brand -->
            <div class="footer-brand">
                <div class="footer-logo-box">
                    <img src="assets/logo_proyect.svg" alt="Logo">
                    <span>Matsue</span>
                </div>
                <p>Sabores auténticos de la cocina oriental en el corazón de Lima</p>
                
                <div class="social-icons">
                    <a href="#" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            
            <!-- Columna 2: Enlaces -->
            <div class="footer-links">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><i class="fas fa-chevron-right"></i> Inicio</li>
                    <li><i class="fas fa-chevron-right"></i> Menú</li>
                    <li><i class="fas fa-chevron-right"></i> Reservas</li>
                </ul>
            </div>
            
            <!-- Columna 3: Contacto -->
            <div class="footer-links">
                <h3>Contacto</h3>
                <ul>
                    <li><i class="fas fa-phone"></i> +51 999 888 777</li>
                    <li><i class="fas fa-envelope"></i> info@matsue.com</li>
                    <li><i class="fas fa-map-marker-alt"></i> Lima, Perú</li>
                </ul>
            </div>
            
            <!-- Columna 4: QR -->
            <div class="qr-section">
                <h3>Escanea y Pide</h3>
                <p>Usa nuestro código QR</p>
                <img src="assets/qr.png" alt="Código QR">
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Chifa Matsue. Todos los derechos reservados.</p>
            <p class="footer-dev">Desarrollado con ❤️ por el equipo de desarrollo</p>
        </div>
    </div>
</footer>
```

## 🎨 VARIABLES CSS PERSONALIZADAS

```css
/* En tu archivo CSS personalizado */
:root {
    /* Sobrescribir colores si es necesario */
    --gold: #d4af37;        /* Dorado más brillante */
    --gold-light: #f0d98d;
    --gold-dark: #b8941f;
}

/* Usar variables en tus estilos */
.mi-elemento {
    background: var(--gold);
    color: var(--primary);
    border: 2px solid var(--gold-dark);
}
```

## 🎯 CLASES UTILITARIAS

```html
<!-- Espaciado -->
<div style="margin-top: 48px;">...</div>
<div style="padding: 24px;">...</div>

<!-- Texto centrado -->
<p style="text-align: center;">Texto centrado</p>

<!-- Fondo semitransparente -->
<div style="background: rgba(255, 255, 255, 0.95);">
    Contenido con fondo blanco semitransparente
</div>

<!-- Sombra -->
<div style="box-shadow: var(--shadow);">...</div>
<div style="box-shadow: var(--shadow-lg);">...</div>

<!-- Border radius -->
<div style="border-radius: var(--radius);">...</div>
<div style="border-radius: var(--radius-lg);">...</div>
```

## 🚀 INTEGRACIÓN CON JAVASCRIPT

```javascript
// Cambiar color de botón dinámicamente
const btn = document.querySelector('.btn-primary');
btn.style.background = 'var(--gold-light)';

// Agregar clase de hover
btn.addEventListener('mouseenter', () => {
    btn.style.transform = 'translateY(-2px)';
});

// Actualizar contador del carrito
const cartCount = document.getElementById('cartCount');
cartCount.textContent = '5';
cartCount.style.background = 'var(--gold)';
```

---

**Nota:** Todos estos ejemplos respetan el diseño oscuro premium migrado y son compatibles con la funcionalidad existente del proyecto.
