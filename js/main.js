// js/main.js - Versión mejorada y optimizada

// ====================== DATOS Y ESTADO ======================
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

const platos = [
    // Sopas
    { 
        id: 1, 
        nombre: "Taypa a la plancha", 
        precio: 18.00, 
        stock: 22, 
        categoria: "sopas", 
        top: true, 
        promo: false, 
        img: "taypa_plancha .jpg", 
        desc: "Sopa tradicional con fideos, verduras y condimentos chinos." 
    },
    { 
        id: 2, 
        nombre: "Sopa Wantan", 
        precio: 16.00, 
        stock: 18, 
        categoria: "sopas", 
        top: false, 
        promo: false, 
        img: "Sopa-wantan-768x510.jpg", 
        desc: "Sopa wantan con pollo o wantan frito." 
    },

    // Chaufas
    { 
        id: 3, 
        nombre: "Chaufa de Carne", 
        precio: 18.00, 
        stock: 20, 
        categoria: "chaufa", 
        top: true, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1543353071-873f17a7a088?auto=format&fit=crop&w=800&q=80", 
        desc: "Chaufa de carne al estilo chifa." 
    },
    { 
        id: 4, 
        nombre: "Chaufa de Pollo", 
        precio: 15.00, 
        stock: 25, 
        categoria: "chaufa", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1495195134817-aeb325a55b65?auto=format&fit=crop&w=800&q=80", 
        desc: "Chaufa con pollo sazonado y arroz frito." 
    },
    { 
        id: 5, 
        nombre: "Chaufa Tumbesino", 
        precio: 19.00, 
        stock: 14, 
        categoria: "chaufa", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1528715471579-d3616b752a5f?auto=format&fit=crop&w=800&q=80", 
        desc: "Chaufa tumbesino con ingredientes especiales." 
    },

    // Tallarines
    { 
        id: 6, 
        nombre: "Tallarín con Carne", 
        precio: 19.00, 
        stock: 18, 
        categoria: "tallarines", 
        top: true, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1512058564366-c9e4a3f4d9e3?auto=format&fit=crop&w=800&q=80", 
        desc: "Tallarín salteado con carne y verduras." 
    },
    { 
        id: 7, 
        nombre: "Tallarín con Chancho", 
        precio: 20.00, 
        stock: 15, 
        categoria: "tallarines", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1490645935967-10de6ba17061?auto=format&fit=crop&w=800&q=80", 
        desc: "Tallarín con chancho y verduras al estilo chino." 
    },

    // Pollo
    { 
        id: 8, 
        nombre: "Pollo con Verduras", 
        precio: 19.00, 
        stock: 16, 
        categoria: "pollo", 
        top: true, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80", 
        desc: "Pollo salteado con verduras y salsa especial." 
    },
    { 
        id: 9, 
        nombre: "Pollo Malakay", 
        precio: 19.00, 
        stock: 20, 
        categoria: "pollo", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1514516870926-123dc2e3d2d5?auto=format&fit=crop&w=800&q=80", 
        desc: "Pollo malakay con salsa picante suave." 
    },

    // Lomo
    { 
        id: 10, 
        nombre: "Lomo Saltado con Pollo", 
        precio: 18.00, 
        stock: 10, 
        categoria: "lomo", 
        top: true, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1495195129352-a54dab37b8e2?auto=format&fit=crop&w=800&q=80", 
        desc: "Lomo saltado con pollo y verduras." 
    },
    { 
        id: 11, 
        nombre: "Lomo Saltado con Carne", 
        precio: 19.00, 
        stock: 8, 
        categoria: "lomo", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80", 
        desc: "Lomo saltado con carne y salsa especial." 
    },

    // Especiales
    { 
        id: 12, 
        nombre: "Kaluw Wantan", 
        precio: 20.00, 
        stock: 12, 
        categoria: "especiales", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1515496642597-7ddab3f23fdc?auto=format&fit=crop&w=800&q=80", 
        desc: "Wantan crujiente con salsa kaluw." 
    },
    { 
        id: 13, 
        nombre: "Taypa con Chaufa", 
        precio: 22.00, 
        stock: 10, 
        categoria: "especiales", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80", 
        desc: "Taypa servido con chaufa y verduras." 
    },

    // Tortillas y combinados
    { 
        id: 14, 
        nombre: "Tortilla con Pollo", 
        precio: 20.00, 
        stock: 14, 
        categoria: "tortillas", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80", 
        desc: "Tortilla rellena de pollo y verduras." 
    },
    { 
        id: 15, 
        nombre: "Combinado de Chancho", 
        precio: 20.00, 
        stock: 16, 
        categoria: "combinados", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80", 
        desc: "Combinado de chancho con arroz y ensalada." 
    },
    { 
        id: 16, 
        nombre: "Combinado de Carne", 
        precio: 19.00, 
        stock: 18, 
        categoria: "combinados", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80", 
        desc: "Combinado de carne con arroz chaufa." 
    },

    // Langostinos y pescado
    { 
        id: 17, 
        nombre: "Langostino con Verdura", 
        precio: 23.00, 
        stock: 12, 
        categoria: "langostinos", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1512058564366-c9e4a3f4d9e3?auto=format&fit=crop&w=800&q=80", 
        desc: "Langostino salteado con verduras." 
    },
    { 
        id: 18, 
        nombre: "Pescado con Tausi", 
        precio: 22.00, 
        stock: 10, 
        categoria: "pescados", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1490647111677-014db1b52f4c?auto=format&fit=crop&w=800&q=80", 
        desc: "Pescado con salsa tausí y verduras." 
    },
    { 
        id: 19, 
        nombre: "Pescado con Tamarindo", 
        precio: 22.00, 
        stock: 10, 
        categoria: "pescados", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80", 
        desc: "Pescado con salsa de tamarindo." 
    },

    // Bebidas
    { 
        id: 20, 
        nombre: "Cerveza Cusqueña", 
        precio: 10.00, 
        stock: 40, 
        categoria: "bebidas", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1532634896-26909d0d3b0b?auto=format&fit=crop&w=800&q=80", 
        desc: "Cerveza fría ideal para acompañar tu chifa." 
    },
    { 
        id: 21, 
        nombre: "Agua Mineral", 
        precio: 4.00, 
        stock: 60, 
        categoria: "bebidas", 
        top: false, 
        promo: false, 
        img: "https://images.unsplash.com/photo-1524594154909-458d3c7cc973?auto=format&fit=crop&w=800&q=80", 
        desc: "Agua sin gas 500ml para refrescar." 
    }
];

