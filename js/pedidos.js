// js/pedido.js
document.addEventListener('DOMContentLoaded', () => {
    verificarSesion();
    cargarResumenCarrito();
});

function verificarSesion() {
    const usuario = JSON.parse(localStorage.getItem('usuario'));
    if (!usuario) {
        alert('Debes iniciar sesión para completar el pedido');
        window.location.href = 'login.html?redirect=pedido.html';
        return;
    }
    document.querySelector('.section-title').textContent += `, ${usuario.nombre.split(' ')[0]}!`;
}

function cargarResumenCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    if (carrito.length === 0) {
        alert('Tu carrito está vacío');
        window.location.href = 'carrito.html';
        return;
    }

    const resumen = document.createElement('div');
    resumen.className = 'order-summary';
    resumen.innerHTML = `
        <h3>Resumen del Pedido</h3>
        ${carrito.map(item => `
            <div class="order-item">
                <span>${item.nombre}</span>
                <span>S/ ${item.precio.toFixed(2)}</span>
            </div>
        `).join('')}
        <div class="total-line"><strong>Total: S/ ${calcularTotal().toFixed(2)}</strong></div>
    `;
    document.querySelector('.order-form').insertBefore(resumen, document.querySelector('.order-form button'));
}

function calcularTotal() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    return carrito.reduce((sum, item) => sum + item.precio, 0) + 8.00;
}

document.getElementById('formPedido').addEventListener('submit', function(e) {
    e.preventDefault();

    const metodoPago = document.querySelector('input[name="pago"]:checked');
    const comprobante = document.querySelector('input[name="comprobante"]:checked');

    if (!metodoPago || !comprobante) {
        alert('Por favor selecciona método de pago y tipo de comprobante');
        return;
    }

    const pedido = {
        fecha: new Date().toLocaleString(),
        items: JSON.parse(localStorage.getItem('carrito')),
        total: calcularTotal(),
        cliente: JSON.parse(localStorage.getItem('usuario')),
        entrega: {
            nombre: this.querySelector('input[type="text"]').value,
            telefono: this.querySelector('input[type="tel"]').value,
            direccion: this.querySelectorAll('input[type="text"]')[2].value,
        },
        pago: metodoPago.value,
        comprobante: comprobante.value
    };

    // Simular envío exitoso
    alert(`¡Pedido confirmado!\nTotal: S/ ${pedido.total.toFixed(2)}\nMétodo: ${pedido.pago.toUpperCase()}`);
    
    localStorage.removeItem('carrito');
    localStorage.setItem('ultimoPedido', JSON.stringify(pedido));
    
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 2000);
});