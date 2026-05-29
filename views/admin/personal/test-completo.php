<?php
// Simulación de datos para prueba
$empleados = [
    [
        'EmpleadoID' => 1,
        'Nombre_Apellidos' => 'Carlos Mendoza Ríos',
        'DNI' => '45231876',
        'Telefono' => '987654321',
        'Sueldo' => 2500.00,
        'Estado' => 'Activo',
        'Rol' => 'Administrador',
        'Turno' => 'Completo',
        'Usuario_Sistema' => 'admin',
        'dias_trabajando' => 500
    ],
    [
        'EmpleadoID' => 2,
        'Nombre_Apellidos' => 'María Torres Huanca',
        'DNI' => '52341987',
        'Telefono' => '976543218',
        'Sueldo' => 1800.00,
        'Estado' => 'Activo',
        'Rol' => 'Cajero',
        'Turno' => 'Mañana',
        'Usuario_Sistema' => 'cajera.maria',
        'dias_trabajando' => 200
    ],
    [
        'EmpleadoID' => 3,
        'Nombre_Apellidos' => 'José Quispe Lima',
        'DNI' => '63452198',
        'Telefono' => '965432187',
        'Sueldo' => 1500.00,
        'Estado' => 'Activo',
        'Rol' => 'Mozo',
        'Turno' => 'Mañana',
        'Usuario_Sistema' => 'mozo.jose',
        'dias_trabajando' => 150
    ]
];

$roles = [
    ['RolID' => 1, 'Nombre' => 'Administrador'],
    ['RolID' => 2, 'Nombre' => 'Cajero'],
    ['RolID' => 3, 'Nombre' => 'Mozo']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Completa - Empleados</title>
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div style="padding: 20px;">
        <h1><i class="fas fa-users"></i> Prueba - Gestión de Empleados</h1>
        
        <!-- Filtros -->
        <div style="display: flex; gap: 15px; margin: 20px 0;">
            <select id="filtroRol" class="form-control" style="width: 200px;">
                <option value="">Todos los roles</option>
                <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol['Nombre'] ?>"><?= $rol['Nombre'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="buscarEmpleado" placeholder="Buscar por nombre o DNI..." class="form-control" style="width: 300px;">
            <button onclick="testFiltros()" class="btn btn-primary">Probar Filtros</button>
        </div>

        <!-- Tabla -->
        <div class="admin-table">
            <table id="tablaEmpleados">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>DNI</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Turno</th>
                        <th>Sueldo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $empleado): ?>
                    <tr data-rol="<?= $empleado['Rol'] ?>" data-estado="<?= $empleado['Estado'] ?>">
                        <td>
                            <strong><?= htmlspecialchars($empleado['Nombre_Apellidos']) ?></strong>
                            <br>
                            <small style="color: #6c757d;">
                                Usuario: <?= $empleado['Usuario_Sistema'] ?>
                            </small>
                        </td>
                        <td><?= htmlspecialchars($empleado['DNI']) ?></td>
                        <td><?= htmlspecialchars($empleado['Telefono']) ?></td>
                        <td>
                            <span class="badge badge-info"><?= htmlspecialchars($empleado['Rol']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($empleado['Turno']) ?></td>
                        <td>S/ <?= number_format($empleado['Sueldo'], 2) ?></td>
                        <td>
                            <span class="badge badge-success"><?= $empleado['Estado'] ?></span>
                        </td>
                        <td>
                            <button onclick="editarEmpleado(<?= $empleado['EmpleadoID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="verDetalles(<?= $empleado['EmpleadoID'] ?>)" class="btn btn-sm" style="background: #6c757d; color: white;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div id="resultados" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <h4>Resultados de Prueba:</h4>
            <div id="log"></div>
        </div>
    </div>

    <script>
    let logDiv = document.getElementById('log');
    
    function log(mensaje) {
        logDiv.innerHTML += '<p>' + mensaje + '</p>';
        console.log(mensaje);
    }
    
    // Configurar filtros
    document.addEventListener('DOMContentLoaded', function() {
        log('✓ DOM cargado correctamente');
        
        document.getElementById('filtroRol').addEventListener('change', function() {
            log('Filtro rol cambiado a: ' + this.value);
            filtrarTabla();
        });
        
        document.getElementById('buscarEmpleado').addEventListener('input', function() {
            log('Búsqueda: ' + this.value);
            filtrarTabla();
        });
        
        log('✓ Event listeners configurados');
    });

    function filtrarTabla() {
        const filtroRol = document.getElementById('filtroRol').value;
        const busqueda = document.getElementById('buscarEmpleado').value.toLowerCase();
        const filas = document.querySelectorAll('#tablaEmpleados tbody tr');
        
        let visibles = 0;
        
        filas.forEach(fila => {
            const rol = fila.dataset.rol;
            const texto = fila.textContent.toLowerCase();
            
            const coincideRol = !filtroRol || rol === filtroRol;
            const coincideBusqueda = !busqueda || texto.includes(busqueda);
            
            if (coincideRol && coincideBusqueda) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });
        
        log(`Filtrado completado: ${visibles} empleados visibles de ${filas.length} totales`);
    }

    function editarEmpleado(id) {
        log('✓ Función editarEmpleado llamada con ID: ' + id);
        alert('Editando empleado ID: ' + id);
    }

    function verDetalles(id) {
        log('✓ Función verDetalles llamada con ID: ' + id);
        alert('Viendo detalles del empleado ID: ' + id);
    }
    
    function testFiltros() {
        log('=== INICIANDO PRUEBA DE FILTROS ===');
        
        // Probar filtro por rol
        document.getElementById('filtroRol').value = 'Mozo';
        filtrarTabla();
        
        setTimeout(() => {
            // Limpiar filtros
            document.getElementById('filtroRol').value = '';
            document.getElementById('buscarEmpleado').value = '';
            filtrarTabla();
            
            // Probar búsqueda
            document.getElementById('buscarEmpleado').value = 'carlos';
            filtrarTabla();
            
            setTimeout(() => {
                // Limpiar todo
                document.getElementById('buscarEmpleado').value = '';
                filtrarTabla();
                log('=== PRUEBA COMPLETADA ===');
            }, 1000);
        }, 1000);
    }
    
    log('✓ Script cargado correctamente');
    </script>
</body>
</html>