// ====================== DOM ELEMENTS ======================
const menuGrid = document.getElementById('menuGrid');
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const menuToggle = document.querySelector('.menu-toggle');
const closeSidebar = document.querySelector('.close-sidebar');
const cartCount = document.getElementById('cartCount');

// ====================== CARRUSEL PRINCIPAL ======================
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
let carouselInterval;

function showSlide(n) {
    if (slides.length === 0) return;
    
    currentSlide = (n + slides.length) % slides.length;
    
    slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === currentSlide);
    });
}

function nextSlide() {
    showSlide(currentSlide + 1);
}

function prevSlide() {
    showSlide(currentSlide - 1);
}

function startCarousel() {
    stopCarousel();
    carouselInterval = setInterval(nextSlide, 6000);
}

function stopCarousel() {
    if (carouselInterval) {
        clearInterval(carouselInterval);
    }
}

// Event listeners para controles del carrusel
const nextBtn = document.querySelector('.next');
const prevBtn = document.querySelector('.prev');

if (nextBtn) {
    nextBtn.addEventListener('click', () => {
        nextSlide();
        startCarousel(); // Reiniciar el intervalo
    });
}

if (prevBtn) {
    prevBtn.addEventListener('click', () => {
        prevSlide();
        startCarousel(); // Reiniciar el intervalo
    });
}

// ====================== RENDER FUNCIONES ======================
function getImageSrc(img) {
    return img.startsWith('http://') || img.startsWith('https://') ? img : `assets/${img}`;
}

function renderMenu() {
    if (!menuGrid) return;
    
    menuGrid.innerHTML = platos.map(plato => {
        const stockClass = plato.stock === 0 ? 'btn-stock-disabled' : '';
        const stockText = plato.stock === 0 ? 'Sin Stock' : 'Añadir al Carrito';
        const promoTag = plato.promo ? '<span class="promo-tag">PROMO</span>' : '';
        
        return `
            <article class="menu-item" onclick="verDetalle(${plato.id})">
                <img src="${getImageSrc(plato.img)}" alt="${plato.nombre}" loading="lazy">
                <div class="menu-item-content">
                    <h3>
                        ${plato.nombre} 
                        ${promoTag}
                    </h3>
                    <p>${plato.desc}</p>
                    <div class="stock-info">
                        Stock: <strong>${plato.stock > 0 ? plato.stock + ' disponibles' : 'Agotado'}</strong>
                    </div>
                    <div class="menu-item-price">S/ ${plato.precio.toFixed(2)}</div>
                    <button 
                        ${plato.stock === 0 ? 'disabled' : ''} 
                        onclick="event.stopPropagation(); addToCart(${plato.id})" 
                        class="btn btn-primary btn-small btn-full ${stockClass}">
                        ${stockText}
                    </button>
                </div>
            </article>
        `;
    }).join('');
}

function renderTopVentas() {
    const top = platos.filter(p => p.top);
    const container = document.querySelector('#top-ventas .mini-carousel');
    
    if (container && top.length > 0) {
        container.innerHTML = top.map(p => `
            <div class="mini-item" onclick="verDetalle(${p.id})">
                <img src="${getImageSrc(p.img)}" alt="${p.nombre}" loading="lazy">
                <p>${p.nombre}</p>
                <span>S/ ${p.precio.toFixed(2)}</span>
            </div>
        `).join('');
    }
}

