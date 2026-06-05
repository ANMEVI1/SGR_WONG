<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Obtener usuarios con sus roles
$usuarios = $db->fetchAll(
    "SELECT u.UsuarioID, u.Login, u.Estado, u.TipUsuID,
            tu.Descripcion as RolDescripcion, tu.Scope,
            COALESCE(c.Nombre_Apellidos, e.Nombre_Apellidos, 'Sin nombre') as NombreCompleto,
            CASE 
                WHEN c.ClienteID IS NOT NULL THEN 'Cliente'
                WHEN e.EmpleadoID IS NOT NULL THEN 'Empleado'
                ELSE 'Usuario'
            END as TipoPersona
     FROM Usuario u
     JOIN Tipo_Usuario tu ON u.TipUsuID = tu.TipUsuID
     LEFT JOIN Cliente c ON u.UsuarioID = c.UsuarioID
     LEFT JOIN Empleado e ON u.UsuarioID = e.UsuarioID
     ORDER BY u.UsuarioID DESC"
);

// Obtener tipos de usuario para el formulario
$tiposUsuario = $db->fetchAll(
    "SELECT TipUsuID, Descripcion, Scope FROM Tipo_Usuario ORDER BY Descripcion"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Chifa Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: transparent;
            color: var(--admin-text-light);
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: var(--admin-transition);
            font-size: 0.95rem;
        }
        
        .tab-btn:hover {
            color: var(--admin-text);
            background: rgba(14, 1, 1, 0.05);
        }
        
        .tab-btn.active {
            color: var(--admin-primary);
            border-bottom-color: var(--admin-primary);
            background: rgba(14, 1, 1, 0.05);
        }
        
        .tab-content {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-user-cog"></i> Usuarios del Sistema</h1>
                <p>Gestiona los usuarios que pueden acceder al sistema (empleados y clientes web con login)</p>
            </div>
            
            <!-- Mensaje informativo -->
            <div style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #1565C0; font-weight: 600;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Usuarios del Sistema:</strong> Personas con credenciales de acceso (login y contraseña)
                </p>
                <small style="color: #1976D2; display: block; margin-top: 8px;">
                    • <strong>Empleados:</strong> Personal del restaurante (administradores, cajeros, mozos, almaceneros)<br>
                    • <strong>Clientes Web:</strong> Clientes que se registran desde la página web para hacer pedidos online<br>
                    • <strong>Nota:</strong> Para clientes sin usuario (ventas presenciales), usa la sección "Clientes" en Punto de Venta
                </small>
            </div>

            <!-- Pestañas para separar tipos de usuarios -->
            <div style="display: flex; gap: 5px; margin-bottom: 30px; border-bottom: 2px solid var(--admin-border);">
                <button class="tab-btn active" onclick="switchTab('empleados')" id="tabEmpleados">
                    <i class="fas fa-user-tie"></i> Empleados (Personal)
                </button>
                <button class="tab-btn" onclick="switchTab('clientes')" id="tabClientes">
                    <i class="fas fa-laptop"></i> Clientes Web (Con Login)
                </button>
            </div>

            <!-- Contenido de Empleados -->
            <div id="empleadosContent" class="tab-content active">
                <!-- Acciones Rápidas -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <button class="btn btn-primary" onclick="openModal('createUser', 'empleado')">
                            <i class="fas fa-plus"></i> Crear Usuario Empleado
                        </button>
                        <button class="btn btn-outline" onclick="refreshTable()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                    <div style="display: flex; gap: 15px;">
                        <input type="text" id="searchEmpleados" placeholder="Buscar empleados..." 
                               style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); width: 280px; font-weight: 500;">
                        <select id="filterRoleEmpleados" style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-weight: 500;">
                            <option value="">Todos los cargos</option>
                            <?php foreach ($tiposUsuario as $tipo): ?>
                                <?php if ($tipo['Scope'] === 'backoffice'): ?>
                                    <option value="<?= $tipo['TipUsuID'] ?>"><?= htmlspecialchars($tipo['Descripcion']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button onclick="limpiarFiltrosEmpleados()" class="btn btn-outline" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Contador de resultados empleados -->
                <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                    <span id="empleados-counter">Cargando empleados...</span>
                </div>

                <!-- Tabla de Empleados -->
                <div class="admin-table">
                    <table id="empleadosTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Empleado</th>
                                <th>Nombre Completo</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php if ($usuario['Scope'] === 'backoffice'): ?>
                                <tr data-user-id="<?= $usuario['UsuarioID'] ?>" data-role-id="<?= $usuario['TipUsuID'] ?>">
                                    <td><?= $usuario['UsuarioID'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($usuario['Login']) ?></strong>
                                        <br>
                                        <small style="color: var(--admin-text-light);">Personal del restaurante</small>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['NombreCompleto']) ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($usuario['RolDescripcion']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $usuario['Estado'] === 'Activo' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $usuario['Estado'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm" onclick="editUser(<?= $usuario['UsuarioID'] ?>, 'empleado')" 
                                                style="padding: 5px 10px; margin-right: 5px; background: #17a2b8; color: white;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm" onclick="toggleUserStatus(<?= $usuario['UsuarioID'] ?>, '<?= $usuario['Estado'] ?>')"
                                                style="padding: 5px 10px; background: <?= $usuario['Estado'] === 'Activo' ? '#dc3545' : '#28a745' ?>; color: white;">
                                            <i class="fas fa-<?= $usuario['Estado'] === 'Activo' ? 'ban' : 'check' ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contenido de Clientes Web -->
            <div id="clientesContent" class="tab-content" style="display: none;">
                <!-- Acciones Rápidas -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <div>
                        <button class="btn btn-success" onclick="openModal('createUser', 'cliente')">
                            <i class="fas fa-plus"></i> Crear Usuario Cliente Web
                        </button>
                        <button class="btn btn-outline" onclick="refreshTable()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                    <div style="display: flex; gap: 15px;">
                        <input type="text" id="searchClientes" placeholder="Buscar clientes..." 
                               style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); width: 280px; font-weight: 500;">
                        <select id="filterEstadoClientes" style="padding: 12px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-weight: 500;">
                            <option value="">Todos los estados</option>
                            <option value="Activo">Activos</option>
                            <option value="Inactivo">Inactivos</option>
                        </select>
                        <button onclick="limpiarFiltrosClientes()" class="btn btn-outline" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Contador de resultados clientes -->
                <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                    <span id="clientes-counter">Cargando clientes...</span>
                </div>

                <!-- Tabla de Clientes Web -->
                <div class="admin-table">
                    <table id="clientesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Nombre Completo</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php if ($usuario['Scope'] === 'web'): ?>
                                <tr data-user-id="<?= $usuario['UsuarioID'] ?>" data-role-id="<?= $usuario['TipUsuID'] ?>">
                                    <td><?= $usuario['UsuarioID'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($usuario['Login']) ?></strong>
                                        <br>
                                        <small style="color: var(--admin-text-light);">Cliente web</small>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['NombreCompleto']) ?></td>
                                    <td>-</td>
                                    <td>
                                        <span class="badge <?= $usuario['Estado'] === 'Activo' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $usuario['Estado'] ?>
                                        </span>
                                    </td>
                                    <td>Web</td>
                                    <td>
                                        <button class="btn btn-sm" onclick="editUser(<?= $usuario['UsuarioID'] ?>, 'cliente')" 
                                                style="padding: 5px 10px; margin-right: 5px; background: #17a2b8; color: white;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm" onclick="toggleUserStatus(<?= $usuario['UsuarioID'] ?>, '<?= $usuario['Estado'] ?>')"
                                                style="padding: 5px 10px; background: <?= $usuario['Estado'] === 'Activo' ? '#dc3545' : '#28a745' ?>; color: white;">
                                            <i class="fas fa-<?= $usuario['Estado'] === 'Activo' ? 'ban' : 'check' ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para Crear/Editar Usuario -->
    <div id="userModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div class="modal" style="background: var(--admin-white); border-radius: var(--admin-radius-lg); box-shadow: var(--admin-shadow-lg); max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div style="padding: 35px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid var(--admin-border);">
                    <h3 id="modalTitle" style="margin: 0; color: var(--admin-text); font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-user-plus" style="color: var(--admin-primary);"></i>
                        Nuevo Usuario
                    </h3>
                    <button onclick="closeModal()" style="background: none; border: none; font-size: 1.8rem; cursor: pointer; color: var(--admin-text-light); padding: 5px; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; transition: var(--admin-transition);" onmouseover="this.style.background='var(--admin-bg)'" onmouseout="this.style.background='none'">×</button>
                </div>

                <form id="userForm">
                    <input type="hidden" id="userId" name="userId">
                    <input type="hidden" id="actionInput" name="action" value="create">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="userLogin" style="font-weight: 700; color: var(--admin-text); margin-bottom: 8px; display: block;">Email/Usuario *</label>
                            <input type="email" id="userLogin" name="login" class="form-control" required 
                                   style="width: 100%; padding: 14px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-size: 1rem; transition: var(--admin-transition);" 
                                   onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
                        </div>
                        <div class="form-group">
                            <label for="userRole" style="font-weight: 700; color: var(--admin-text); margin-bottom: 8px; display: block;">Rol *</label>
                            <select id="userRole" name="role" class="form-control" required 
                                    style="width: 100%; padding: 14px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-size: 1rem; transition: var(--admin-transition);" 
                                    onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
                                <option value="">Seleccionar rol</option>
                                <?php foreach ($tiposUsuario as $tipo): ?>
                                    <option value="<?= $tipo['TipUsuID'] ?>" data-scope="<?= $tipo['Scope'] ?>">
                                        <?= htmlspecialchars($tipo['Descripcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userPassword" style="font-weight: 700; color: var(--admin-text); margin-bottom: 8px; display: block;">Contraseña *</label>
                            <input type="password" id="userPassword" name="password" class="form-control" required 
                                   style="width: 100%; padding: 14px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-size: 1rem; transition: var(--admin-transition);" 
                                   onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
                            <small style="color: var(--admin-text-light); font-size: 0.85rem; margin-top: 5px; display: block;">Mínimo 6 caracteres</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword" style="font-weight: 700; color: var(--admin-text); margin-bottom: 8px; display: block;">Confirmar Contraseña *</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" required 
                                   style="width: 100%; padding: 14px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-size: 1rem; transition: var(--admin-transition);" 
                                   onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
                        </div>
                    </div>

                    <div id="clientFields" style="display: none; margin-top: 25px; padding: 25px; background: var(--admin-bg); border-radius: var(--admin-radius); border-left: 4px solid var(--admin-primary);">
                        <h4 style="margin: 0 0 20px 0; color: var(--admin-text); font-weight: 700; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-user" style="color: var(--admin-primary);"></i>
                            Datos del Cliente
                        </h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="clientName" style="font-weight: 700; color: var(--admin-text); margin-bottom: 8px; display: block;">Nombre Completo *</label>
                                <input type="text" id="clientName" name="clientName" class="form-control" 
                                       style="width: 100%; padding: 14px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-size: 1rem; transition: var(--admin-transition); background: white;" 
                                       onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
                            </div>
                            <div class="form-group">
                                <label for="clientPhone" style="font-weight: 700; color: var(--admin-text); margin-bottom: 8px; display: block;">Teléfono *</label>
                                <input type="tel" id="clientPhone" name="clientPhone" class="form-control" 
                                       style="width: 100%; padding: 14px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-size: 1rem; transition: var(--admin-transition); background: white;" 
                                       onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="clientAddress" style="font-weight: 700; color: var(--admin-text); margin-bottom: 8px; display: block;">Dirección</label>
                            <input type="text" id="clientAddress" name="clientAddress" class="form-control" 
                                   style="width: 100%; padding: 14px 16px; border: 2px solid var(--admin-border); border-radius: var(--admin-radius); font-size: 1rem; transition: var(--admin-transition); background: white;" 
                                   onfocus="this.style.borderColor='var(--admin-primary)'" onblur="this.style.borderColor='var(--admin-border)'">
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 35px; padding-top: 25px; border-top: 2px solid var(--admin-border);">
                        <button type="button" onclick="closeModal()" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentUsers = <?= json_encode($usuarios) ?>;
        let isEditing = false;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            setupRoleChange();
            
            // Inicializar contadores
            updateCounters();
        });
        
        function updateCounters() {
            // Contar empleados
            const empleadosRows = document.querySelectorAll('#empleadosTable tbody tr');
            document.getElementById('empleados-counter').textContent = `${empleadosRows.length} empleados registrados`;
            
            // Contar clientes
            const clientesRows = document.querySelectorAll('#clientesTable tbody tr');
            document.getElementById('clientes-counter').textContent = `${clientesRows.length} clientes registrados`;
        }

        // Función para cambiar pestañas
        function switchTab(tabName) {
            // Ocultar todos los contenidos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
            });
            
            // Remover clase active de todos los botones
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar contenido seleccionado
            const contentId = tabName + 'Content';
            const tabBtnId = 'tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1);
            
            document.getElementById(contentId).style.display = 'block';
            document.getElementById(tabBtnId).classList.add('active');
            
            // Actualizar título
            const header = document.querySelector('.admin-header h1');
            if (tabName === 'empleados') {
                header.innerHTML = '<i class="fas fa-user-cog"></i> Usuarios del Sistema - Empleados';
                document.querySelector('.admin-header p').textContent = 'Empleados con acceso al sistema (administradores, cajeros, mozos, almaceneros)';
            } else {
                header.innerHTML = '<i class="fas fa-user-cog"></i> Usuarios del Sistema - Clientes Web';
                document.querySelector('.admin-header p').textContent = 'Clientes que se registraron desde la página web para hacer pedidos online';
            }
        }

        function setupEventListeners() {
            // Filtros para empleados
            document.getElementById('searchEmpleados').addEventListener('input', () => filterEmpleados());
            document.getElementById('filterRoleEmpleados').addEventListener('change', () => filterEmpleados());
            
            // Filtros para clientes
            document.getElementById('searchClientes').addEventListener('input', () => filterClientes());
            document.getElementById('filterEstadoClientes').addEventListener('change', () => filterClientes());
            
            // Formulario de usuario
            document.getElementById('userForm').addEventListener('submit', handleUserSubmit);
        }
        
        function filterEmpleados() {
            const searchTerm = document.getElementById('searchEmpleados').value.toLowerCase();
            const roleFilter = document.getElementById('filterRoleEmpleados').value;
            const rows = document.querySelectorAll('#empleadosTable tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const roleId = row.getAttribute('data-role-id');
                
                const matchesSearch = !searchTerm || text.includes(searchTerm);
                const matchesRole = !roleFilter || roleId === roleFilter;
                
                if (matchesSearch && matchesRole) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Actualizar contador
            document.getElementById('empleados-counter').textContent = `${visibleCount} de ${rows.length} empleados`;
            console.log(`Empleados filtrados: ${visibleCount}/${rows.length}`);
        }
        
        function filterClientes() {
            const searchTerm = document.getElementById('searchClientes').value.toLowerCase();
            const estadoFilter = document.getElementById('filterEstadoClientes').value;
            const rows = document.querySelectorAll('#clientesTable tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const estadoBadge = row.querySelector('.badge');
                const estado = estadoBadge ? estadoBadge.textContent.trim() : '';
                
                const matchesSearch = !searchTerm || text.includes(searchTerm);
                const matchesEstado = !estadoFilter || estado === estadoFilter;
                
                if (matchesSearch && matchesEstado) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Actualizar contador
            document.getElementById('clientes-counter').textContent = `${visibleCount} de ${rows.length} clientes`;
            console.log(`Clientes filtrados: ${visibleCount}/${rows.length}`);
        }
        
        function limpiarFiltrosEmpleados() {
            document.getElementById('searchEmpleados').value = '';
            document.getElementById('filterRoleEmpleados').value = '';
            filterEmpleados();
            console.log('Filtros de empleados limpiados');
        }
        
        function limpiarFiltrosClientes() {
            document.getElementById('searchClientes').value = '';
            document.getElementById('filterEstadoClientes').value = '';
            filterClientes();
            console.log('Filtros de clientes limpiados');
        }

        function setupRoleChange() {
            document.getElementById('userRole').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const scope = selectedOption.getAttribute('data-scope');
                const clientFields = document.getElementById('clientFields');
                
                if (scope === 'web') {
                    clientFields.style.display = 'block';
                    // Hacer campos requeridos para clientes
                    document.getElementById('clientName').required = true;
                    document.getElementById('clientPhone').required = true;
                } else {
                    clientFields.style.display = 'none';
                    // Quitar requerimiento para empleados
                    document.getElementById('clientName').required = false;
                    document.getElementById('clientPhone').required = false;
                }
            });
        }



        function openModal(action, userType = 'empleado', userId = null) {
            const modal = document.getElementById('userModal');
            const title = document.getElementById('modalTitle');
            const actionInput = document.getElementById('actionInput');
            const passwordField = document.getElementById('userPassword');
            const confirmField = document.getElementById('confirmPassword');
            const clientFields = document.getElementById('clientFields');
            const roleSelect = document.getElementById('userRole');

            document.getElementById('userForm').reset();
            isEditing = action === 'editUser';
            actionInput.value = isEditing ? 'update' : 'create';
            document.getElementById('userId').value = '';

            // Configurar opciones de rol según el tipo de usuario
            const roleOptions = roleSelect.querySelectorAll('option');
            roleOptions.forEach(option => {
                if (option.value === '') return; // Mantener opción vacía
                
                const scope = option.getAttribute('data-scope');
                if (userType === 'empleado') {
                    option.style.display = scope === 'backoffice' ? 'block' : 'none';
                } else {
                    option.style.display = scope === 'web' ? 'block' : 'none';
                }
            });

            if (isEditing && userId) {
                title.innerHTML = `<i class="fas fa-user-edit" style="color: var(--admin-primary);"></i> Editar ${userType === 'empleado' ? 'Empleado' : 'Cliente'}`;
                loadUserData(userId);
                passwordField.required = false;
                confirmField.required = false;
            } else {
                title.innerHTML = `<i class="fas fa-user-plus" style="color: var(--admin-primary);"></i> Nuevo ${userType === 'empleado' ? 'Empleado' : 'Cliente Web'}`;
                passwordField.required = true;
                confirmField.required = true;
                
                // Mostrar campos de cliente si es cliente web
                if (userType === 'cliente' && clientFields) {
                    clientFields.style.display = 'block';
                    document.getElementById('clientName').required = true;
                    document.getElementById('clientPhone').required = true;
                } else if (clientFields) {
                    clientFields.style.display = 'none';
                    document.getElementById('clientName').required = false;
                    document.getElementById('clientPhone').required = false;
                }
            }

            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function loadUserData(userId) {
            const user = currentUsers.find(u => u.UsuarioID == userId);
            if (!user) return;

            document.getElementById('userId').value = user.UsuarioID;
            document.getElementById('userLogin').value = user.Login;
            document.getElementById('userRole').value = user.TipUsuID;
            document.getElementById('userPassword').value = '';
            document.getElementById('confirmPassword').value = '';

            document.getElementById('userRole').dispatchEvent(new Event('change'));

            const clientFields = document.getElementById('clientFields');
            if (user.Scope === 'web' && clientFields) {
                clientFields.style.display = 'block';
                document.getElementById('clientName').value = user.NombreCompleto !== 'Sin nombre' ? user.NombreCompleto : '';
                document.getElementById('clientPhone').value = '';
                document.getElementById('clientAddress').value = '';
            } else if (clientFields) {
                clientFields.style.display = 'none';
            }
        }

        async function handleUserSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirmPassword');
            
            // Validar contraseñas si se proporcionan
            if (password && password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                return;
            }
            
            if (!isEditing && password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    closeModal();
                    refreshTable();
                } else {
                    alert(result.message || 'Error al procesar la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            }
        }

        function editUser(userId, userType = 'empleado') {
            openModal('editUser', userType, userId);
        }

        async function toggleUserStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'Activo' ? 'Inactivo' : 'Activo';
            const action = newStatus === 'Activo' ? 'activar' : 'desactivar';
            
            if (!confirm(`¿Estás seguro de ${action} este usuario?`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'toggleStatus');
                formData.append('userId', userId);
                formData.append('status', newStatus);
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    refreshTable();
                } else {
                    alert(result.message || 'Error al cambiar el estado');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            }
        }

        function refreshTable() {
            window.location.reload();
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>