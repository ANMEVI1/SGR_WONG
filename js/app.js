/**
 * App.js - Funcionalidades principales del sitio web
 * Combina carrusel, menú, carrito y notificaciones
 */

class ChifaApp {
    constructor() {
        this.currentSlide = 0;
        this.carouselInterval = null;
        this.searchTimeout = null;
        this.todosLosPlatos = [];
        this.carrito = this.getCarrito();
        
        this.init();
    }
    
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initCarousel();
            this.initMenu();
            this.initSearch();
            this.initCarrito();
            this.setupNotifications();
        });
    }
    
    // ====================== CARRUSEL ======================
    initCarousel() {
        const slides = document.querySelectorAll('.carousel-slide');
        if (!slides.length) return;
        
        this.slides = slides;
        this.showSlide(0);
        this.startCarousel();
        
        // Event listeners para navegación
        document.querySelector('.next')?.addEventListener('click', () => {
            this.showSlide(this.currentSlide + 1);
            this.startCarousel();
        });
        
        document.querySelector('.prev')?.addEventListener('click', () => {
            this.showSlide(this.currentSlide - 1);
            this.startCarousel();
        });
        
        // Pausar en hover
        const carousel = document.querySelector('.hero-carousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', () => this.stopCarousel());
            carousel.addEventListener('mouseleave', () => this.startCarousel());
        }
    }
    
    showSlide(n) {
        if (!this.slides?.length) return;
        this.currentSlide = (n + this.slides.length) % this.slides.length;
        this.slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === this.currentSlide);
        });
    }
    
    startCarousel() {
        this.stopCarousel();
        this.carouselInterval = setInterval(() => {
            this.showSlide(this.currentSlide + 1);
        }, 6000);
    }
    
    stopCarousel() {
        if (this.carouselInterval) {
            clearInterval(this.carouselInterval);
            this.carouselInterval = null;
        }
    }
    
    // ====================== MENÚ ======================
    async initMenu() {
        this.actualizarContadorCarrito();
        
        // Validar horario para Menú del Día
        this.checkMenuDiaHorario();
        
        // Cargar secciones del menú
        await Promise.all([
            this.cargarSeccion('todos', document.getElementById('menuGrid'), 'card'),
            this.cargarSeccion('top', document.querySelector('#top-ventas .scroll-track'), 'mini'),
            this.cargarSeccion('promo', document.querySelector('#promociones .scroll-track'), 'mini'),
            this.cargarMenuDia(),
            this.iniciarBusqueda()
        ]);
    }
    
    async cargarSeccion(filtro, contenedor, tipo = 'card') {
        if (!contenedor) return;
        
        // Mostrar skeleton loading
        contenedor.innerHTML = this.skeletonHTML(tipo === 'mini' ? 4 : 8);
        
        try {
            const response = await fetch(`api/menu.php?filtro=${encodeURIComponent(filtro)}`);
            const data = await response.json();
            
            if (!data.success || !data.data?.length) {
                contenedor.innerHTML = '<p class=\"empty-menu\">No hay platos disponibles.</p>';
                return;
            }
            
            contenedor.innerHTML = tipo === 'mini'
                ? data.data.map(plato => this.miniCardHTML(plato)).join('')
                : data.data.map(plato => this.cardHTML(plato)).join('');
                
        } catch (error) {
            console.error('Error cargando menú:', error);
            contenedor.innerHTML = '<p class=\"empty-menu\">Error al cargar el menú.</p>';
        }
    }
    
    cardHTML(plato) {
        const promo = plato.promo ? '<span class="badge badge-promo">PROMO</span>' : '';
        const precio = plato.tiene_variantes 
            ? `Desde S/ ${plato.precio.toFixed(2)}` 
            : `S/ ${plato.precio.toFixed(2)}`;
        const imagenSrc = plato.imagen || 'assets/img/platos/plato_default.png';
        const nombreSeguro = (plato.nombre || '').replace(/'/g, '&#39;');
        const descripcionSegura = (plato.descripcion || '').replace(/'/g, '&#39;');
            
        return `
            <article class="menu-item" onclick="app.verDetalle(${plato.id})" role="button" tabindex="0">
                <div class="menu-item-img-wrap">
                    <img src="${imagenSrc}" alt="${nombreSeguro}" loading="lazy"
                         onerror="this.style.display='none';">
                    <span class="menu-item-cat-badge">${plato.categoria || 'Sin categoría'}</span>
                </div>
                <div class="menu-item-content">
                    <h3>${nombreSeguro} ${promo}</h3>
                    <p>${descripcionSegura}</p>
                    <div class="menu-item-footer">
                        <div class="menu-item-price">${precio}</div>
                    </div>
                    <button onclick="event.stopPropagation(); app.addToCart(${plato.id}, \`${nombreSeguro}\`, ${plato.precio}, \`${imagenSrc}\`)" class="btn btn-primary btn-small btn-full" aria-label="Añadir al carrito">
                        <i class="fas fa-shopping-cart"></i> Añadir
                    </button>
                </div>
            </article>`;
    }
    
    miniCardHTML(plato) {
        const promo = plato.promo ? '<span class="promo-badge">PROMO</span>' : '';
        const topBadge = plato.top ? '<span class="top-card-rank">TOP</span>' : '';
        const imagenSrc = plato.imagen || 'assets/img/platos/plato_default.png';
        const nombreSeguro = (plato.nombre || '').replace(/'/g, '&#39;');
        const descripcionSegura = (plato.descripcion || 'Delicioso plato de nuestra carta').replace(/'/g, '&#39;');
        
        return `
            <div class="top-card" onclick="app.verDetalle(${plato.id})" role="button" tabindex="0">
                <div class="top-card-img">
                    <img src="${imagenSrc}" alt="${nombreSeguro}" loading="lazy"
                         onerror="this.style.display='none';">
                    ${topBadge}
                </div>
                <div class="top-card-body">
                    <h4>${nombreSeguro} ${promo}</h4>
                    <p>${descripcionSegura}</p>
                    <div class="top-card-footer">
                        <div class="top-card-price">S/ ${plato.precio.toFixed(2)}</div>
                        <button class="top-card-add" onclick="event.stopPropagation(); app.addToCart(${plato.id}, \`${nombreSeguro}\`, ${plato.precio}, \`${imagenSrc}\`)" aria-label="Añadir al carrito">
                            +
                        </button>
                    </div>
                </div>
            </div>`;
    }
    
    skeletonHTML(count = 4) {
        return Array(count).fill('<div class=\"menu-item skeleton\"></div>').join('');
    }
    
    // ====================== MENÚ DEL DÍA ======================
    checkMenuDiaHorario() {
        const ahora = new Date();
        const hora = ahora.getHours();
        
        const seccion = document.getElementById('menu-dia');
        if (!seccion) return;
        
        // Mostrar solo entre 12pm (12) y 4pm (16)
        if (hora >= 12 && hora < 16) {
            seccion.style.display = 'block';
        } else {
            seccion.style.display = 'none';
        }
        
        // Revisar cada hora para actualizar
        setTimeout(() => this.checkMenuDiaHorario(), 60 * 60 * 1000);
    }
    
    async cargarMenuDia() {
        const contenedor = document.getElementById('menuDiaGrid');
        if (!contenedor) return;
        
        contenedor.innerHTML = this.skeletonHTML(3);
        
        try {
            const response = await fetch('api/menu-dia.php');
            const data = await response.json();
            
            if (!data.success || !data.data?.length) {
                contenedor.innerHTML = '<p class="empty-menu">No hay menús del día disponibles.</p>';
                return;
            }
            
            contenedor.innerHTML = data.data.map(plato => this.menuDiaCardHTML(plato)).join('');
            
        } catch (error) {
            console.error('Error cargando menú del día:', error);
            contenedor.innerHTML = '<p class="empty-menu">Error al cargar los menús.</p>';
        }
    }
    
    menuDiaCardHTML(plato) {
        const imagenSrc = plato.imagen || 'assets/img/platos/plato_default.png';
        const nombreSeguro = (plato.nombre || '').replace(/'/g, '&#39;');
        const descripcionSegura = (plato.descripcion || 'Delicioso plato de nuestra carta').replace(/'/g, '&#39;');
        
        return `
            <div class="mini-item" onclick="app.verDetalle(${plato.id})" role="button" tabindex="0">
                <img src="${imagenSrc}" alt="${nombreSeguro}" loading="lazy"
                     onerror="this.style.display='none';">
                <div style="padding: 20px;">
                    <p style="font-weight: 700; font-size: 1rem; color: var(--text); margin-bottom: 8px;">${nombreSeguro}</p>
                    <p style="font-size: 0.85rem; color: #888; line-height: 1.55; margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${descripcionSegura}</p>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid #f0ece4;">
                        <span style="font-size: 1.3rem; font-weight: 800; color: var(--gold-dark);">S/ ${plato.precio.toFixed(2)}</span>
                        <button class="top-card-add" onclick="event.stopPropagation(); app.addToCart(${plato.id}, \`${nombreSeguro}\`, ${plato.precio}, \`${imagenSrc}\`)" aria-label="Añadir al carrito">
                            +
                        </button>
                    </div>
                    <small style="color: #999; font-size: 0.7rem; display: block; margin-top: 8px; text-align: center;">
                        12:00pm - 4:00pm
                    </small>
                </div>
            </div>`;
    }
    

    
    // ====================== BÚSQUEDA ======================
    initSearch() {
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        
        if (!searchInput || !searchResults) return;
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            const term = e.target.value.toLowerCase().trim();
            
            if (!term) {
                searchResults.classList.remove('active');
                return;
            }
            
            this.searchTimeout = setTimeout(() => {
                const resultados = this.buscarPlatos(term).slice(0, 8);
                searchResults.innerHTML = resultados.length
                    ? resultados.map(plato => `
                        <a href=\"detalle-plato.php?id=${plato.id}\" class=\"search-result-item\">
                            <strong>${plato.nombre}</strong> — S/ ${plato.precio.toFixed(2)}
                        </a>`).join('')
                    : '<div class=\"no-results\">Sin resultados</div>';
                searchResults.classList.add('active');
            }, 300);
        });
        
        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('active');
            }
        });
        
        // Navegación con teclado
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchResults.classList.remove('active');
                searchInput.blur();
            }
        });
    }
    
    async iniciarBusqueda() {
        try {
            const response = await fetch('api/menu.php?filtro=todos');
            const data = await response.json();
            if (data.success) {
                this.todosLosPlatos = data.data;
            }
        } catch (error) {
            console.error('Error cargando datos para búsqueda:', error);
        }
    }
    
    buscarPlatos(term) {
        return this.todosLosPlatos.filter(plato =>
            plato.nombre.toLowerCase().includes(term) ||
            plato.categoria.toLowerCase().includes(term) ||
            (plato.descripcion || '').toLowerCase().includes(term)
        );
    }
    
    // ====================== CARRITO ======================
    initCarrito() {
        this.actualizarContadorCarrito();
    }
    
    getCarrito() {
        try {
            return JSON.parse(localStorage.getItem('carrito')) || [];
        } catch {
            return [];
        }
    }
    
    saveCarrito() {
        try {
            localStorage.setItem('carrito', JSON.stringify(this.carrito));
        } catch (error) {
            console.error('Error guardando carrito:', error);
        }
    }
    
    addToCart(id, nombre, precio, imagen) {
        const item = { id, nombre, precio: parseFloat(precio), imagen };
        this.carrito.push(item);
        this.saveCarrito();
        this.actualizarContadorCarrito();
        this.mostrarNotificacion(`${nombre} añadido al carrito`, 'success');
    }
    
    removeFromCart(index) {
        if (index >= 0 && index < this.carrito.length) {
            const item = this.carrito.splice(index, 1)[0];
            this.saveCarrito();
            this.actualizarContadorCarrito();
            this.mostrarNotificacion(`${item.nombre} eliminado del carrito`, 'info');
        }
    }
    
    clearCart() {
        this.carrito = [];
        this.saveCarrito();
        this.actualizarContadorCarrito();
        this.mostrarNotificacion('Carrito vaciado', 'info');
    }
    
    actualizarContadorCarrito() {
        const contador = document.getElementById('cartCount');
        if (contador) {
            contador.textContent = this.carrito.length;
        }
    }
    
    verDetalle(id) {
        window.location.href = `detalle-plato.php?id=${id}`;
    }
    
    // ====================== NOTIFICACIONES ======================
    setupNotifications() {
        // Agregar estilos para animaciones
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideInRight { 
                    from { transform: translateX(400px); opacity: 0; } 
                    to { transform: translateX(0); opacity: 1; } 
                }
                @keyframes slideOutRight { 
                    from { transform: translateX(0); opacity: 1; } 
                    to { transform: translateX(400px); opacity: 0; } 
                }
                .notification {
                    position: fixed;
                    top: 100px;
                    right: 20px;
                    padding: 16px 24px;
                    border-radius: 12px;
                    color: white;
                    font-weight: 600;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    z-index: 10000;
                    animation: slideInRight 0.3s ease;
                    max-width: 300px;
                    word-wrap: break-word;
                }
                .notification.success { background: #10b981; }
                .notification.error { background: #ef4444; }
                .notification.info { background: #3b82f6; }
                .notification.warning { background: #f59e0b; }
            `;
            document.head.appendChild(style);
        }
    }
    
    mostrarNotificacion(mensaje, tipo = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${tipo}`;
        notification.textContent = mensaje;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // ====================== CLEANUP ======================
    destroy() {
        this.stopCarousel();
        clearTimeout(this.searchTimeout);
    }
}

// Inicializar aplicación
const app = new ChifaApp();

// Cleanup al salir
window.addEventListener('beforeunload', () => {
    app.destroy();
});

// Exponer funciones globales para compatibilidad
window.mostrarNotificacion = (mensaje, tipo) => app.mostrarNotificacion(mensaje, tipo);
window.addToCart = (id, nombre, precio, imagen) => app.addToCart(id, nombre, precio, imagen);
window.verDetalle = (id) => app.verDetalle(id);