function renderPromociones() {
    const promo = platos.filter(p => p.promo);
    const container = document.querySelector('#promociones .mini-carousel');
    
    if (container && promo.length > 0) {
        container.innerHTML = promo.map(p => `
            <div class="mini-item promo" onclick="verDetalle(${p.id})">
                <img src="${getImageSrc(p.img)}" alt="${p.nombre}" loading="lazy">
                <p>${p.nombre} <strong>¡Oferta!</strong></p>
                <span>S/ ${p.precio.toFixed(2)}</span>
            </div>
        `).join('');
    }
}

// ====================== BÚSQUEDA ======================
let searchTimeout;

if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        
        const term = e.target.value.toLowerCase().trim();
        
        if (term === '') {
            searchResults.classList.remove('active');
            return;
        }

        searchTimeout = setTimeout(() => {
            const resultados = platos.filter(p => 
                p.nombre.toLowerCase().includes(term) || 
                p.categoria.toLowerCase().includes(term) ||
                p.desc.toLowerCase().includes(term)
            );

            if (resultados.length > 0) {
                searchResults.innerHTML = resultados.slice(0, 8).map(p => `
                    <a href="producto-detalle.html?id=${p.id}">
                        <strong>${p.nombre}</strong> - S/ ${p.precio.toFixed(2)}
                        ${p.stock === 0 ? '<em style="color: #999;"> (Sin stock)</em>' : ''}
                    </a>
                `).join('');
            } else {
                searchResults.innerHTML = '<div style="padding: 14px 18px; color: #999;">No se encontraron resultados</div>';
            }
            
            searchResults.classList.add('active');
        }, 300);
    });
}

// Cerrar resultados al hacer click fuera
document.addEventListener('click', (e) => {
    if (searchInput && searchResults && 
        !searchInput.contains(e.target) && 
        !searchResults.contains(e.target)) {
        searchResults.classList.remove('active');
    }
});

// ====================== SIDEBAR ======================
function toggleSidebar() {
    const isActive = sidebar.classList.contains('active');
    
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
    document.body.style.overflow = isActive ? '' : 'hidden';
}

if (menuToggle) menuToggle.addEventListener('click', toggleSidebar);
if (closeSidebar) closeSidebar.addEventListener('click', toggleSidebar);
if (overlay) overlay.addEventListener('click', toggleSidebar);

// Cerrar sidebar con tecla ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && sidebar.classList.contains('active')) {
        toggleSidebar();
    }
});

// ====================== CARRITO ======================
function addToCart(id) {
    const plato = platos.find(p => p.id === id);
    
    if (!plato) {
        console.error('Plato no encontrado');
        return;
    }
    
    if (plato.stock <= 0) {
        mostrarNotificacion('Este producto está agotado', 'error');
        return;
    }
    
    // Agregar al carrito
    carrito.push({ ...plato, cantidadComprada: 1 });
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    // Reducir stock
    plato.stock--;
    
    // Actualizar vista
    updateCartCount();
    renderMenu();
    renderTopVentas();
    renderPromociones();
    
    mostrarNotificacion(`${plato.nombre} añadido al carrito`, 'success');
}

function updateCartCount() {
    if (cartCount) {
        const total = carrito.length;
        cartCount.textContent = total;
        
        // Animación del contador
        if (total > 0) {
            cartCount.style.transform = 'scale(1.3)';
            setTimeout(() => {
                cartCount.style.transform = 'scale(1)';
            }, 200);
        }
    }
}

function verDetalle(id) {
    window.location.href = `producto-detalle.html?id=${id}`;
}

// ====================== NOTIFICACIONES ======================
function mostrarNotificacion(mensaje, tipo = 'info') {
    // Crear elemento de notificación
    const notif = document.createElement('div');
    notif.className = `notificacion notificacion-${tipo}`;
    notif.textContent = mensaje;
    
    // Estilos inline para la notificación
    notif.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${tipo === 'success' ? '#10b981' : tipo === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-weight: 600;
    `;
    
    document.body.appendChild(notif);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notif.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

// Agregar estilos de animación para notificaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ====================== INICIALIZACIÓN ======================
document.addEventListener('DOMContentLoaded', () => {
    // Actualizar contador del carrito
    updateCartCount();
    
    // Renderizar todas las secciones
    renderMenu();
    renderTopVentas();
    renderPromociones();
    
    // Iniciar carrusel
    if (slides.length > 0) {
        showSlide(currentSlide);
        startCarousel();
    }
    
    // Log de inicialización
    console.log('Chifa Matsue cargado correctamente');
    console.log(`Productos en carrito: ${carrito.length}`);
});

// Limpiar intervalo al salir de la página
window.addEventListener('beforeunload', () => {
    stopCarousel();
});