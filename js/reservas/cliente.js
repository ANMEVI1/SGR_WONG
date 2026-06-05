/**
 * Módulo de Reservas - Cliente Web
 * Maneja el formulario de reservas y comunicación con API
 */

(function() {
    'use strict';

    // ========== CONFIGURACIÓN ==========
    const CONFIG = {
        // Usar ruta base dinámica o fallback a ruta relativa
        API_URL: (window.APP_BASE_URL || '') + 'api/reservas/crear.php',
        MIN_ANTICIPACION_HORAS: 2,
        HORARIO_APERTURA: 11,
        HORARIO_CIERRE: 22
    };

    // ========== ELEMENTOS DEL DOM ==========
    const elementos = {
        form: document.getElementById('reservaForm'),
        btnSubmit: null,
        inputFecha: null,
        inputHora: null,
        inputPersonas: null
    };

    // ========== INICIALIZACIÓN ==========
    function init() {
        if (!elementos.form) {
            console.warn('Formulario de reservas no encontrado en esta página');
            return;
        }

        // Log de configuración para debug
        console.log('=== Sistema de Reservas Inicializado ===');
        console.log('API URL:', CONFIG.API_URL);
        console.log('Base URL:', window.APP_BASE_URL);
        console.log('Location:', window.location.href);

        // Cachear elementos
        elementos.btnSubmit = elementos.form.querySelector('button[type="submit"]');
        elementos.inputFecha = elementos.form.querySelector('input[name="fecha"]');
        elementos.inputHora = elementos.form.querySelector('input[name="hora"]');
        elementos.inputPersonas = elementos.form.querySelector('select[name="personas"]');

        // Configurar fecha mínima (hoy + anticipación mínima)
        configurarFechaMinima();

        // Event listeners
        elementos.form.addEventListener('submit', handleSubmit);
        elementos.inputFecha?.addEventListener('change', validarFechaSeleccionada);
        elementos.inputHora?.addEventListener('change', validarHoraSeleccionada);
    }

    // ========== CONFIGURAR FECHA MÍNIMA ==========
    function configurarFechaMinima() {
        if (!elementos.inputFecha) return;

        const ahora = new Date();
        ahora.setHours(ahora.getHours() + CONFIG.MIN_ANTICIPACION_HORAS);
        
        const fechaMinima = ahora.toISOString().split('T')[0];
        elementos.inputFecha.setAttribute('min', fechaMinima);
    }

    // ========== VALIDAR FECHA SELECCIONADA ==========
    function validarFechaSeleccionada(e) {
        const fechaSeleccionada = new Date(e.target.value + 'T00:00:00');
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        if (fechaSeleccionada < hoy) {
            mostrarError('No puedes seleccionar una fecha pasada');
            e.target.value = '';
            return false;
        }

        return true;
    }

    // ========== VALIDAR HORA SELECCIONADA ==========
    function validarHoraSeleccionada(e) {
        const horaSeleccionada = e.target.value;
        if (!horaSeleccionada) return true;

        const [hora, minuto] = horaSeleccionada.split(':').map(Number);
        
        if (hora < CONFIG.HORARIO_APERTURA || hora >= CONFIG.HORARIO_CIERRE) {
            mostrarError(`Nuestro horario es de ${CONFIG.HORARIO_APERTURA}:00 AM a ${CONFIG.HORARIO_CIERRE}:00 PM`);
            e.target.value = '';
            return false;
        }

        return true;
    }

    // ========== MANEJAR ENVÍO DEL FORMULARIO ==========
    async function handleSubmit(e) {
        e.preventDefault();

        console.log('=== Enviando reserva ===');
        console.log('URL de API:', CONFIG.API_URL);

        // Validaciones del lado del cliente
        if (!validarFormulario()) {
            return;
        }

        // Deshabilitar botón y mostrar estado de carga
        const textOriginal = elementos.btnSubmit.innerHTML;
        elementos.btnSubmit.disabled = true;
        elementos.btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        try {
            // Preparar datos del formulario
            const formData = new FormData(elementos.form);

            console.log('Datos a enviar:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }

            // Enviar a la API
            console.log('Realizando fetch a:', CONFIG.API_URL);
            const response = await fetch(CONFIG.API_URL, {
                method: 'POST',
                body: formData
            });

            console.log('Response status:', response.status);
            console.log('Response OK:', response.ok);

            const result = await response.json();
            console.log('Resultado:', result);

            if (result.success) {
                // Éxito: mostrar mensaje y resetear formulario
                mostrarExito(result);
                elementos.form.reset();
            } else {
                // Error: mostrar mensaje de error
                if (result.errors) {
                    // Errores de validación múltiples
                    mostrarErroresValidacion(result.errors);
                } else {
                    // Error general
                    mostrarError(result.message || 'Error al procesar la reserva');
                }
            }

        } catch (error) {
            console.error('Error al enviar reserva:', error);
            console.error('URL intentada:', CONFIG.API_URL);
            console.error('Detalles del error:', {
                message: error.message,
                stack: error.stack
            });
            mostrarError('Error de conexión. Verifica que XAMPP esté corriendo y la ruta sea correcta.<br><br>URL: ' + CONFIG.API_URL);
        } finally {
            // Restaurar botón
            elementos.btnSubmit.disabled = false;
            elementos.btnSubmit.innerHTML = textOriginal;
        }
    }

    // ========== VALIDAR FORMULARIO COMPLETO ==========
    function validarFormulario() {
        const errores = [];

        // Validar nombre
        const nombre = elementos.form.querySelector('input[name="nombre"]').value.trim();
        if (nombre.length < 3) {
            errores.push('El nombre debe tener al menos 3 caracteres');
        }

        // Validar correo
        const correo = elementos.form.querySelector('input[name="correo"]').value.trim();
        if (!validarEmail(correo)) {
            errores.push('Ingresa un correo válido');
        }

        // Validar teléfono
        const telefono = elementos.form.querySelector('input[name="telefono"]').value.trim();
        if (telefono.length < 7) {
            errores.push('El teléfono debe tener al menos 7 dígitos');
        }

        // Validar fecha
        const fecha = elementos.inputFecha?.value;
        if (!fecha) {
            errores.push('Selecciona una fecha');
        }

        // Validar hora
        const hora = elementos.inputHora?.value;
        if (!hora) {
            errores.push('Selecciona una hora');
        }

        // Validar personas
        const personas = elementos.inputPersonas?.value;
        if (!personas || personas === '') {
            errores.push('Selecciona el número de personas');
        }

        if (errores.length > 0) {
            mostrarError(errores.join('<br>'));
            return false;
        }

        return true;
    }

    // ========== VALIDAR EMAIL ==========
    function validarEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // ========== MOSTRAR ÉXITO ==========
    function mostrarExito(result) {
        const data = result.data;
        const mensaje = `
            <div class="reserva-exito">
                <i class="fas fa-check-circle" style="color: #28a745; font-size: 48px; margin-bottom: 15px;"></i>
                <h3 style="color: #28a745; margin-bottom: 10px;">¡Reserva Confirmada!</h3>
                <p style="margin-bottom: 20px;">${result.message}</p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <p><strong>Número de reserva:</strong> #${data.reserva_id}</p>
                    <p><strong>Fecha:</strong> ${formatearFecha(data.fecha)}</p>
                    <p><strong>Hora:</strong> ${data.hora}</p>
                    <p><strong>Personas:</strong> ${data.num_comensales}</p>
                </div>
                <p style="font-size: 14px; color: #666;">
                    Te contactaremos pronto al correo o teléfono registrado para confirmar tu reserva.
                </p>
                <p style="font-size: 12px; color: #999; margin-top: 10px;">
                    Token de cancelación: <code>${data.token}</code><br>
                    <small>Guarda este código para cancelar tu reserva si es necesario.</small>
                </p>
            </div>
        `;

        mostrarModal(mensaje, 'success');
    }

    // ========== MOSTRAR ERROR ==========
    function mostrarError(mensaje) {
        const contenido = `
            <div class="reserva-error">
                <i class="fas fa-exclamation-circle" style="color: #dc3545; font-size: 48px; margin-bottom: 15px;"></i>
                <h3 style="color: #dc3545; margin-bottom: 10px;">Error</h3>
                <p>${mensaje}</p>
            </div>
        `;
        mostrarModal(contenido, 'error');
    }

    // ========== MOSTRAR ERRORES DE VALIDACIÓN ==========
    function mostrarErroresValidacion(errores) {
        const listaErrores = Object.values(errores).map(error => `<li>${error}</li>`).join('');
        const contenido = `
            <div class="reserva-error">
                <i class="fas fa-exclamation-triangle" style="color: #ffc107; font-size: 48px; margin-bottom: 15px;"></i>
                <h3 style="color: #ffc107; margin-bottom: 10px;">Errores de Validación</h3>
                <ul style="text-align: left; display: inline-block; margin: 0 auto;">${listaErrores}</ul>
            </div>
        `;
        mostrarModal(contenido, 'warning');
    }

    // ========== MOSTRAR MODAL GENÉRICO ==========
    function mostrarModal(contenido, tipo = 'info') {
        // Usar alert simple por ahora (puede mejorarse con modal custom)
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = contenido;
        const textoPlano = tempDiv.textContent || tempDiv.innerText;
        
        alert(textoPlano);
        
        // TODO: Implementar modal visual personalizado en futuras versiones
    }

    // ========== FORMATEAR FECHA ==========
    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', opciones);
    }

    // ========== EJECUTAR AL CARGAR EL DOM ==========
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
