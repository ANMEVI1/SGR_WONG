<?php
/**
 * API de Pedidos - Gestión completa de pedidos web
 */
require_once __DIR__ . '/../config/conexion.php';

startSecureSession();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGetPedidos();
        break;
    case 'POST':
        handleCreatePedido();
        break;
    case 'PUT':
        handleUpdatePedido();
        break;
    default:
        Response::error('Método no permitido', 405);
}

/**
 * Obtener pedidos del cliente actual
 */
function handleGetPedidos() {
    requireAuth();
    
    $currentUser = getCurrentUser();
    if (!$currentUser) {
        Response::unauthorized('Sesión expirada');
    }
    
    try {
        $db = getDB();
        
        // Obtener ClienteID del usuario
        $cliente = $db->fetchOne(
            "SELECT ClienteID FROM Cliente WHERE UsuarioID = :userId",
            [':userId' => $currentUser['id']]
        );
        
        if (!$cliente) {
            Response::error('Cliente no encontrado', 404);
        }
        
        $services = getServices();
        $pedidos = $services->getClientePedidos($cliente['ClienteID']);
        
        Response::success($pedidos, 'Pedidos obtenidos exitosamente');
        
    } catch (Exception $e) {
        error_log('Error getting pedidos: ' . $e->getMessage());
        Response::error('Error al obtener pedidos');
    }
}

/**
 * Crear nuevo pedido web
 */
function handleCreatePedido() {
    requireAuth();
    
    $currentUser = getCurrentUser();
    if (!$currentUser) {
        Response::unauthorized('Sesión expirada');
    }
    
    // Obtener datos del pedido
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        Response::error('Datos de pedido inválidos', 400);
    }
    
    // Validar datos requeridos
    $validator = new Validator();
    $validator
        ->required('items', $input['items'] ?? [], 'Los items del pedido son requeridos')
        ->required('entrega', $input['entrega'] ?? [], 'Los datos de entrega son requeridos');
    
    if (empty($input['items']) || !is_array($input['items'])) {
        $validator->errors['items'] = 'Debe incluir al menos un item';
    }
    
    // Validar datos de entrega
    $entrega = $input['entrega'] ?? [];
    $validator
        ->required('direccion', $entrega['direccion'] ?? '', 'La dirección de entrega es requerida')
        ->required('telefono', $entrega['telefono'] ?? '', 'El teléfono de contacto es requerido')
        ->phone('telefono', $entrega['telefono'] ?? '');
    
    if ($validator->hasErrors()) {
        Response::validation($validator->getErrors());
    }
    
    try {
        $db = getDB();
        
        // Obtener ClienteID del usuario
        $cliente = $db->fetchOne(
            "SELECT ClienteID FROM Cliente WHERE UsuarioID = :userId",
            [':userId' => $currentUser['id']]
        );
        
        if (!$cliente) {
            Response::error('Cliente no encontrado', 404);
        }
        
        // Validar items del pedido
        $itemsValidados = [];
        foreach ($input['items'] as $item) {
            // Validar que la variante existe y está disponible
            $variante = $db->fetchOne(
                "SELECT pv.VarianteID, pv.Precio_Venta, pl.Nombre as plato_nombre, pv.Nombre as variante_nombre
                 FROM Plato_Variante pv
                 JOIN Plato pl ON pv.PlatoID = pl.PlatoID
                 WHERE pv.VarianteID = :varianteId AND pv.Estado = 1 AND pl.Estado = 'Disponible'",
                [':varianteId' => $item['variante_id'] ?? 0]
            );
            
            if (!$variante) {
                Response::error('Producto no disponible: ' . ($item['nombre'] ?? 'Desconocido'), 400);
            }
            
            $itemsValidados[] = [
                'variante_id' => $variante['VarianteID'],
                'cantidad' => max(1, (int)($item['cantidad'] ?? 1)),
                'precio' => $variante['Precio_Venta'],
                'nombre' => $variante['plato_nombre'] . ' - ' . $variante['variante_nombre']
            ];
        }
        
        // Obtener empleado por defecto para pedidos web (cajero o admin)
        $empleadoWeb = $db->fetchOne(
            "SELECT e.EmpleadoID FROM Empleado e 
             JOIN Usuario u ON e.UsuarioID = u.UsuarioID
             JOIN Tipo_Usuario tu ON u.TipUsuID = tu.TipUsuID
             WHERE tu.Descripcion IN ('Cajero / Vendedor', 'Administrador del sistema')
             AND e.Estado = 'Activo'
             LIMIT 1"
        );
        
        if (!$empleadoWeb) {
            Response::error('No hay empleados disponibles para procesar el pedido', 500);
        }
        
        // Crear pedido
        $db->beginTransaction();
        
        try {
            // Insertar pedido
            $db->execute(
                "INSERT INTO Pedido (EmpleadoID, ClienteID, Origen, Estado, Estado_Pago, Tipo_Pedido, Observaciones)
                 VALUES (:empleadoId, :clienteId, 'Web', 'Pendiente', 'Pendiente', 'Delivery', :observaciones)",
                [
                    ':empleadoId' => $empleadoWeb['EmpleadoID'],
                    ':clienteId' => $cliente['ClienteID'],
                    ':observaciones' => $entrega['observaciones'] ?? null
                ]
            );
            
            $pedidoId = $db->lastInsertId();
            
            // Insertar detalles del pedido
            $subtotal = 0;
            foreach ($itemsValidados as $item) {
                $itemSubtotal = $item['precio'] * $item['cantidad'];
                $subtotal += $itemSubtotal;
                
                $db->execute(
                    "INSERT INTO Detalle_Pedido (PedidoID, VarianteID, Cantidad, Precio_Unitario, Subtotal)
                     VALUES (:pedidoId, :varianteId, :cantidad, :precio, :subtotal)",
                    [
                        ':pedidoId' => $pedidoId,
                        ':varianteId' => $item['variante_id'],
                        ':cantidad' => $item['cantidad'],
                        ':precio' => $item['precio'],
                        ':subtotal' => $itemSubtotal
                    ]
                );
            }
            
            // Crear registro de delivery
            $costoDelivery = 8.00; // Por defecto, se puede calcular por zona
            $db->execute(
                "INSERT INTO Delivery (PedidoID, Direccion_Entrega, Referencia_Direccion, Estado, Costo_Delivery)
                 VALUES (:pedidoId, :direccion, :referencia, 'Pendiente', :costo)",
                [
                    ':pedidoId' => $pedidoId,
                    ':direccion' => $entrega['direccion'],
                    ':referencia' => $entrega['referencia'] ?? null,
                    ':costo' => $costoDelivery
                ]
            );
            
            $db->commit();
            
            $total = $subtotal + $costoDelivery;
            
            Response::success([
                'pedido_id' => (int) $pedidoId,
                'subtotal' => $subtotal,
                'costo_delivery' => $costoDelivery,
                'total' => $total,
                'estado' => 'Pendiente'
            ], 'Pedido creado exitosamente');
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Error creating pedido: ' . $e->getMessage());
        Response::error('Error al crear el pedido');
    }
}

