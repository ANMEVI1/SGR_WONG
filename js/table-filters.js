/**
 * Sistema de Filtros Reutilizable para Tablas Admin
 * Uso: new TableFilter('idTabla', { filtros: ['campo1', 'campo2'], busqueda: 'idInput' })
 */

class TableFilter {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.tbody = this.table.querySelector('tbody');
        this.rows = this.tbody.querySelectorAll('tr');
        this.options = {
            searchInput: options.searchInput || null,
            filters: options.filters || [], // Array de objetos {id: 'selectId', attribute: 'data-attribute'}
            onFilter: options.onFilter || null,
            caseSensitive: options.caseSensitive || false,
            ...options
        };
        
        this.init();
    }
    
    init() {
        // Configurar búsqueda
        if (this.options.searchInput) {
            const searchInput = document.getElementById(this.options.searchInput);
            if (searchInput) {
                searchInput.addEventListener('input', () => this.filter());
            }
        }
        
        // Configurar filtros
        this.options.filters.forEach(filter => {
            const filterElement = document.getElementById(filter.id);
            if (filterElement) {
                filterElement.addEventListener('change', () => this.filter());
            }
        });
        
        console.log(`TableFilter inicializado para tabla: ${this.table.id}`);
    }
    
    filter() {
        const searchTerm = this.getSearchTerm();
        const filterValues = this.getFilterValues();
        let visibleCount = 0;
        
        this.rows.forEach(row => {
            const shouldShow = this.shouldShowRow(row, searchTerm, filterValues);
            row.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleCount++;
        });
        
        // Callback personalizado
        if (this.options.onFilter) {
            this.options.onFilter({
                total: this.rows.length,
                visible: visibleCount,
                searchTerm,
                filterValues
            });
        }
        
        // Mostrar mensaje si no hay resultados
        this.showNoResultsMessage(visibleCount === 0);
        
        return visibleCount;
    }
    
    getSearchTerm() {
        if (!this.options.searchInput) return '';
        const input = document.getElementById(this.options.searchInput);
        return input ? (this.options.caseSensitive ? input.value : input.value.toLowerCase()) : '';
    }
    
    getFilterValues() {
        const values = {};
        this.options.filters.forEach(filter => {
            const element = document.getElementById(filter.id);
            if (element) {
                values[filter.attribute] = element.value;
            }
        });
        return values;
    }
    
    shouldShowRow(row, searchTerm, filterValues) {
        // Verificar filtros
        for (const [attribute, value] of Object.entries(filterValues)) {
            if (value && row.dataset[attribute.replace('data-', '')] !== value) {
                return false;
            }
        }
        
        // Verificar búsqueda
        if (searchTerm) {
            const rowText = this.options.caseSensitive ? row.textContent : row.textContent.toLowerCase();
            if (!rowText.includes(searchTerm)) {
                return false;
            }
        }
        
        return true;
    }
    
    showNoResultsMessage(show) {
        let noResultsRow = this.tbody.querySelector('.no-results-row');
        
        if (show && !noResultsRow) {
            // Crear fila de "sin resultados"
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="100%" style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    <strong>No se encontraron resultados</strong><br>
                    <small>Intenta ajustar los filtros de búsqueda</small>
                </td>
            `;
            this.tbody.appendChild(noResultsRow);
        } else if (!show && noResultsRow) {
            noResultsRow.remove();
        }
    }
    
    // Métodos públicos
    clearFilters() {
        // Limpiar búsqueda
        if (this.options.searchInput) {
            const input = document.getElementById(this.options.searchInput);
            if (input) input.value = '';
        }
        
        // Limpiar filtros
        this.options.filters.forEach(filter => {
            const element = document.getElementById(filter.id);
            if (element) element.value = '';
        });
        
        this.filter();
    }
    
    getStats() {
        const visible = Array.from(this.rows).filter(row => row.style.display !== 'none').length;
        return {
            total: this.rows.length,
            visible: visible,
            hidden: this.rows.length - visible
        };
    }
}

// Función helper para inicializar filtros comunes
function initEmployeeFilters(tableId = 'tablaEmpleados') {
    return new TableFilter(tableId, {
        searchInput: 'buscarEmpleado',
        filters: [
            { id: 'filtroRol', attribute: 'data-rol' },
            { id: 'filtroEstado', attribute: 'data-estado' }
        ],
        onFilter: function(stats) {
            console.log(`Filtrado: ${stats.visible}/${stats.total} empleados visibles`);
            
            // Actualizar contador si existe
            const counter = document.getElementById('empleados-counter');
            if (counter) {
                counter.textContent = `${stats.visible} de ${stats.total} empleados`;
            }
        }
    });
}

function initUserFilters(tableId = 'tablaUsuarios') {
    return new TableFilter(tableId, {
        searchInput: 'buscarUsuario',
        filters: [
            { id: 'filtroEstado', attribute: 'data-estado' }
        ],
        onFilter: function(stats) {
            console.log(`Filtrado: ${stats.visible}/${stats.total} usuarios visibles`);
        }
    });
}

// Exportar para uso global
window.TableFilter = TableFilter;
window.initEmployeeFilters = initEmployeeFilters;
window.initUserFilters = initUserFilters;