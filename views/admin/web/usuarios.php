<?php
require_once '../../../config/conexion.php';

startSecureSession();
requireAuth();
requirePermission('backoffice');

$currentUser = getCurrentUser();
$db = getDB();

// Verificar permisos de administrador
$userRole = $db->fetchOne(
    "SELECT tu.TipUsuID FROM Usuario u
     JOIN Tipo_Usuario tu ON u.TipUsuID = tu.TipUsuID
     WHERE u.UsuarioID = :userId",
    [':userId' => $currentUser['UsuarioID']]
);

if ($userRole['TipUsuID'] != 1) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Obtener estadísticas de usuarios web
$stats = $db->fetchOne(
    "SELECT 
        COUNT(DISTINCT u.UsuarioID) as total_usuarios,
        COUNT(DISTINCT c.ClienteID) as total_clientes,
        SUM(CASE WHEN u.Estado = 'Activo' THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN u.Estado = 'Inactivo' THEN 1 ELSE 0 END) as inactivos
     FROM Usuario u
     LEFT JOIN Cliente c ON u.UsuarioID = c.UsuarioID
     WHERE u.TipUsuID = 5"
);

// Obtener usuarios web con información de cliente
$usuariosWeb = $db->fetchAll(
    "SELECT u.UsuarioID, u.Login, u.Estado, u.Token_Reset,
            c.ClienteID, c.Nombre_Apellidos, c.Telefono, c.Direccion, c.Fecha_Creacion,
            COUNT(p.PedidoID) as total_pedidos,
            COALESCE(SUM(cp.Total), 0) as total_gastado
     FROM Usuario u
     LEFT JOIN Cliente c ON u.UsuarioID = c.UsuarioID
     LEFT JOIN Pedido p ON c.ClienteID = p.ClienteID
     LEFT JOIN Comprobante_Pago cp ON p.PedidoID = cp.PedidoID
     WHERE u.TipUsuID = 5
     GROUP BY u.UsuarioID
     ORDER BY c.Fecha_Creacion DESC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios del Sistema — Chifa Matsue</title>
    <link rel="stylesheet" href="../../../css/estilos.css">
    <link rel="stylesheet" href="../../../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div style="padding: 25px; border-bottom: 2px solid var(--admin-border);">
                <h3 style="margin: 0; color: var(--admin-text); display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-utensils" style="color: var(--admin-primary); font-size: 1.5rem;"></i>
                    Chifa Matsue
                </h3>
                <small style="color: var(--admin-text-light); font-weight: 600; margin-top: 5px; display: block;">Administración Web</small>
            </div>
            <nav class="admin-nav" style="padding: 25px 0;">
                <a href="../dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <div class="nav-section">
                    <div class="nav-section-title">ADMINISTRACIÓN WEB</div>
                    <a href="usuarios.php" class="nav-item active">
                        <i class="fas fa-user-cog"></i> Usuarios Sistema
                    </a>
                    <a href="contenido.php" class="nav-item">
                        <i class="fas fa-globe"></i> Contenido Web
                    </a>
                    <a href="pedidos-online.php" class="nav-item">
                        <i class="fas fa-laptop"></i> Pedidos Online
                    </a>
                </div>
            </nav>
        </div>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-user-cog"></i> Usuarios del Sistema</h1>
                <p>Gestión de clientes registrados en la plataforma web</p>
            </div>

            <!-- Estadísticas -->
            <div class="dashboard-grid" style="margin-bottom: 30px;">
                <div class="metric-card success">
                    <div class="metric-label">Total Usuarios</div>
                    <div class="metric-value"><?= $stats['total_usuarios'] ?></div>
                    <small>Registrados</small>
                </div>
                
                <div class="metric-card info">
                    <div class="metric-label">Activos</div>
                    <div class="metric-value"><?= $stats['activos'] ?></div>
                    <small>Con acceso</small>
                </div>
                
                <div class="metric-card warning">
                    <div class="metric-label">Inactivos</div>
                    <div class="metric-value"><?= $stats['inactivos'] ?></div>
                    <small>Bloqueados</small>
                </div>
                
                <div class="metric-card">
                    <div class="metric-label">Con Pedidos</div>
                    <div class="metric-value"><?= count(array_filter($usuariosWeb, fn($u) => $u['total_pedidos'] > 0)) ?></div>
                    <small>Compradores</small>
                </div>
            </div>

            <!-- Filtros y Búsqueda -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <button onclick="exportarUsuarios()" class="btn btn-outline">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <button onclick="enviarNotificacion()" class="btn btn-outline">
                        <i class="fas fa-bell"></i> Notificación Masiva
                    </button>
                </div>
                <div style="display: flex; gap: 15px;">
                    <select id="filtroEstado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="Activo">Activos</option>
                        <option value="Inactivo">Inactivos</option>
                    </select>
                    <input type="text" id="buscarUsuario" placeholder="Buscar por nombre o email..." class="form-control" style="width: 300px;">
                    <button onclick="limpiarFiltrosUsuarios()" class="btn btn-outline" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div style="margin-bottom: 15px; color: var(--admin-text-light); font-size: 0.9rem;">
                <span id="usuarios-counter">Cargando...</span>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="admin-table">
                <table id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Registro</th>
                            <th>Pedidos</th>
                            <th>Total Gastado</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuariosWeb as $usuario): ?>
                        <tr data-estado="<?= $usuario['Estado'] ?>">
                            <td>
                                <strong><?= htmlspecialchars($usuario['Nombre_Apellidos'] ?: 'Sin nombre') ?></strong>
                                <br>
                                <small style="color: #6c757d;">ID: <?= $usuario['UsuarioID'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($usuario['Login']) ?></td>
                            <td><?= htmlspecialchars($usuario['Telefono'] ?: 'No registrado') ?></td>
                            <td>
                                <?= $usuario['Fecha_Creacion'] ? date('d/m/Y', strtotime($usuario['Fecha_Creacion'])) : 'N/A' ?>
                                <br>
                                <small style="color: #6c757d;"><?= $usuario['Fecha_Creacion'] ? date('H:i', strtotime($usuario['Fecha_Creacion'])) : '' ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $usuario['total_pedidos'] > 0 ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= $usuario['total_pedidos'] ?>
                                </span>
                            </td>
                            <td>S/ <?= number_format($usuario['total_gastado'], 2) ?></td>
                            <td>
                                <span class="badge <?= $usuario['Estado'] === 'Activo' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $usuario['Estado'] ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="verPerfil(<?= $usuario['UsuarioID'] ?>)" class="btn btn-sm" style="background: #17a2b8; color: white; margin-right: 5px;">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="toggleEstado(<?= $usuario['UsuarioID'] ?>, '<?= $usuario['Estado'] ?>')" 
                                        class="btn btn-sm" style="background: <?= $usuario['Estado'] === 'Activo' ? '#dc3545' : '#28a745' ?>; color: white;">
                                    <i class="fas <?= $usuario['Estado'] === 'Activo' ? 'fa-ban' : 'fa-check' ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Ver Perfil -->
    <div id="modalPerfil" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>Perfil de Usuario</h2>
            <div id="contenidoPerfil"></div>
        </div>
    </div>

    <script src="../../../js/table-filters.js"></script>
    <script>
    let usuarioFilter;
    
    // Inicializar filtros cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar sistema de filtros optimizado
        usuarioFilter = initUserFilters('tablaUsuarios');
        
        // Mostrar contador inicial
        const stats = usuarioFilter.getStats();
        document.getElementById('usuarios-counter').textContent = `${stats.total} usuarios registrados`;
        
        console.log('✓ Sistema de filtros de usuarios inicializado');
    });

    function limpiarFiltrosUsuarios() {
        if (usuarioFilter) {
            usuarioFilter.clearFilters();
        }
    }

    function verPerfil(usuarioId) {
        fetch(`../../../api/admin/usuario-detalle.php?id=${usuarioId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('contenidoPerfil').innerHTML = `
                        <div class="perfil-detalle">
                            <h3>${data.usuario.Nombre_Apellidos}</h3>
                            <p><strong>Email:</strong> ${data.usuario.Login}</p>
                            <p><strong>Teléfono:</strong> ${data.usuario.Telefono || 'No registrado'}</p>
                            <p><strong>Dirección:</strong> ${data.usuario.Direccion || 'No registrada'}</p>
                            <p><strong>Registro:</strong> ${data.usuario.Fecha_Creacion}</p>
                            <p><strong>Total Pedidos:</strong> ${data.usuario.total_pedidos}</p>
                            <p><strong>Total Gastado:</strong> S/ ${data.usuario.total_gastado}</p>
                        </div>
                    `;
                    document.getElementById('modalPerfil').style.display = 'block';
                } else {
                    alert('Error al cargar el perfil');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
    }

    function toggleEstado(usuarioId, estadoActual) {
        const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
        const accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
        
        if (confirm(`¿Está seguro de ${accion} este usuario?`)) {
            fetch('../../../api/admin/toggle-usuario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    usuarioId: usuarioId,
                    nuevoEstado: nuevoEstado
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al cambiar el estado: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }
    }

    function exportarUsuarios() {
        window.open('../../../api/admin/exportar-usuarios.php', '_blank');
    }

    function enviarNotificacion() {
        const mensaje = prompt('Ingrese el mensaje a enviar a todos los usuarios:');
        if (mensaje) {
            fetch('../../../api/admin/notificacion-masiva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ mensaje: mensaje })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.success ? 'Notificación enviada' : 'Error: ' + data.message);
            });
        }
    }

    function cerrarModal() {
        document.getElementById('modalPerfil').style.display = 'none';
    }

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('modalPerfil');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
    </script>

    <style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: none;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        position: relative;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        position: absolute;
        right: 15px;
        top: 10px;
    }

    .close:hover {
        color: black;
    }

    .perfil-detalle {
        padding: 20px 0;
    }

    .perfil-detalle h3 {
        margin-bottom: 15px;
        color: var(--admin-primary);
    }

    .perfil-detalle p {
        margin: 8px 0;
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }
    </style>
</body>
</html>="background: <?= $usuario['Estado'] === 'Activo' ? '#dc3545' : '#28a745' ?>; color: white; margin-right: 5px;">
                                    <i class="fas fa-<?= $usuario['Estado'] === 'Activo' ? 'ban' : 'check' ?>"></i>
                                </button>
                                <button onclick="resetPassword(<?= $usuario['UsuarioID'] ?>)" class="btn btn-sm" style="background: #ffc107; color: #212529;">
                                    <i class="fas fa-key"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Perfil Usuario -->
    <div id="modalPerfil" class="modal-overlay" style="display: none;">
        <div class="modal" style="max-width: 800px;">
            <div style="padding: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><i class="fas fa-user"></i> Perfil de Usuario</h3>
                    <button onclick="cerrarModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
                </div>
                <div id="contenidoPerfil">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filtros
        document.getElementById('filtroEstado').addEventListener('change', filtrarTabla);
        document.getElementById('buscarUsuario').addEventListener('input', filtrarTabla);

        function filtrarTabla() {
            const estado = document.getElementById('filtroEstado').value;
            const busqueda = document.getElementById('buscarUsuario').value.toLowerCase();
            const filas = document.querySelectorAll('#tablaUsuarios tbody tr');

            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                const estadoFila = fila.getAttribute('data-estado');
                
                const coincideBusqueda = textoFila.includes(busqueda);
                const coincideEstado = !estado || estadoFila === estado;
                
                fila.style.display = (coincideBusqueda && coincideEstado) ? '' : 'none';
            });
        }

        async function verPerfil(userId) {
            try {
                const response = await fetch(`api.php?action=get_user_profile&user_id=${userId}`);
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('contenidoPerfil').innerHTML = result.html;
                    document.getElementById('modalPerfil').style.display = 'flex';
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error al cargar perfil');
            }
        }

        async function toggleEstado(userId, estadoActual) {
            const nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
            const accion = nuevoEstado === 'Activo' ? 'activar' : 'desactivar';
            
            if (!confirm(`¿Estás seguro de ${accion} este usuario?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_user_status');
                formData.append('user_id', userId);
                formData.append('estado', nuevoEstado);

                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error de conexión');
            }
        }

        async function resetPassword(userId) {
            if (!confirm('¿Enviar email de recuperación de contraseña?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'reset_password');
                formData.append('user_id', userId);

                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                alert(result.message);
            } catch (error) {
                alert('Error de conexión');
            }
        }

        function cerrarModal() {
            document.getElementById('modalPerfil').style.display = 'none';
        }

        function exportarUsuarios() {
            window.open('api.php?action=export_users', '_blank');
        }

        function enviarNotificacion() {
            alert('Función de notificación masiva en desarrollo');
        }
    </script>
</body>
</html>