/**
 * Actualizar estado de pedido (solo para admin/empleados)
 */
function handleUpdatePedido() {
    requireAuth();
    
    $currentUser = getCurrentUser();
    if (!hasPermission('backoffice')) {
        Response::error('No tienes permisos para esta acción', 403);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $pedidoId = filter_var($input['pedido_id'] ?? 0, FILTER_VALIDATE_INT);
    $nuevoEstado = Validator::sanitize($input['estado'] ?? '');
    
    if (!$pedidoId || !$nuevoEstado) {
        Response::error('Datos inválidos', 400);
    }
    
    $estadosValidos = ['Pendiente', 'En cocina', 'Listo', 'En delivery', 'Entregado', 'Cancelado'];
    if (!in_array($nuevoEstado, $estadosValidos)) {
        Response::error('Estado inválido', 400);
    }
    
    try {
        $db = getDB();
        
        $updated = $db->execute(
            "UPDATE Pedido SET Estado = :estado WHERE PedidoID = :pedidoId",
            [':estado' => $nuevoEstado, ':pedidoId' => $pedidoId]
        );
        
        if ($updated > 0) {
            Response::success(['pedido_id' => $pedidoId, 'nuevo_estado' => $nuevoEstado], 'Estado actualizado');
        } else {
            Response::notFound('Pedido no encontrado');
        }
        
    } catch (Exception $e) {
        error_log('Error updating pedido: ' . $e->getMessage());
        Response::error('Error al actualizar el pedido');
    }
}
?>