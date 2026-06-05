/**
 * Módulo de Reservas Mejorado - UX/UI Optimizada
 * Incluye: Toggle usuario, selector de platos, cálculo de señal
 */

(function() {
    'use strict';

    // ========== CONFIGURACIÓN ==========
    const CONFIG = {
        API_URL: (window.APP_BASE_URL || '') + 'api/reservas/crear.php',
        API_PLATOS: (window.APP_BASE_URL || '') + 'api/reservas/listar-platos.php',
        API_CLIENTE: (window.APP_BASE_URL || '') + 'api/reservas/datos-cliente.php',
        MIN_ANTICIPACION_HORAS: 2,
        HORARIO_APERTURA: 11,
        HORARIO_CIERRE: 22,
        SENAL_POR_PERSONA: 11.00,
        PORCENTAJE_SENAL_PLATOS: 30  // 30% del subtotal de platos
    };

    // ========== ESTADO GLOBAL ==========
    const estado = {
        reservaParaMi: true,
        platosSeleccionados: [],
        subtotalPlatos: 0,
        numPersonas: 0,
        totalSenal: 0
    };

    // ========== ELEMENTOS DEL DOM ==========
    const elementos = {
        form: document.getElementById('reservaForm'),
        btnSubmit: null,
        // Toggle tipo de reserva
        btnsReservaTipo: document.querySelectorAll('.btn-reserva-tipo'),
        datosContacto: document.getElementById('datosContacto'),
        // Campos de formulario
        inputNombre: null,
        inputCorreo: null,
        inputTelefono: null,
        inputFecha: null,
        inputHora: null,
        selectPersonas: null,
        inputMetodoPago: null,
        // Platos
        checkIncluirPlatos: document.getElementById('incluirPlatos'),
        selectorPlatos: document.getElementById('selectorPlatos'),
        listadoPlatos: document.getElementById('listadoPlatos'),
        resumenPlatos: document.getElementById('resumenPlatos'),
        listaSeleccionados: document.getElementById('listaSeleccionados'),
        subtotalPlatosSpan: document.getElementById('subtotalPlatos'),
        // Señal
        totalSenalSpan: document.getElementById('totalSenal'),
        desgloseSenalPreview: document.getElementById('desgloseSenalPreview'),
        prevPersonas: document.getElementById('prevPersonas'),
        prevBase: document.getElementById('prevBase'),
        prevPlatos: document.getElementById('prevPlatos'),
        instruccionesPago: document.getElementById('instruccionesPago')
    };

    // ========== INICIALIZACIÓN ==========
    function init() {
        if (!elementos.form) {
            console.warn('Formulario de reservas no encontrado');
            return;
        }

        console.log('=== Sistema de Reservas Mejorado Inicializado ===');

        // Cachear elementos
        cachearElementos();

        // Si hay usuario autenticado, cargar sus datos
        if (elementos.btnsReservaTipo.length > 0) {
            configurarToggleUsuario();
            cargarDatosCliente();
        }

        // Configurar fecha mínima
        configurarFechaMinima();

        // Event listeners
        elementos.form.addEventListener('submit', handleSubmit);
        elementos.selectPersonas?.addEventListener('change', calcularTotalSenal);
        elementos.inputMetodoPago?.addEventListener('change', mostrarInstruccionesPago);
        elementos.checkIncluirPlatos?.addEventListener('change', toggleSelectorPlatos);
        elementos.inputFecha?.addEventListener('change', validarFechaSeleccionada);
        elementos.inputHora?.addEventListener('change', validarHoraSeleccionada);

        console.log('Estado inicial:', estado);
    }

    // ========== CACHEAR ELEMENTOS ==========
    function cachearElementos() {
        elementos.btnSubmit = elementos.form.querySelector('button[type="submit"]');
        elementos.inputNombre = elementos.form.querySelector('input[name="nombre"]');
        elementos.inputCorreo = elementos.form.querySelector('input[name="correo"]');
        elementos.inputTelefono = elementos.form.querySelector('input[name="telefono"]');
        elementos.inputFecha = elementos.form.querySelector('input[name="fecha"]');
        elementos.inputHora = elementos.form.querySelector('input[name="hora"]');
        elementos.selectPersonas = elementos.form.querySelector('select[name="personas"]');
        elementos.inputMetodoPago = elementos.form.querySelector('select[name="metodo_pago"]');
    }

    // ========== CONFIGURAR TOGGLE USUARIO ==========
    function configurarToggleUsuario() {
        elementos.btnsReservaTipo.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remover active de todos
                elementos.btnsReservaTipo.forEach(b => {
                    b.classList.remove('active');
                    b.style.background = 'transparent';
                    b.style.color = 'var(--gold)';
                });

                // Agregar active al clickeado
                this.classList.add('active');
                this.style.background = 'var(--gold)';
                this.style.color = 'white';

                // Actualizar estado
                const tipo = this.dataset.tipo;
                estado.reservaParaMi = (tipo === 'yo');

                // Mostrar/ocultar campos
                if (estado.reservaParaMi) {
                    elementos.datosContacto.style.display = 'none';
                    // Remover required de campos ocultos
                    elementos.inputNombre.removeAttribute('required');
                    elementos.inputCorreo.removeAttribute('required');
                    elementos.inputTelefono.removeAttribute('required');
                } else {
                    elementos.datosContacto.style.display = 'block';
                    // Agregar required a campos visibles
                    elementos.inputNombre.setAttribute('required', 'required');
                    elementos.inputCorreo.setAttribute('required', 'required');
                    elementos.inputTelefono.setAttribute('required', 'required');
                    // Limpiar campos
                    elementos.inputNombre.value = '';
                    elementos.inputCorreo.value = '';
                    elementos.inputTelefono.value = '';
                }

                console.log('Reserva para:', estado.reservaParaMi ? 'Usuario autenticado' : 'Otra persona');
            });
        });
    }

    // ========== CARGAR DATOS DEL CLIENTE ==========
    async function cargarDatosCliente() {
        try {
            const response = await fetch(CONFIG.API_CLIENTE);
            const result = await response.json();

            if (result.success && result.data) {
                const cliente = result.data;
                // Guardar en inputs hidden
                document.getElementById('clienteNombre').value = cliente.nombre || '';
                document.getElementById('clienteCorreo').value = cliente.correo || '';
                document.getElementById('clienteTelefono').value = cliente.telefono || '';
                
                console.log('Datos del cliente cargados:', cliente);
            }
        } catch (error) {
            console.warn('No se pudieron cargar datos del cliente:', error);
        }
    }

    // ========== TOGGLE SELECTOR DE PLATOS ==========
    function toggleSelectorPlatos() {
        const checked = elementos.checkIncluirPlatos.checked;
        elementos.selectorPlatos.style.display = checked ? 'block' : 'none';

        if (checked && elementos.listadoPlatos.children.length === 1) {
            // Primera vez que se abre, cargar platos
            cargarPlatos();
        }
    }

    // ========== CARGAR PLATOS DESDE API ==========
    async function cargarPlatos() {
        try {
            const response = await fetch(CONFIG.API_PLATOS);
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                renderizarPlatos(result.data);
            } else {
                elementos.listadoPlatos.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;">No hay platos disponibles en este momento</p>';
            }
        } catch (error) {
            console.error('Error cargando platos:', error);
            elementos.listadoPlatos.innerHTML = '<p style="color: #dc3545; text-align: center; padding: 20px;"><i class="fas fa-exclamation-triangle"></i> Error al cargar platos. Intenta recargar la página.</p>';
        }
    }

    // ========== RENDERIZAR PLATOS ==========
    function renderizarPlatos(platos) {
        elementos.listadoPlatos.innerHTML = '';

        platos.forEach(plato => {
            const item = document.createElement('div');
            item.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #ddd; cursor: pointer; transition: background 0.2s;';
            item.innerHTML = `
                <div style="flex: 1;">
                    <strong style="color: #333;">${plato.nombre}</strong>
                    <p style="font-size: 12px; color: #666; margin: 5px 0 0 0;">${plato.descripcion || ''}</p>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: bold; color: var(--gold-dark);">S/ ${plato.precio}</span>
                    <button type="button" class="btn-agregar-plato" data-id="${plato.variante_id}" data-nombre="${plato.nombre}" data-precio="${plato.precio}" style="padding: 8px 15px; background: var(--gold); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            `;

            // Hover effect
            item.addEventListener('mouseenter', () => item.style.background = '#f8f9fa');
            item.addEventListener('mouseleave', () => item.style.background = 'transparent');

            // Click en botón agregar
            const btnAgregar = item.querySelector('.btn-agregar-plato');
            btnAgregar.addEventListener('click', (e) => {
                e.stopPropagation();
                agregarPlato(plato);
            });

            elementos.listadoPlatos.appendChild(item);
        });
    }

    // ========== AGREGAR PLATO A SELECCIÓN ==========
    function agregarPlato(plato) {
        // Verificar si ya está agregado
        const existe = estado.platosSeleccionados.find(p => p.id === plato.variante_id);
        
        if (existe) {
            existe.cantidad++;
        } else {
            estado.platosSeleccionados.push({
                id: plato.variante_id,
                nombre: plato.nombre,
                precio: parseFloat(plato.precio),
                cantidad: 1
            });
        }

        actualizarResumenPlatos();
    }

    // ========== ACTUALIZAR RESUMEN DE PLATOS ==========
    function actualizarResumenPlatos() {
        if (estado.platosSeleccionados.length === 0) {
            elementos.resumenPlatos.style.display = 'none';
            estado.subtotalPlatos = 0;
            calcularTotalSenal(); // Recalcular señal
            return;
        }

        elementos.resumenPlatos.style.display = 'block';
        elementos.listaSeleccionados.innerHTML = '';

        estado.subtotalPlatos = 0;

        estado.platosSeleccionados.forEach((plato, index) => {
            const subtotal = plato.precio * plato.cantidad;
            estado.subtotalPlatos += subtotal;

            const li = document.createElement('li');
            li.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee;';
            li.innerHTML = `
                <span>${plato.nombre} x${plato.cantidad}</span>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span>S/ ${subtotal.toFixed(2)}</span>
                    <button type="button" class="btn-quitar-plato" data-index="${index}" style="padding: 4px 8px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer; font-size: 12px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            const btnQuitar = li.querySelector('.btn-quitar-plato');
            btnQuitar.addEventListener('click', () => quitarPlato(index));

            elementos.listaSeleccionados.appendChild(li);
        });

        elementos.subtotalPlatosSpan.textContent = estado.subtotalPlatos.toFixed(2);
        
        // Recalcular señal con el nuevo subtotal
        calcularTotalSenal();
    }

    // ========== QUITAR PLATO ==========
    function quitarPlato(index) {
        estado.platosSeleccionados.splice(index, 1);
        actualizarResumenPlatos();
    }

    // ========== CALCULAR TOTAL SEÑAL (Mejorado con platos) ==========
    function calcularTotalSenal() {
        const personas = parseInt(elementos.selectPersonas.value) || 0;
        estado.numPersonas = personas;
        
        // Base: S/11 por persona
        let senalBase = personas * CONFIG.SENAL_POR_PERSONA;
        
        // Adicional: 30% del subtotal de platos
        let senalPlatos = estado.subtotalPlatos * (CONFIG.PORCENTAJE_SENAL_PLATOS / 100);
        
        // Total
        estado.totalSenal = senalBase + senalPlatos;
        
        elementos.totalSenalSpan.textContent = estado.totalSenal.toFixed(2);
        
        // Mostrar/ocultar desglose
        if (elementos.desgloseSenalPreview) {
            if (senalPlatos > 0) {
                elementos.desgloseSenalPreview.style.display = 'block';
                elementos.prevPersonas.textContent = personas;
                elementos.prevBase.textContent = senalBase.toFixed(2);
                elementos.prevPlatos.textContent = senalPlatos.toFixed(2);
            } else {
                elementos.desgloseSenalPreview.style.display = 'none';
            }
        }
        
        console.log(`💰 Señal calculada:`);
        console.log(`   - Base: ${personas} personas x S/ ${CONFIG.SENAL_POR_PERSONA} = S/ ${senalBase.toFixed(2)}`);
        console.log(`   - Platos: ${CONFIG.PORCENTAJE_SENAL_PLATOS}% de S/ ${estado.subtotalPlatos.toFixed(2)} = S/ ${senalPlatos.toFixed(2)}`);
        console.log(`   - TOTAL: S/ ${estado.totalSenal.toFixed(2)}`);
    }

    // ========== MOSTRAR INSTRUCCIONES DE PAGO ==========
    function mostrarInstruccionesPago() {
        const metodo = elementos.inputMetodoPago.value;
        elementos.instruccionesPago.style.display = metodo ? 'block' : 'none';
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
            alert('No puedes seleccionar una fecha pasada');
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
            alert(`Nuestro horario es de ${CONFIG.HORARIO_APERTURA}:00 AM a ${CONFIG.HORARIO_CIERRE}:00 PM`);
            e.target.value = '';
            return false;
        }

        return true;
    }

    // ========== MANEJAR ENVÍO DEL FORMULARIO ==========
    async function handleSubmit(e) {
        e.preventDefault();

        console.log('=== Enviando reserva mejorada ===');
        console.log('Estado actual:', estado);

        // Validaciones
        if (!validarFormularioCompleto()) {
            return;
        }

        // Deshabilitar botón
        const textOriginal = elementos.btnSubmit.innerHTML;
        elementos.btnSubmit.disabled = true;
        elementos.btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando reserva...';

        try {
            const formData = prepararDatos();

            console.log('Enviando a:', CONFIG.API_URL);
            const response = await fetch(CONFIG.API_URL, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log('Resultado:', result);

            if (result.success) {
                mostrarExito(result);
                elementos.form.reset();
                estado.platosSeleccionados = [];
                actualizarResumenPlatos();
            } else {
                mostrarError(result.message || 'Error al procesar la reserva');
            }

        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error de conexión. Verifica que XAMPP esté corriendo.');
        } finally {
            elementos.btnSubmit.disabled = false;
            elementos.btnSubmit.innerHTML = textOriginal;
        }
    }

    // ========== PREPARAR DATOS PARA ENVIAR ==========
    function prepararDatos() {
        const formData = new FormData();

        // Datos de contacto (reutilizable para ambas opciones)
        if (estado.reservaParaMi) {
            // ✅ Opción: Para mí (usuario autenticado)
            formData.append('nombre', document.getElementById('clienteNombre').value);
            formData.append('correo', document.getElementById('clienteCorreo').value);
            formData.append('telefono', document.getElementById('clienteTelefono').value);
            console.log('📝 Usando datos del usuario autenticado');
        } else {
            // ✅ Opción: Para otra persona
            formData.append('nombre', elementos.inputNombre.value);
            formData.append('correo', elementos.inputCorreo.value);
            formData.append('telefono', elementos.inputTelefono.value);
            console.log('📝 Usando datos del formulario manual');
        }

        // Datos de la reserva
        formData.append('fecha', elementos.inputFecha.value);
        formData.append('hora', elementos.inputHora.value);
        formData.append('personas', elementos.selectPersonas.value);
        formData.append('comentarios', elementos.form.querySelector('textarea[name="comentarios"]').value);

        // Método de pago y señal
        formData.append('metodo_pago', elementos.inputMetodoPago.value);
        formData.append('total_senal', estado.totalSenal);

        // Platos pre-ordenados (si hay)
        if (estado.platosSeleccionados.length > 0) {
            formData.append('platos', JSON.stringify(estado.platosSeleccionados));
            formData.append('subtotal_platos', estado.subtotalPlatos);
        }

        return formData;
    }

    // ========== VALIDAR FORMULARIO COMPLETO ==========
    function validarFormularioCompleto() {
        // Validar número de personas
        if (!elementos.selectPersonas.value || elementos.selectPersonas.value === '') {
            alert('Selecciona el número de personas');
            return false;
        }

        // Validar método de pago
        if (!elementos.inputMetodoPago.value) {
            alert('Selecciona un método de pago para la señal');
            return false;
        }

        return true;
    }

    // ========== MOSTRAR ÉXITO (Reutilizable para ambas opciones) ==========
    function mostrarExito(result) {
        const data = result.data;
        
        console.log('=== Confirmación de Reserva ===');
        console.log('Tipo:', estado.reservaParaMi ? 'Para mí' : 'Para otra persona');
        console.log('Datos:', data);
        
        // Cerrar cualquier modal anterior
        const modalAnterior = document.getElementById('modalConfirmacionReserva');
        if (modalAnterior) {
            modalAnterior.remove();
        }
        
        // Verificar disponibilidad del módulo del modal
        if (typeof ModalConfirmacionReserva === 'undefined' || typeof ModalConfirmacionReserva.mostrar !== 'function') {
            console.warn('⚠️ Módulo modal no disponible, usando fallback');
            mostrarExitoFallback(data);
            return;
        }
        
        try {
            // ✅ Reutilizar el mismo modal para ambos casos
            ModalConfirmacionReserva.mostrar(data);
            console.log('✅ Modal mostrado correctamente');
        } catch (error) {
            console.error('❌ Error al mostrar modal:', error);
            mostrarExitoFallback(data);
        }
    }

    // ========== MOSTRAR ÉXITO FALLBACK ==========
    function mostrarExitoFallback(data) {
        let mensaje = `¡Reserva Confirmada!

`;
        mensaje += `Número de reserva: #${data.reserva_id}
`;
        mensaje += `Fecha: ${formatearFecha(data.fecha)}
`;
        mensaje += `Hora: ${data.hora}
`;
        mensaje += `Personas: ${data.num_comensales}

`;
        
        if (data.total_senal > 0) {
            mensaje += `SEÑAL A PAGAR
`;
            mensaje += `Monto: S/ ${data.total_senal.toFixed(2)}
`;
            mensaje += `Método: ${formatearMetodoPago(data.metodo_pago)}

`;
        }
        
        if (data.platos_pre_ordenados && data.platos_pre_ordenados.length > 0) {
            mensaje += `PLATOS PRE-ORDENADOS:
`;
            data.platos_pre_ordenados.forEach(plato => {
                mensaje += `• ${plato.nombre} x${plato.cantidad} - S/ ${(plato.precio * plato.cantidad).toFixed(2)}
`;
            });
            mensaje += `Subtotal: S/ ${data.subtotal_platos.toFixed(2)}

`;
        }
        
        mensaje += `Token de cancelación: ${data.token}

`;
        mensaje += `Te contactaremos pronto para confirmar tu reserva.`;
        
        alert(mensaje);
    }

    // ========== MOSTRAR ERROR ==========
    function mostrarError(mensaje) {
        alert('Error: ' + mensaje);
    }

    // ========== FORMATEAR FECHA ==========
    function formatearFecha(fecha) {
        const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', opciones);
    }

    // ========== FORMATEAR MÉTODO DE PAGO ==========
    function formatearMetodoPago(metodo) {
        const metodos = {
            'yape': 'Yape',
            'plin': 'Plin',
            'transferencia': 'Transferencia Bancaria',
            'tarjeta': 'Tarjeta de Crédito/Débito'
        };
        return metodos[metodo] || metodo;
    }

    // ========== EJECUTAR AL CARGAR ==========
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
