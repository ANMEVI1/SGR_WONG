// js/carrito.js
const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
const carritoItemsContainer = document.getElementById('carritoItems');
const subtotalElement = document.getElementById('subtotal');
const totalElement = document.getElementById('total');
const btnPagar = document.getElementById('btnPagar');

document.addEventListener('DOMContentLoaded', () => {
    renderCarrito();
    actualizarResumen();
    setupEventListeners();
    actualizarContadorCarrito(); // Actualiza el header
});

function renderCarrito() {
    if (carrito.length === 0) {
        carritoItemsContainer.innerHTML = `
            <div class="carrito-vacio">
                <i class="fas fa-shopping-cart fa-3x"></i>
                <p>Tu carrito está vacío</p>
                <a href="index.html" class="btn btn-primary">Explorar Menú</a>
            </div>`;
        btnPagar.disabled = true;
        return;
    }

    btnPagar.disabled = false;

    carritoItemsContainer.innerHTML = carrito.map((item, index) => `
        <div class="carrito-item">
            <img src="assets/${item.img || 'default.jpg'}" alt="${item.nombre}">
            <div class="item-info">
                <h4>${item.nombre}</h4>
                <p class="precio">S/ ${item.precio.toFixed(2)}</p>
            </div>
            <button class="btn-eliminar" onclick="eliminarDelCarrito(${index})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');
}

function eliminarDelCarrito(index) {
    carrito.splice(index, 1);
    localStorage.setItem('carrito', JSON.stringify(carrito));
    renderCarrito();
    actualizarResumen();
    actualizarContadorCarrito();
}

function actualizarResumen() {
    const subtotal = carrito.reduce((sum, item) => sum + item.precio, 0);
    const delivery = carrito.length > 0 ? 8.00 : 0;
    const total = subtotal + delivery;

    subtotalElement.textContent = `S/ ${subtotal.toFixed(2)}`;
    totalElement.textContent = `S/ ${total.toFixed(2)}`;
}

function setupEventListeners() {
    btnPagar.addEventListener('click', () => {
        const usuario = JSON.parse(localStorage.getItem('usuario'));
        if (!usuario) {
            alert('Debes iniciar sesión para continuar con el pago');
            window.location.href = 'login.html?redirect=pedido.html';
        } else {
            window.location.href = 'pedido.html';
        }
    });
}

function actualizarContadorCarrito() {
    const contador = document.getElementById('cartCount');
    if (contador) contador.textContent = carrito.length;
}

// Exportar para uso global si es necesario
window.eliminarDelCarrito = eliminarDelCarrito;