<?php
/**
 * Clase Services - Servicios comunes del sistema
 */
class Services {
    
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtiene información del usuario por ID
     */
    public function getUserById(int $userId): ?array {
        $sql = "SELECT u.UsuarioID, u.Login, u.Estado, u.TipUsuID,
                       t.Descripcion AS RolDescripcion, t.Scope AS RolScope,
                       c.Nombre_Apellidos, c.Telefono, c.Direccion
                FROM Usuario u
                JOIN Tipo_Usuario t ON u.TipUsuID = t.TipUsuID
                LEFT JOIN Cliente c ON u.UsuarioID = c.UsuarioID
                WHERE u.UsuarioID = :userId AND u.Estado = 'Activo'
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [':userId' => $userId]);
    }
    
    /**
     * Actualiza perfil de cliente
     */
    public function updateClientProfile(int $userId, array $data): bool {
        try {
            $this->db->beginTransaction();
            
            // Actualizar tabla Cliente
            $sqlCliente = "UPDATE Cliente SET 
                          Nombre_Apellidos = :nombre,
                          Telefono = :telefono,
                          Direccion = :direccion
                          WHERE UsuarioID = :userId";
            
            $this->db->execute($sqlCliente, [
                ':nombre' => $data['nombre'],
                ':telefono' => $data['telefono'],
                ':direccion' => $data['direccion'] ?? null,
                ':userId' => $userId
            ]);
            
            // Si hay cambio de email, actualizar Usuario
            if (!empty($data['email'])) {
                $sqlUsuario = "UPDATE Usuario SET Login = :email WHERE UsuarioID = :userId";
                $this->db->execute($sqlUsuario, [
                    ':email' => $data['email'],
                    ':userId' => $userId
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Error updating profile: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambia contraseña del usuario
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array {
        // Verificar contraseña actual
        $user = $this->db->fetchOne(
            "SELECT Contrasena FROM Usuario WHERE UsuarioID = :userId",
            [':userId' => $userId]
        );
        
        if (!$user || !password_verify($currentPassword, $user['Contrasena'])) {
            return ['success' => false, 'message' => 'Contraseña actual incorrecta'];
        }
        
        // Actualizar contraseña
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->db->execute(
                "UPDATE Usuario SET Contrasena = :password WHERE UsuarioID = :userId",
                [':password' => $hashedPassword, ':userId' => $userId]
            );
            
            return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
            
        } catch (Exception $e) {
            error_log('Error changing password: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar contraseña'];
        }
    }
    
    /**
     * Obtiene categorías de platos
     */
    public function getCategories(): array {
        $sql = "SELECT CatID, Nombre, Descripcion 
                FROM Categoria 
                WHERE Tipo = 'Plato' AND Estado = 'Activo'
                ORDER BY Orden ASC, Nombre ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Obtiene platos por categoría
     */
    public function getPlatosByCategory(string $categoria): array {
        $sql = "SELECT pl.PlatoID AS id, pl.Nombre AS nombre, 
                       pl.Descripcion AS descripcion, pl.Imagen_URL AS imagen,
                       pl.Es_Top AS top, pl.Es_Promo AS promo,
                       cat.Nombre AS categoria,
                       MIN(pv.Precio_Venta) AS precio_min,
                       MAX(pv.Precio_Venta) AS precio_max
                FROM Plato pl
                JOIN Categoria cat ON pl.CatID = cat.CatID
                JOIN Plato_Variante pv ON pl.PlatoID = pv.PlatoID
                WHERE pl.Estado = 'Disponible' 
                  AND pv.Estado = 1 
                  AND cat.Nombre = :categoria
                GROUP BY pl.PlatoID
                ORDER BY pl.Orden ASC, pl.Nombre ASC";
        
        return $this->db->fetchAll($sql, [':categoria' => $categoria]);
    }
    
    /**
     * Obtiene detalles de un plato con sus variantes
     */
    public function getPlatoDetails(int $platoId): ?array {
        $sql = "SELECT pl.PlatoID AS id, pl.Nombre AS nombre,
                       pl.Descripcion AS descripcion, pl.Imagen_URL AS imagen,
                       pl.Es_Top AS top, pl.Es_Promo AS promo,
                       cat.Nombre AS categoria
                FROM Plato pl
                JOIN Categoria cat ON pl.CatID = cat.CatID
                WHERE pl.PlatoID = :platoId AND pl.Estado = 'Disponible'
                LIMIT 1";
        
        $plato = $this->db->fetchOne($sql, [':platoId' => $platoId]);
        
        if (!$plato) return null;
        
        // Obtener variantes
        $sqlVariantes = "SELECT PlatoVarianteID AS id, Nombre AS nombre,
                                Descripcion AS descripcion, Precio_Venta AS precio
                         FROM Plato_Variante
                         WHERE PlatoID = :platoId AND Estado = 1
                         ORDER BY Precio_Venta ASC";
        
        $plato['variantes'] = $this->db->fetchAll($sqlVariantes, [':platoId' => $platoId]);
        
        return $plato;
    }
    
    /**
     * Registra un pedido
     */
    public function createPedido(int $clienteId, array $items, array $entrega): array {
        try {
            $this->db->beginTransaction();
            
            // Calcular total
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['precio'] * $item['cantidad'];
            }
            $delivery = 8.00;
            $total = $subtotal + $delivery;
            
            // Insertar pedido
            $sqlPedido = "INSERT INTO Pedido (ClienteID, Fecha_Pedido, Estado, Subtotal, Delivery, Total, 
                                            Direccion_Entrega, Telefono_Entrega, Observaciones)
                         VALUES (:clienteId, NOW(), 'Pendiente', :subtotal, :delivery, :total,
                                :direccion, :telefono, :observaciones)";
            
            $this->db->execute($sqlPedido, [
                ':clienteId' => $clienteId,
                ':subtotal' => $subtotal,
                ':delivery' => $delivery,
                ':total' => $total,
                ':direccion' => $entrega['direccion'],
                ':telefono' => $entrega['telefono'],
                ':observaciones' => $entrega['observaciones'] ?? null
            ]);
            
            $pedidoId = $this->db->lastInsertId();
            
            // Insertar detalles del pedido
            foreach ($items as $item) {
                $sqlDetalle = "INSERT INTO Detalle_Pedido (PedidoID, PlatoVarianteID, Cantidad, Precio_Unitario)
                              VALUES (:pedidoId, :varianteId, :cantidad, :precio)";
                
                $this->db->execute($sqlDetalle, [
                    ':pedidoId' => $pedidoId,
                    ':varianteId' => $item['variante_id'],
                    ':cantidad' => $item['cantidad'],
                    ':precio' => $item['precio']
                ]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'pedido_id' => $pedidoId,
                'total' => $total,
                'message' => 'Pedido registrado exitosamente'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Error creating pedido: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al procesar el pedido'
            ];
        }
    }
    
    /**
     * Obtiene pedidos de un cliente
     */
    public function getClientePedidos(int $clienteId, int $limit = 10): array {
        $sql = "SELECT p.PedidoID AS id, p.Fecha_Pedido AS fecha,
                       p.Estado AS estado, p.Total AS total,
                       COUNT(dp.DetallePedidoID) AS items
                FROM Pedido p
                LEFT JOIN Detalle_Pedido dp ON p.PedidoID = dp.PedidoID
                WHERE p.ClienteID = :clienteId
                GROUP BY p.PedidoID
                ORDER BY p.Fecha_Pedido DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            ':clienteId' => $clienteId,
            ':limit' => $limit
        ]);
    }
    
    /**
     * Limpia sesiones expiradas (llamar periódicamente)
     */
    public function cleanExpiredSessions(): void {
        // Esta función se puede implementar si se usa almacenamiento de sesiones en BD
        // Por ahora, PHP maneja las sesiones automáticamente
    }
}