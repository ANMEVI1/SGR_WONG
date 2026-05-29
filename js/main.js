// js/main.js — Carrusel + notificaciones (platos → menu.js)

// ====================== CARRUSEL ======================
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
let carouselInterval;

function showSlide(n) {
    if (!slides.length) return;
    currentSlide = (n + slides.length) % slides.length;
    slides.forEach((s, i) => s.classList.toggle('active', i === currentSlide));
}

function startCarousel() {
    stopCarousel();
    carouselInterval = setInterval(() => showSlide(currentSlide + 1), 6000);
}

function stopCarousel() {
    clearInterval(carouselInterval);
}

document.querySelector('.next')?.addEventListener('click', () => { showSlide(currentSlide + 1); startCarousel(); });
document.querySelector('.prev')?.addEventListener('click', () => { showSlide(currentSlide - 1); startCarousel(); });

// ====================== NOTIFICACIONES ======================
function mostrarNotificacion(mensaje, tipo = 'info') {
    const notif = document.createElement('div');
    notif.textContent = mensaje;
    notif.style.cssText = `
        position:fixed;top:100px;right:20px;
        background:${tipo === 'success' ? '#10b981' : tipo === 'error' ? '#ef4444' : '#3b82f6'};
        color:#fff;padding:16px 24px;border-radius:12px;
        box-shadow:0 10px 30px rgba(0,0,0,.2);z-index:10000;
        font-weight:600;animation:slideIn .3s ease;`;
    document.body.appendChild(notif);
    setTimeout(() => { notif.style.animation = 'slideOut .3s ease'; setTimeout(() => notif.remove(), 300); }, 3000);
}

const _style = document.createElement('style');
_style.textContent = `
    @keyframes slideIn  { from { transform:translateX(400px);opacity:0 } to { transform:translateX(0);opacity:1 } }
    @keyframes slideOut { from { transform:translateX(0);opacity:1 } to { transform:translateX(400px);opacity:0 } }`;
document.head.appendChild(_style);

// ====================== INIT ======================
document.addEventListener('DOMContentLoaded', () => {
    if (slides.length) { showSlide(0); startCarousel(); }
});

window.addEventListener('beforeunload', stopCarousel);
window.mostrarNotificacion = mostrarNotificacion;
