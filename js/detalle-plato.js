/**
 * Detalle de Plato - Sistema de visualización y selección de platos
 * Integrado con API, variantes y modificadores
 */

class DetallePlato {
    constructor(platoId) {
        this.platoId = platoId;
        this.plato = null;
        this.cantidad = 1;
        this.varianteSeleccionada = null;
        this.modificadoresSeleccionados = {};
        
        this.init();
    }
    
    async init() {
        try {
            await this.cargarPlato();
            this.renderPlato();
            this.setupEventListeners();
        } catch (error) {
            console.error('Error inicializando detalle:', error);
            this.mostrarError('No se pudo cargar el plato');
        }
    }
    
    async cargarPlato() {
        try {
            const response = await fetch(`api/plato-detalle.php?id=${this.platoId}`);
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar plato');
            }
            
            this.plato = data.data;
            
            // Seleccionar primera variante por defecto
            if (this.plato.variantes?.length > 0) {
                this.varianteSeleccionada = this.plato.variantes[0];
            }
            
            // Inicializar modificadores obligatorios
            this.plato.modificadores?.forEach(grupo => {
                if (grupo.obligatorio && grupo.opciones.length > 0) {
                    this.modificadoresSeleccionados[grupo.id] = [grupo.opciones[0].id];
                }
            });
            
        } catch (error) {
            console.error('Error cargando plato:', error);
            throw error;
        }
    }
    
    renderPlato() {
        if (!this.plato) return;
        
        const container = document.getElementById('productoContainer');
        const placeholderSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Crect fill='%23f0ece4' width='400' height='400'/%3E%3Ctext fill='%23c9954a' font-size='24' font-weight='bold' x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle'%3ESin imagen%3C/text%3E%3C/svg%3E";
        
        const badges = [];
        if (this.plato.es_promo) badges.push('<span class="badge badge-promo">PROMOCIÓN</span>');
        if (this.plato.es_top) badges.push('<span class="badge badge-popular">TOP VENTAS</span>');
        
        container.innerHTML = `
            <!-- Imagen del producto -->
            <div class="producto-imagen">
                <img src="${this.plato.imagen}" 
                     alt="${this.plato.nombre}"
                     onerror="if(this.src!=='${placeholderSvg}')this.src='${placeholderSvg}'">
            </div>
            
            <!-- Información del producto -->
            <div class="producto-info">
                ${badges.length > 0 ? `<div class="producto-badges">${badges.join('')}</div>` : ''}
                
                <h1>${this.plato.nombre}</h1>
                
                <p>${this.plato.descripcion || 'Delicioso plato de nuestra carta.'}</p>
                
                <div class="producto-precio">
                    S/ <span id="precioActual">${this.calcularPrecioTotal().toFixed(2)}</span>
                </div>
                
                ${this.renderVariantes()}
                
                ${this.renderModificadores()}
                
                <!-- Control de cantidad -->
                <div class="cantidad-control">
                    <button onclick="detallePlato.cambiarCantidad(-1)" aria-label="Reducir cantidad">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" 
                           id="cantidadInput" 
                           value="${this.cantidad}" 
                           min="1" 
                           max="20"
                           readonly>
                    <button onclick="detallePlato.cambiarCantidad(1)" aria-label="Aumentar cantidad">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <!-- Botones de acción -->
                <button onclick="detallePlato.agregarAlCarrito()" 
                        class="btn btn-primary btn-full">
                    <i class="fas fa-shopping-cart"></i> 
                    Añadir al Carrito - S/ ${(this.calcularPrecioTotal() * this.cantidad).toFixed(2)}
                </button>
                
                <a href="carta.php" class="btn btn-secondary btn-full">
                    <i class="fas fa-arrow-left"></i> 
                    Volver a la Carta
                </a>
                
                ${this.plato.categoria ? `
                <div class="producto-caracteristicas">
                    <h3>Información</h3>
                    <ul>
                        <li>Categoría: ${this.plato.categoria}</li>
                        ${this.plato.variantes?.length > 1 ? '<li>Disponible en múltiples presentaciones</li>' : ''}
                        ${this.plato.modificadores?.length > 0 ? '<li>Personalizable a tu gusto</li>' : ''}
                    </ul>
                </div>
                ` : ''}
            </div>
        `;
    }
    
    renderVariantes() {
        if (!this.plato.variantes || this.plato.variantes.length <= 1) {
            return '';
        }
        
        return `
            <div style="margin: 20px 0;">
                <h3 style="font-size: 1.2rem; margin-bottom: 12px; color: var(--text);">
                    Selecciona tamaño:
                </h3>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    ${this.plato.variantes.map(variante => `
                        <button onclick="detallePlato.seleccionarVariante(${variante.id})"
                                class="btn ${this.varianteSeleccionada?.id === variante.id ? 'btn-primary' : 'btn-secondary'}"
                                style="padding: 12px 24px;">
                            ${variante.nombre}
                            ${variante.descripcion ? `<small style="display: block; font-size: 0.85em; opacity: 0.9;">${variante.descripcion}</small>` : ''}
                            <strong style="display: block; margin-top: 4px;">S/ ${variante.precio.toFixed(2)}</strong>
                        </button>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    renderModificadores() {
        if (!this.plato.modificadores || this.plato.modificadores.length === 0) {
            return '';
        }
        
        return this.plato.modificadores.map(grupo => `
            <div style="margin: 24px 0; padding: 20px; background: var(--bg); border-radius: var(--radius-lg);">
                <h3 style="font-size: 1.15rem; margin-bottom: 12px; color: var(--text);">
                    ${grupo.nombre}
                    ${grupo.obligatorio ? '<span style="color: var(--primary); font-size: 0.85em;">(Obligatorio)</span>' : ''}
                </h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    ${grupo.opciones.map(opcion => {
                        const isSelected = this.modificadoresSeleccionados[grupo.id]?.includes(opcion.id);
                        const inputType = grupo.tipo === 'multiple' ? 'checkbox' : 'radio';
                        const inputName = `modificador_${grupo.id}`;
                        
                        return `
                            <label style="display: flex; align-items: center; gap: 12px; padding: 12px; 
                                         background: white; border-radius: var(--radius); cursor: pointer;
                                         border: 2px solid ${isSelected ? 'var(--primary)' : 'var(--border)'};">
                                <input type="${inputType}" 
                                       name="${inputName}"
                                       value="${opcion.id}"
                                       ${isSelected ? 'checked' : ''}
                                       onchange="detallePlato.toggleModificador(${grupo.id}, ${opcion.id}, '${grupo.tipo}')"
                                       style="width: 20px; height: 20px; cursor: pointer;">
                                <span style="flex: 1; font-weight: 500;">${opcion.nombre}</span>
                                ${opcion.precio_extra > 0 ? `<span style="color: var(--primary); font-weight: 700;">+S/ ${opcion.precio_extra.toFixed(2)}</span>` : ''}
                            </label>
                        `;
                    }).join('')}
                </div>
            </div>
        `).join('');
    }
    
    seleccionarVariante(varianteId) {
        this.varianteSeleccionada = this.plato.variantes.find(v => v.id === varianteId);
        this.actualizarPrecio();
        this.renderPlato();
    }
    
    toggleModificador(grupoId, opcionId, tipo) {
        if (tipo === 'simple') {
            // Radio: solo una opción
            this.modificadoresSeleccionados[grupoId] = [opcionId];
        } else {
            // Checkbox: múltiples opciones
            if (!this.modificadoresSeleccionados[grupoId]) {
                this.modificadoresSeleccionados[grupoId] = [];
            }
            
            const index = this.modificadoresSeleccionados[grupoId].indexOf(opcionId);
            if (index > -1) {
                this.modificadoresSeleccionados[grupoId].splice(index, 1);
            } else {
                this.modificadoresSeleccionados[grupoId].push(opcionId);
            }
        }
        
        this.actualizarPrecio();
    }
    
    cambiarCantidad(delta) {
        const nuevaCantidad = this.cantidad + delta;
        if (nuevaCantidad >= 1 && nuevaCantidad <= 20) {
            this.cantidad = nuevaCantidad;
            document.getElementById('cantidadInput').value = this.cantidad;
            this.actualizarPrecio();
        }
    }
    
    calcularPrecioTotal() {
        let precioBase = this.varianteSeleccionada?.precio || 0;
        
        // Sumar extras de modificadores
        this.plato.modificadores?.forEach(grupo => {
            const seleccionados = this.modificadoresSeleccionados[grupo.id] || [];
            seleccionados.forEach(opcionId => {
                const opcion = grupo.opciones.find(o => o.id === opcionId);
                if (opcion) {
                    precioBase += opcion.precio_extra;
                }
            });
        });
        
        return precioBase;
    }
    
    actualizarPrecio() {
        const precioUnitario = this.calcularPrecioTotal();
        const precioTotal = precioUnitario * this.cantidad;
        
        document.getElementById('precioActual').textContent = precioUnitario.toFixed(2);
        
        const btnCarrito = document.querySelector('.btn-primary.btn-full');
        if (btnCarrito) {
            btnCarrito.innerHTML = `
                <i class="fas fa-shopping-cart"></i> 
                Añadir al Carrito - S/ ${precioTotal.toFixed(2)}
            `;
        }
    }
    
    agregarAlCarrito() {
        if (!this.varianteSeleccionada) {
            app.mostrarNotificacion('Por favor selecciona una variante', 'warning');
            return;
        }
        
        // Validar modificadores obligatorios
        const faltanObligatorios = this.plato.modificadores?.some(grupo => {
            return grupo.obligatorio && (!this.modificadoresSeleccionados[grupo.id] || 
                   this.modificadoresSeleccionados[grupo.id].length === 0);
        });
        
        if (faltanObligatorios) {
            app.mostrarNotificacion('Por favor completa las opciones obligatorias', 'warning');
            return;
        }
        
        // Construir detalle de modificadores
        const modificadoresTexto = [];
        this.plato.modificadores?.forEach(grupo => {
            const seleccionados = this.modificadoresSeleccionados[grupo.id] || [];
            seleccionados.forEach(opcionId => {
                const opcion = grupo.opciones.find(o => o.id === opcionId);
                if (opcion) {
                    modificadoresTexto.push(opcion.nombre);
                }
            });
        });
        
        const precioUnitario = this.calcularPrecioTotal();
        
        // Agregar al carrito con detalles
        for (let i = 0; i < this.cantidad; i++) {
            const item = {
                id: this.plato.id,
                nombre: this.plato.nombre,
                variante: this.varianteSeleccionada.nombre,
                modificadores: modificadoresTexto,
                precio: precioUnitario,
                imagen: this.plato.imagen
            };
            
            app.carrito.push(item);
        }
        
        app.saveCarrito();
        app.actualizarContadorCarrito();
        
        app.mostrarNotificacion(
            `${this.cantidad} ${this.plato.nombre} añadido${this.cantidad > 1 ? 's' : ''} al carrito`, 
            'success'
        );
        
        // Resetear cantidad
        this.cantidad = 1;
        document.getElementById('cantidadInput').value = 1;
        this.actualizarPrecio();
    }
    
    setupEventListeners() {
        // Listener para cambio manual de cantidad
        const cantidadInput = document.getElementById('cantidadInput');
        if (cantidadInput) {
            cantidadInput.addEventListener('change', (e) => {
                let valor = parseInt(e.target.value) || 1;
                valor = Math.max(1, Math.min(20, valor));
                this.cantidad = valor;
                e.target.value = valor;
                this.actualizarPrecio();
            });
        }
    }
    
    mostrarError(mensaje) {
        const container = document.getElementById('productoContainer');
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--primary); margin-bottom: 20px;"></i>
                <h2 style="color: var(--text); margin-bottom: 12px;">${mensaje}</h2>
                <a href="carta.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-arrow-left"></i> Volver a la Carta
                </a>
            </div>
        `;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    if (typeof PLATO_ID !== 'undefined') {
        window.detallePlato = new DetallePlato(PLATO_ID);
    }
});
