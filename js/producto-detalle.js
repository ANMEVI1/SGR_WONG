// js/producto-detalle.js
const urlParams = new URLSearchParams(window.location.search);
const productoId = parseInt(urlParams.get('id'));

// Datos de ejemplo (debes tener esto en un archivo data.js o importarlo)
const platos = [
    { id: 1, nombre: "Hamburguesa Clásica", precio: 25.90, stock: 10, desc: "Carne 100% res, lechuga, tomate y salsa especial.", img: "72526429-tasty-classical-burger.jpg" },
    { id: 2, nombre: "Salchipapa Glotona", precio: 18.00, stock: 5, desc: "Papas fritas con salchicha, huevo, queso y salsas.", img: "salchipapa.jpg" },
    // Agrega más...
];

let cantidad = 1;

document.addEventListener('DOMContentLoaded', () => {
    const producto = platos.find(p => p.id === productoId);
    if (!producto) {
        document.body.innerHTML = "<h1>Producto no encontrado</h1>";
        return;
    }

    document.getElementById('detalleImg').src = `assets/${producto.img}`;
    document.getElementById('detalleNombre').textContent = producto.nombre;
    document.getElementById('detalleDesc').textContent = producto.desc;
    document.getElementById('detallePrecio').textContent = producto.precio.toFixed(2);
    document.getElementById('detalleStock').textContent = producto.stock;

    if (producto.stock === 0) {
        document.getElementById('btnAddCarrito').disabled = true;
        document.getElementById('btnAddCarrito').textContent = "Sin Stock";
    }

    document.getElementById('btnAddCarrito').addEventListener('click', () => agregarAlCarrito(producto, cantidad));
});

function changeQty(delta) {
    cantidad = Math.max(1, cantidad + delta);
    document.getElementById('qty').value = cantidad;
}

function agregarAlCarrito(producto, qty) {
    if (producto.stock < qty) {
        alert('No hay suficiente stock');
        return;
    }

    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    for (let i = 0; i < qty; i++) {
        carrito.push({ ...producto, precio: producto.precio });
    }
    localStorage.setItem('carrito', JSON.stringify(carrito));
    alert(`¡${qty} ${producto.nombre} añadido(s) al carrito!`);
    actualizarContadorCarrito();
}

function pagarDirecto() {
    const producto = platos.find(p => p.id === productoId);
    agregarAlCarrito(producto, cantidad);
    window.location.href = 'carrito.html';
}

function actualizarContadorCarrito() {
    const contador = document.getElementById('cartCount');
    if (contador) {
        const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
        contador.textContent = carrito.length;
    }
}

window.changeQty = changeQty;