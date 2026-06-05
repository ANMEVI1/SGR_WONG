/**
 * Módulo: Modal de Confirmación de Reserva
 * Genera modal profesional con información de pago
 */

const ModalConfirmacionReserva = (function() {
    'use strict';

    // Configuración de métodos de pago
    const METODOS_PAGO = {
        yape: {
            nombre: 'Yape',
            icono: 'fa-mobile-alt',
            color: '#6C1D8E',
            numero: '991 183 777',
            titular: 'Chifa Matsue',
            qr: 'assets/qr/yape-qr.png',
            instrucciones: [
                'Abre tu app de Yape',
                'Selecciona "Yapear"',
                'Escanea el código QR o ingresa el número',
                'Ingresa el monto exacto',
                'Agrega tu nombre y número de reserva en el comentario',
                'Confirma el pago'
            ]
        },
        plin: {
            nombre: 'Plin',
            icono: 'fa-mobile-alt',
            color: '#00D4B4',
            numero: '991 183 777',
            titular: 'Chifa Matsue',
            qr: 'assets/qr/plin-qr.png',
            instrucciones: [
                'Abre tu app de Plin',
                'Selecciona "Enviar"',
                'Escanea el código QR o busca el número',
                'Ingresa el monto exacto',
                'Escribe tu nombre y número de reserva',
                'Confirma el pago'
            ]
        },
        transferencia: {
            nombre: 'Transferencia Bancaria',
            icono: 'fa-university',
            color: '#007ACC',
            banco: 'BCP',
            tipoCuenta: 'Cuenta Corriente',
            numeroCuenta: '191-2345678-0-00',
            cci: '002-191-002345678000-00',
            titular: 'Chifa Matsue SAC',
            ruc: '20512345678',
            instrucciones: [
                'Ingresa a tu banca por internet o app',
                'Selecciona "Transferir a otras cuentas"',
                'Usa los datos bancarios proporcionados',
                'Ingresa el monto exacto',
                'En el concepto escribe: Reserva + tu nombre',
                'Guarda el comprobante de operación'
            ]
        },
        tarjeta: {
            nombre: 'Tarjeta de Crédito/Débito',
            icono: 'fa-credit-card',
            color: '#FF6B6B',
            procesador: 'Culqi / Mercado Pago',
            instrucciones: [
                'Recibirás un enlace de pago por correo',
                'Haz clic en el enlace seguro',
                'Ingresa los datos de tu tarjeta',
                'Confirma el pago',
                'Guarda el comprobante digital'
            ]
        }
    };

    /**
     * Crear y mostrar modal
     */
    function mostrar(datosReserva) {
        const modal = crearModal(datosReserva);
        document.body.appendChild(modal);
        
        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';
        
        // Configurar eventos
        configurarEventos(modal, datosReserva);
        
        return modal;
    }

    /**
     * Crear estructura del modal
     */
    function crearModal(datos) {
        const overlay = document.createElement('div');
        overlay.className = 'modal-reserva-overlay';
        overlay.id = 'modalConfirmacionReserva';
        
        const metodoPago = METODOS_PAGO[datos.metodo_pago] || METODOS_PAGO.yape;
        
        overlay.innerHTML = `
            <div class="modal-reserva-container">
                ${crearHeader()}
                ${crearBody(datos, metodoPago)}
                ${crearFooter(datos)}
            </div>
        `;
        
        return overlay;
    }

    /**
     * Crear header del modal
     */
    function crearHeader() {
        return `
            <div class="modal-reserva-header">
                <button class="modal-reserva-close" id="btnCerrarModal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
                <div class="icon-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>¡Reserva Confirmada!</h2>
                <p>Tu reserva ha sido registrada exitosamente</p>
            </div>
        `;
    }

    /**
     * Crear body del modal
     */
    function crearBody(datos, metodoPago) {
        return `
            <div class="modal-reserva-body">
                ${crearSeccionInfo(datos)}
                ${crearSeccionPago(datos, metodoPago)}
                ${crearSeccionPlatos(datos)}
                ${crearTokenCancelacion(datos)}
            </div>
        `;
    }

    /**
     * Sección de información de la reserva
     */
    function crearSeccionInfo(datos) {
        const fecha = formatearFecha(datos.fecha);
        
        return `
            <div class="reserva-info-section">
                <h3>
                    <i class="fas fa-calendar-check"></i>
                    Detalles de tu Reserva
                </h3>
                <div class="reserva-info-grid">
                    <div class="reserva-info-item">
                        <label>Número de Reserva</label>
                        <div class="value">#${datos.reserva_id}</div>
                    </div>
                    <div class="reserva-info-item">
                        <label>Fecha</label>
                        <div class="value">${fecha}</div>
                    </div>
                    <div class="reserva-info-item">
                        <label>Hora</label>
                        <div class="value">${datos.hora}</div>
                    </div>
                    <div class="reserva-info-item">
                        <label>Comensales</label>
                        <div class="value">${datos.num_comensales} ${datos.num_comensales === 1 ? 'persona' : 'personas'}</div>
                    </div>
                    <div class="reserva-info-item">
                        <label>Cliente</label>
                        <div class="value">${datos.nombre}</div>
                    </div>
                    <div class="reserva-info-item">
                        <label>Estado</label>
                        <div class="value">
                            <span style="color: #ffc107; font-weight: 700;">
                                <i class="fas fa-clock"></i> Pendiente de Pago
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Sección de pago
     */
    function crearSeccionPago(datos, metodoPago) {
        // Calcular desglose si hay platos
        const senalBase = datos.num_comensales * 11;
        const senalPlatos = datos.subtotal_platos ? (datos.subtotal_platos * 0.30) : 0;
        const mostrarDesglose = datos.subtotal_platos && datos.subtotal_platos > 0;
        
        let desglose = '';
        if (mostrarDesglose) {
            desglose = `
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #666;">
                        <span>• Señal base (${datos.num_comensales} ${datos.num_comensales === 1 ? 'persona' : 'personas'} x S/ 11.00)</span>
                        <span style="font-weight: 600; color: #333;">S/ ${senalBase.toFixed(2)}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #666;">
                        <span>• 30% de platos pre-ordenados (S/ ${datos.subtotal_platos.toFixed(2)})</span>
                        <span style="font-weight: 600; color: #333;">S/ ${senalPlatos.toFixed(2)}</span>
                    </div>
                    <div style="border-top: 2px solid #f0f0f0; margin-top: 10px; padding-top: 10px; display: flex; justify-content: space-between; font-weight: 700; color: #d4af37;">
                        <span>TOTAL SEÑAL</span>
                        <span style="font-size: 18px;">S/ ${parseFloat(datos.total_senal).toFixed(2)}</span>
                    </div>
                </div>
            `;
        }
        
        return `
            <div class="reserva-pago-section">
                <h3>
                    <i class="fas fa-wallet"></i>
                    Información de Pago
                </h3>
                
                <div class="reserva-monto-destacado">
                    <div class="label">Señal a pagar</div>
                    <div class="monto">S/ ${parseFloat(datos.total_senal).toFixed(2)}</div>
                    <div class="descripcion">Este monto se descontará de tu consumo final</div>
                </div>

                ${desglose}

                ${crearInstruccionesPago(metodoPago, datos)}
                
                <div class="reserva-nota-importante">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Debes realizar el pago dentro de las próximas 2 horas para confirmar tu reserva. 
                        Una vez realizado el pago, envía el comprobante por WhatsApp.
                    </p>
                </div>
            </div>
        `;
    }

    /**
     * Instrucciones según método de pago
     */
    function crearInstruccionesPago(metodoPago, datos) {
        let html = `
            <div class="reserva-instrucciones-pago">
                <h4>
                    <i class="${metodoPago.icono}" style="color: ${metodoPago.color};"></i>
                    Pagar con ${metodoPago.nombre}
                </h4>
        `;

        // QR para Yape y Plin
        if (metodoPago.qr) {
            html += `
                <div class="reserva-qr-container">
                    <img src="${metodoPago.qr}" alt="QR ${metodoPago.nombre}" onerror="this.src='assets/img/qr-placeholder.png'">
                    <div class="qr-label">Escanea este código QR</div>
                </div>
                <div class="reserva-datos-pago">
                    <div class="reserva-dato-item">
                        <span class="label">Número:</span>
                        <span class="value">
                            ${metodoPago.numero}
                            <button class="btn-copiar" onclick="ModalConfirmacionReserva.copiarTexto('${metodoPago.numero}')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">Titular:</span>
                        <span class="value">${metodoPago.titular}</span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">Monto:</span>
                        <span class="value" style="color: #d4af37; font-weight: 700;">S/ ${parseFloat(datos.total_senal).toFixed(2)}</span>
                    </div>
                </div>
            `;
        }

        // Datos bancarios para transferencia
        if (metodoPago.banco) {
            html += `
                <div class="reserva-datos-pago">
                    <div class="reserva-dato-item">
                        <span class="label">Banco:</span>
                        <span class="value">${metodoPago.banco}</span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">Tipo de Cuenta:</span>
                        <span class="value">${metodoPago.tipoCuenta}</span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">Número de Cuenta:</span>
                        <span class="value">
                            ${metodoPago.numeroCuenta}
                            <button class="btn-copiar" onclick="ModalConfirmacionReserva.copiarTexto('${metodoPago.numeroCuenta}')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">CCI:</span>
                        <span class="value">
                            ${metodoPago.cci}
                            <button class="btn-copiar" onclick="ModalConfirmacionReserva.copiarTexto('${metodoPago.cci}')">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">Titular:</span>
                        <span class="value">${metodoPago.titular}</span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">RUC:</span>
                        <span class="value">${metodoPago.ruc}</span>
                    </div>
                    <div class="reserva-dato-item">
                        <span class="label">Monto:</span>
                        <span class="value" style="color: #d4af37; font-weight: 700;">S/ ${parseFloat(datos.total_senal).toFixed(2)}</span>
                    </div>
                </div>
            `;
        }

        // Instrucciones paso a paso
        html += `
            <div style="margin-top: 20px;">
                <h5 style="font-size: 14px; color: #666; margin-bottom: 10px;">
                    <i class="fas fa-list-ol"></i> Pasos a seguir:
                </h5>
                <ol style="margin: 0; padding-left: 20px; color: #666; font-size: 14px; line-height: 1.8;">
        `;

        metodoPago.instrucciones.forEach(instruccion => {
            html += `<li>${instruccion}</li>`;
        });

        html += `
                </ol>
            </div>
        </div>
        `;

        return html;
    }

    /**
     * Sección de platos pre-ordenados
     */
    function crearSeccionPlatos(datos) {
        if (!datos.platos_pre_ordenados || datos.platos_pre_ordenados.length === 0) {
            return '';
        }

        let html = `
            <div class="reserva-info-section">
                <h3>
                    <i class="fas fa-utensils"></i>
                    Platos Pre-Ordenados
                </h3>
                <div class="reserva-platos-lista">
                    <ul>
        `;

        datos.platos_pre_ordenados.forEach(plato => {
            const subtotal = plato.precio * plato.cantidad;
            html += `
                <li>
                    <span class="plato-nombre">${plato.nombre} x${plato.cantidad}</span>
                    <span class="plato-precio">S/ ${subtotal.toFixed(2)}</span>
                </li>
            `;
        });

        html += `
                    </ul>
                    <div style="border-top: 2px solid #d4af37; margin-top: 15px; padding-top: 15px; text-align: right;">
                        <strong style="font-size: 16px; color: #333;">
                            Subtotal Platos: 
                            <span style="color: #d4af37; font-size: 18px;">S/ ${parseFloat(datos.subtotal_platos).toFixed(2)}</span>
                        </strong>
                    </div>
                </div>
            </div>
        `;

        return html;
    }

    /**
     * Token de cancelación
     */
    function crearTokenCancelacion(datos) {
        return `
            <div class="reserva-token-box">
                <div class="token-label">Token de Cancelación</div>
                <div class="token-value">${datos.token}</div>
                <div class="token-info">Guarda este código para cancelar tu reserva si es necesario</div>
            </div>
        `;
    }

    /**
     * Footer con botones
     */
    function crearFooter(datos) {
        return `
            <div class="modal-reserva-footer">
                <button class="btn btn-whatsapp" id="btnEnviarWhatsApp">
                    <i class="fab fa-whatsapp"></i>
                    Enviar Comprobante por WhatsApp
                </button>
                <button class="btn btn-cerrar" id="btnCerrarModalFooter">
                    <i class="fas fa-check"></i>
                    Entendido
                </button>
            </div>
        `;
    }

    /**
     * Configurar eventos del modal
     */
    function configurarEventos(modal, datos) {
        // Botón cerrar (X)
        const btnCerrarX = modal.querySelector('#btnCerrarModal');
        btnCerrarX?.addEventListener('click', () => cerrar(modal));

        // Botón cerrar footer
        const btnCerrarFooter = modal.querySelector('#btnCerrarModalFooter');
        btnCerrarFooter?.addEventListener('click', () => cerrar(modal));

        // Botón WhatsApp
        const btnWhatsApp = modal.querySelector('#btnEnviarWhatsApp');
        btnWhatsApp?.addEventListener('click', () => enviarWhatsApp(datos));

        // Cerrar al hacer clic en el overlay
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                cerrar(modal);
            }
        });

        // Cerrar con tecla ESC
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                cerrar(modal);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    /**
     * Cerrar modal
     */
    function cerrar(modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';
        }, 300);
    }

    /**
     * Enviar información por WhatsApp
     */
    function enviarWhatsApp(datos) {
        const metodoPago = METODOS_PAGO[datos.metodo_pago];
        const fecha = formatearFecha(datos.fecha);
        
        let mensaje = `🎉 *NUEVA RESERVA - CHIFA MATSUE*\n\n`;
        mensaje += `📋 *Número:* #${datos.reserva_id}\n`;
        mensaje += `👤 *Cliente:* ${datos.nombre}\n`;
        mensaje += `📅 *Fecha:* ${fecha}\n`;
        mensaje += `🕐 *Hora:* ${datos.hora}\n`;
        mensaje += `👥 *Comensales:* ${datos.num_comensales}\n\n`;
        
        mensaje += `💰 *PAGO*\n`;
        mensaje += `Método: ${metodoPago.nombre}\n`;
        mensaje += `Señal: S/ ${parseFloat(datos.total_senal).toFixed(2)}\n`;
        mensaje += `Estado: ⏳ Pendiente\n\n`;
        
        if (datos.platos_pre_ordenados && datos.platos_pre_ordenados.length > 0) {
            mensaje += `🍜 *PLATOS PRE-ORDENADOS:*\n`;
            datos.platos_pre_ordenados.forEach(plato => {
                mensaje += `• ${plato.nombre} x${plato.cantidad} - S/ ${(plato.precio * plato.cantidad).toFixed(2)}\n`;
            });
            mensaje += `\nSubtotal: S/ ${parseFloat(datos.subtotal_platos).toFixed(2)}\n\n`;
        }
        
        mensaje += `🔑 *Token:* ${datos.token}\n\n`;
        mensaje += `_Enviaré el comprobante de pago_`;

        const numeroWhatsApp = '51991183777'; // Cambiar por el número real
        const url = `https://wa.me/${numeroWhatsApp}?text=${encodeURIComponent(mensaje)}`;
        
        window.open(url, '_blank');
    }

    /**
     * Copiar texto al portapapeles
     */
    function copiarTexto(texto) {
        navigator.clipboard.writeText(texto).then(() => {
            // Mostrar notificación temporal
            mostrarNotificacion('¡Copiado!', 'success');
        }).catch(() => {
            mostrarNotificacion('No se pudo copiar', 'error');
        });
    }

    /**
     * Mostrar notificación temporal
     */
    function mostrarNotificacion(mensaje, tipo) {
        const notif = document.createElement('div');
        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: ${tipo === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 99999;
            animation: slideInRight 0.3s ease;
        `;
        notif.textContent = mensaje;
        document.body.appendChild(notif);

        setTimeout(() => {
            notif.remove();
        }, 2000);
    }

    /**
     * Formatear fecha
     */
    function formatearFecha(fecha) {
        const opciones = { 
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', opciones);
    }

    // API Pública
    return {
        mostrar,
        cerrar,
        copiarTexto
    };

})();

// Hacer disponible globalmente
window.ModalConfirmacionReserva = ModalConfirmacionReserva;
