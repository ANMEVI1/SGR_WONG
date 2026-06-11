// js/menu.js — Menú dinámico desde BD vía api/menu.php

const API_MENU = 'api/menu.php';

// ====================== RENDER HELPERS ======================

function cardHTML(plato, claseExtra = '') {
    const promo = plato.promo ? '<span class="promo-tag">PROMO</span>' : '';
    const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23f0ece4' width='400' height='300'/%3E%3Ctext fill='%23c9954a' font-size='18' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3ESin imagen%3C/text%3E%3C/svg%3E";
    return `
        <article class="menu-item ${claseExtra}" onclick="verDetalle(${plato.id})">
            <img src="${plato.imagen}" alt="${plato.nombre}" loading="lazy"
                 onerror="if(this.src!=='${placeholderSvg}')this.src='${placeholderSvg}'">
            <div class="menu-item-content">
                <h3>${plato.nombre} ${promo}</h3>
                <p>${plato.descripcion || ''}</p>
                <div class="menu-item-price">Desde S/ ${plato.precio.toFixed(2)}</div>
                <button onclick="event.stopPropagation(); addToCart(${plato.id}, '${plato.nombre}', ${plato.precio}, '${plato.imagen}')"
                        class="btn btn-primary btn-small btn-full">
                    Añadir al Carrito
                </button>
            </div>
        </article>`;
}

function miniCardHTML(plato) {
    const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='150'%3E%3Crect fill='%23f0ece4' width='200' height='150'/%3E%3Ctext fill='%23c9954a' font-size='14' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3E?%3C/text%3E%3C/svg%3E";
    return `
        <div class="mini-item" onclick="verDetalle(${plato.id})">
            <img src="${plato.imagen}" alt="${plato.nombre}" loading="lazy"
                 onerror="if(this.src!=='${placeholderSvg}')this.src='${placeholderSvg}'">
            <p>${plato.nombre}</p>
            <span>S/ ${plato.precio.toFixed(2)}</span>
        </div>`;
}

function skeletonHTML(n = 4) {
    return Array(n).fill('<div class="menu-item skeleton"></div>').join('');
}

// ====================== FETCH Y RENDER ======================

async function cargarSeccion(filtro, contenedor, tipo = 'card') {
    if (!contenedor) return;
    contenedor.innerHTML = skeletonHTML(tipo === 'mini' ? 4 : 8);

    try {
        const res  = await fetch(`${API_MENU}?filtro=${filtro}`);
        const json = await res.json();

        if (!json.ok || json.data.length === 0) {
            contenedor.innerHTML = '<p class="empty-menu">No hay platos disponibles.</p>';
            return;
        }

        contenedor.innerHTML = tipo === 'mini'
            ? json.data.map(miniCardHTML).join('')
            : json.data.map(p => cardHTML(p)).join('');

    } catch {
        contenedor.innerHTML = '<p class="empty-menu">Error al cargar el menú.</p>';
    }
}

// ====================== BÚSQUEDA ======================

let todosLosPlatos = [];

async function iniciarBusqueda() {
    try {
        const res  = await fetch(`${API_MENU}?filtro=todos`);
        const json = await res.json();
        if (json.ok) todosLosPlatos = json.data;
    } catch { /* silencioso */ }
}

function buscarPlatos(term) {
    return todosLosPlatos.filter(p =>
        p.nombre.toLowerCase().includes(term) ||
        p.categoria.toLowerCase().includes(term) ||
        (p.descripcion || '').toLowerCase().includes(term)
    );
}

// ====================== CARRITO (localStorage) ======================

function addToCart(id, nombre, precio, imagen) {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    carrito.push({ id, nombre, precio, imagen });
    localStorage.setItem('carrito', JSON.stringify(carrito));
    actualizarContadorCarrito();
    mostrarNotificacion(`${nombre} añadido al carrito`, 'success');
}

function actualizarContadorCarrito() {
    const el = document.getElementById('cartCount');
    if (el) el.textContent = (JSON.parse(localStorage.getItem('carrito')) || []).length;
}

function verDetalle(id) {
    window.location.href = `producto-detalle.html?id=${id}`;
}

// ====================== INIT ======================

document.addEventListener('DOMContentLoaded', async () => {
    actualizarContadorCarrito();

    await Promise.all([
        cargarSeccion('todos', document.getElementById('menuGrid'), 'card'),
        cargarSeccion('top',   document.querySelector('#top-ventas .mini-carousel'), 'mini'),
        cargarSeccion('promo', document.querySelector('#promociones .mini-carousel'), 'mini'),
        iniciarBusqueda(),
    ]);

    // Búsqueda en vivo
    const searchInput   = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', e => {
            clearTimeout(searchTimeout);
            const term = e.target.value.toLowerCase().trim();

            if (!term) { searchResults.classList.remove('active'); return; }

            searchTimeout = setTimeout(() => {
                const resultados = buscarPlatos(term).slice(0, 8);
                searchResults.innerHTML = resultados.length
                    ? resultados.map(p => `
                        <a href="producto-detalle.html?id=${p.id}">
                            <strong>${p.nombre}</strong> — S/ ${p.precio.toFixed(2)}
                        </a>`).join('')
                    : '<div style="padding:14px 18px;color:#999">Sin resultados</div>';
                searchResults.classList.add('active');
            }, 300);
        });

        document.addEventListener('click', e => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target))
                searchResults.classList.remove('active');
        });
    }
});
