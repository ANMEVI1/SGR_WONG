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
        
        // Cargar secciones del menú
        await Promise.all([
            this.cargarSeccion('todos', document.getElementById('menuGrid'), 'card'),
            this.cargarSeccion('top', document.querySelector('#top-ventas .mini-carousel'), 'mini'),
            this.cargarSeccion('promo', document.querySelector('#promociones .mini-carousel'), 'mini'),
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
        const promo = plato.promo ? '<span class=\"promo-tag\">PROMO</span>' : '';
        const precio = plato.tiene_variantes 
            ? `Desde S/ ${plato.precio.toFixed(2)}` 
            : `S/ ${plato.precio.toFixed(2)}`;
            
        return `
            <article class=\"menu-item\" onclick=\"app.verDetalle(${plato.id})\" role=\"button\" tabindex=\"0\">
                <img src=\"${plato.imagen}\" alt=\"${plato.nombre}\" loading=\"lazy\"
                     onerror=\"this.src='assets/img/platos/default.jpg'\">
                <div class=\"menu-item-content\">
                    <h3>${plato.nombre} ${promo}</h3>
                    <p>${plato.descripcion}</p>
                    <div class=\"menu-item-price\">${precio}</div>
                    <button onclick=\"event.stopPropagation(); app.addToCart(${plato.id}, '${plato.nombre}', ${plato.precio}, '${plato.imagen}')\"
                            class=\"btn btn-primary btn-small btn-full\"
                            aria-label=\"Añadir ${plato.nombre} al carrito\">
                        Añadir al Carrito
                    </button>
                </div>
            </article>`;
    }
    
    miniCardHTML(plato) {
        return `
            <div class=\"mini-item\" onclick=\"app.verDetalle(${plato.id})\" role=\"button\" tabindex=\"0\">
                <img src=\"${plato.imagen}\" alt=\"${plato.nombre}\" loading=\"lazy\"
                     onerror=\"this.src='assets/img/platos/default.jpg'\">
                <p>${plato.nombre}</p>
                <span>S/ ${plato.precio.toFixed(2)}</span>
            </div>`;
    }
    
    skeletonHTML(count = 4) {
        return Array(count).fill('<div class=\"menu-item skeleton\"></div>').join('');
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
                        <a href=\"producto-detalle.html?id=${plato.id}\" class=\"search-result-item\">
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
        window.location.href = `producto-detalle.html?id=${id}`;
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