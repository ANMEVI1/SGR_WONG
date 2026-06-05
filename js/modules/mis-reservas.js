/**
 * Módulo: Mis Reservas (Cliente)
 * Gestiona la visualización de reservas del cliente logueado
 * Se integra con modal-perfil.js sin modificarlo
 */

(function() {
    'use strict';

    const API_URL = (window.APP_BASE_URL || '') + 'api/reservas/mis-reservas.php';

    /**
     * Crear HTML de la sección de reservas
     */
    function crearSeccionReservas() {
        return `
            <div class="section-head">
                <div>
                    <h2>Mis Reservas</h2>
                    <p>Consulta el estado de tus reservas activas</p>
                </div>
                <button class="btn btn-ghost-red" id="btnNuevaReserva">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v8M8 12h8"/>
                    </svg>
                    Nueva Reserva
                </button>
            </div>
            <div id="reservasContainer">
                <div class="loading-state">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                    </svg>
                    Cargando reservas...
                </div>
            </div>
        `;
    }

    /**
     * Cargar reservas desde API
     */
    async function cargarReservas() {
        const container = document.getElementById('reservasContainer');
        
        if (!container) return;

        try {
            const response = await fetch(API_URL, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                renderizarReservas(result.data, container);
            } else {
                mostrarEstadoVacio(container);
            }

        } catch (error) {
            console.error('Error cargando reservas:', error);
            mostrarError(container);
        }
    }

    /**
     * Renderizar lista de reservas
     */
    function renderizarReservas(reservas, container) {
        const html = `
            <div class="reservas-grid">
                ${reservas.map(reserva => crearTarjetaReserva(reserva)).join('')}
            </div>
        `;
        
        container.innerHTML = html;

        // Agregar event listeners
        container.querySelectorAll('.btn-ver-detalles').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.currentTarget.dataset.id);
                const reserva = reservas.find(r => r.id === id);
                if (reserva) mostrarDetallesReserva(reserva);
            });
        });
    }

    /**
     * Crear tarjeta de reserva
     */
    function crearTarjetaReserva(reserva) {
        const estadoClass = obtenerClaseEstado(reserva.estado);
        const estadoLabel = obtenerLabelEstado(reserva.estado);
        const fechaFormateada = formatearFecha(reserva.fecha);
        const esFutura = esFechaFutura(reserva.fecha);

        return `
            <div class="reserva-card ${estadoClass}">
                <div class="reserva-card-header">
                    <div class="reserva-numero">#${reserva.id}</div>
                    <span class="reserva-badge reserva-badge-${estadoClass}">${estadoLabel}</span>
                </div>
                <div class="reserva-card-body">
                    <div class="reserva-info-row">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        <span>${fechaFormateada}</span>
                    </div>
                    <div class="reserva-info-row">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                        <span>${reserva.hora}</span>
                    </div>
                    <div class="reserva-info-row">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>${reserva.personas} ${reserva.personas === 1 ? 'persona' : 'personas'}</span>
                    </div>
                </div>
                <div class="reserva-card-footer">
                    <button class="btn btn-sm btn-outline btn-ver-detalles" data-id="${reserva.id}">
                        Ver detalles
                    </button>
                    ${esFutura && (reserva.estado === 'Pendiente' || reserva.estado === 'Confirmada') ? `
                        <button class="btn btn-sm btn-outline-danger" onclick="MisReservas.cancelarReserva('${reserva.token}', ${reserva.id})">
                            Cancelar
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }

    /**
     * Mostrar detalles de reserva en modal pequeño
     */
    function mostrarDetallesReserva(reserva) {
        const modal = document.createElement('div');
        modal.className = 'reserva-detalle-overlay';
        modal.innerHTML = `
            <div class="reserva-detalle-modal">
                <div class="reserva-detalle-header">
                    <h3>Detalles de Reserva #${reserva.id}</h3>
                    <button class="reserva-detalle-close" onclick="this.closest('.reserva-detalle-overlay').remove()">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="reserva-detalle-body">
                    <div class="detalle-item">
                        <label>Estado:</label>
                        <span class="reserva-badge reserva-badge-${obtenerClaseEstado(reserva.estado)}">${obtenerLabelEstado(reserva.estado)}</span>
                    </div>
                    <div class="detalle-item">
                        <label>Fecha:</label>
                        <span>${formatearFecha(reserva.fecha)}</span>
                    </div>
                    <div class="detalle-item">
                        <label>Hora:</label>
                        <span>${reserva.hora}</span>
                    </div>
                    <div class="detalle-item">
                        <label>Comensales:</label>
                        <span>${reserva.personas} ${reserva.personas === 1 ? 'persona' : 'personas'}</span>
                    </div>
                    ${reserva.observaciones ? `
                        <div class="detalle-item full">
                            <label>Observaciones:</label>
                            <span>${reserva.observaciones}</span>
                        </div>
                    ` : ''}
                    <div class="detalle-item full">
                        <label>Token de cancelación:</label>
                        <div class="token-box">
                            <code>${reserva.token}</code>
                            <button class="btn-copy-token" onclick="MisReservas.copiarToken('${reserva.token}')">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="detalle-item full">
                        <small style="color: #666;">Creada el ${new Date(reserva.fecha_creacion).toLocaleString('es-ES')}</small>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);

        // Cerrar al hacer clic fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    /**
     * Mostrar estado vacío
     */
    function mostrarEstadoVacio(container) {
        container.innerHTML = `
            <div class="empty-state">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <p>Aún no tienes reservas registradas</p>
                <button class="btn btn-primary" onclick="window.location.href='reservas.php'">
                    Hacer mi primera reserva
                </button>
            </div>
        `;
    }

    /**
     * Mostrar error
     */
    function mostrarError(container) {
        container.innerHTML = `
            <div class="empty-state error-state">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4M12 16h.01"/>
                </svg>
                <p>Error al cargar tus reservas</p>
                <button class="btn btn-outline" onclick="MisReservas.recargar()">
                    Reintentar
                </button>
            </div>
        `;
    }

    /**
     * Utilidades
     */
    function obtenerClaseEstado(estado) {
        const map = {
            'Pendiente': 'pendiente',
            'Confirmada': 'confirmada',
            'Cancelada': 'cancelada',
            'Completada': 'completada',
            'No Show': 'no-show'
        };
        return map[estado] || 'pendiente';
    }

    function obtenerLabelEstado(estado) {
        const map = {
            'Pendiente': '⏳ Pendiente',
            'Confirmada': '✅ Confirmada',
            'Cancelada': '❌ Cancelada',
            'Completada': '✔️ Completada',
            'No Show': '⚠️ No asistió'
        };
        return map[estado] || estado;
    }

    function formatearFecha(fecha) {
        const opciones = { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };
        return new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', opciones);
    }

    function esFechaFutura(fecha) {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const fechaReserva = new Date(fecha + 'T00:00:00');
        return fechaReserva >= hoy;
    }

    /**
     * Copiar token
     */
    function copiarToken(token) {
        navigator.clipboard.writeText(token).then(() => {
            mostrarToast('Token copiado al portapapeles');
        }).catch(() => {
            mostrarToast('No se pudo copiar el token');
        });
    }

    /**
     * Cancelar reserva
     */
    async function cancelarReserva(token, id) {
        if (!confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
            return;
        }

        // TODO: Implementar endpoint de cancelación
        mostrarToast('Función de cancelación en desarrollo');
    }

    /**
     * Mostrar toast
     */
    function mostrarToast(mensaje) {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.textContent = mensaje;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2500);
        }
    }

    /**
     * Recargar reservas
     */
    function recargar() {
        const container = document.getElementById('reservasContainer');
        if (container) {
            container.innerHTML = `
                <div class="loading-state">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                    </svg>
                    Cargando reservas...
                </div>
            `;
            cargarReservas();
        }
    }

    /**
     * Ir a nueva reserva
     */
    function irANuevaReserva() {
        window.location.href = 'reservas.php';
    }

    // API Pública
    window.MisReservas = {
        crear: crearSeccionReservas,
        cargar: cargarReservas,
        copiarToken: copiarToken,
        cancelarReserva: cancelarReserva,
        recargar: recargar,
        irANuevaReserva: irANuevaReserva
    };

})();
