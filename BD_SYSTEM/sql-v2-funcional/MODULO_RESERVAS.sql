-- ================================================================
--  MÓDULO DE RESERVAS - SISTEMA COMPLETO
--  Versión : 1.0
--  Ejecutar sobre db_restaurante ya creada
-- ================================================================

USE db_restaurante;

-- ── Configuración global de reservas ─────────────────────────
-- El dueño ajusta estas reglas desde admin sin tocar código.
CREATE TABLE IF NOT EXISTS Reserva_Config (
  ConfigID              INT           NOT NULL AUTO_INCREMENT,
  -- Señal por persona (S/)
  Senal_Por_Persona     DECIMAL(8,2)  NOT NULL DEFAULT 11.00,
  -- Porcentaje de señal sobre platos pre-ordenados (30% = 0.30)
  Porcentaje_Senal_Platos DECIMAL(5,2) NOT NULL DEFAULT 0.30,
  -- Anticipación mínima para reservar (en horas)
  Anticipacion_Min_Hrs  TINYINT       NOT NULL DEFAULT 2,
  -- Con cuántos días de anticipación máximo se puede reservar
  Anticipacion_Max_Dias TINYINT       NOT NULL DEFAULT 30,
  -- Tiempo que se guarda la mesa sin que el cliente llegue (minutos)
  Tolerancia_Min        TINYINT       NOT NULL DEFAULT 15,
  -- Duración estimada de una reserva (minutos) — para calcular solapamiento
  Duracion_Estimada_Min SMALLINT      NOT NULL DEFAULT 90,
  -- Máximo de comensales por reserva individual
  Max_Comensales        TINYINT       NOT NULL DEFAULT 20,
  -- ¿Se requiere confirmación manual del admin? 0 = auto-confirma
  Requiere_Confirmacion TINYINT       NOT NULL DEFAULT 1,
  -- Mensaje de confirmación que recibe el cliente por correo
  Mensaje_Confirmacion  VARCHAR(500)  NULL,
  -- Mensaje de cancelación
  Mensaje_Cancelacion   VARCHAR(500)  NULL,
  Estado                TINYINT       NOT NULL DEFAULT 1,
  Fecha_Modificacion    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ConfigID)
) ENGINE=InnoDB
COMMENT='Reglas globales del sistema de reservas. Una fila activa (Estado=1).
         PHP lee esto una vez por sesión igual que Config_Fiscal.';

-- ── Tabla principal de reservas ───────────────────────────────
CREATE TABLE IF NOT EXISTS Reserva (
  ReservaID          INT           NOT NULL AUTO_INCREMENT,
  -- Token único para que el cliente cancele sin iniciar sesión
  -- PHP genera: bin2hex(random_bytes(16)) — 32 chars hex
  Token_Cancelacion  CHAR(32)      NOT NULL UNIQUE,
  -- ClienteID si está registrado; NULL si es anónimo
  ClienteID          INT           NULL,
  -- Datos del cliente anónimo (redundantes si hay ClienteID,
  -- pero evitan JOIN costoso en confirmaciones por correo)
  Nombre_Contacto    VARCHAR(150)  NOT NULL,
  Telefono_Contacto  VARCHAR(15)   NOT NULL,
  Correo_Contacto    VARCHAR(100)  NULL,
  -- Fecha y hora deseada por el cliente
  Fecha_Reserva      DATE          NOT NULL,
  Hora_Reserva       TIME          NOT NULL,
  Num_Comensales     TINYINT       NOT NULL,
  -- MesaID asignada por el admin al confirmar (NULL hasta asignación)
  MesaID             INT           NULL,
  -- 'Pendiente' → 'Confirmada' → 'Completada'
  --              ↘ 'Cancelada_Cliente' | 'Cancelada_Admin' | 'No_Show'
  Estado             VARCHAR(25)   NOT NULL DEFAULT 'Pendiente',
  -- Observaciones del cliente al reservar ("silla de bebé", "cumpleaños")
  Observaciones      VARCHAR(300)  NULL,
  -- Nota interna del admin (no visible al cliente)
  Nota_Admin         VARCHAR(300)  NULL,
  -- Monto de señal calculado (personas * senal_por_persona + % platos)
  Monto_Senal        DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
  -- PedidoID vinculado al confirmar llegada del cliente en POS
  PedidoID           INT           NULL,
  -- EmpleadoID que gestionó la reserva en backoffice
  EmpleadoID         INT           NULL,
  Fecha_Creacion     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Fecha_Modificacion DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ReservaID),
  INDEX idx_reserva_fecha    (Fecha_Reserva, Hora_Reserva),
  INDEX idx_reserva_estado   (Estado),
  INDEX idx_reserva_cliente  (ClienteID),
  CONSTRAINT fk_res_cliente  FOREIGN KEY (ClienteID)  REFERENCES Cliente  (ClienteID),
  CONSTRAINT fk_res_mesa     FOREIGN KEY (MesaID)     REFERENCES Mesa     (MesaID),
  CONSTRAINT fk_res_pedido   FOREIGN KEY (PedidoID)   REFERENCES Pedido   (PedidoID),
  CONSTRAINT fk_res_empleado FOREIGN KEY (EmpleadoID) REFERENCES Empleado (EmpleadoID)
) ENGINE=InnoDB
COMMENT='Tabla central del módulo de reservas.
         Token_Cancelacion: PHP genera bin2hex(random_bytes(16)).
         Nombre/Telefono/Correo_Contacto se guardan siempre para
         no perder datos si el cliente elimina su cuenta.
         PedidoID se vincula desde POS al confirmar llegada.
         Monto_Senal calculado en tiempo real en frontend.';

-- ── Detalle de platos pre-ordenados ───────────────────────────
CREATE TABLE IF NOT EXISTS Detalle_Reserva_Plato (
  DetalleID       INT           NOT NULL AUTO_INCREMENT,
  ReservaID       INT           NOT NULL,
  PlatoID         INT           NOT NULL,
  Cantidad        TINYINT       NOT NULL DEFAULT 1,
  -- Precio al momento de la reserva (histórico)
  Precio_Unitario DECIMAL(8,2)  NOT NULL,
  Observaciones   VARCHAR(200)  NULL COMMENT 'Ej: Sin cebolla, extra picante',
  PRIMARY KEY (DetalleID),
  CONSTRAINT fk_drp_reserva FOREIGN KEY (ReservaID) REFERENCES Reserva (ReservaID) ON DELETE CASCADE,
  CONSTRAINT fk_drp_plato   FOREIGN KEY (PlatoID)   REFERENCES Plato   (PlatoID)
) ENGINE=InnoDB
COMMENT='Platos pre-ordenados al hacer la reserva.
         Se guardan precios históricos para calcular señal correctamente.';

-- ── Historial de cambios de estado ────────────────────────────
-- Append-only: nunca se elimina. Auditoría completa del ciclo de vida.
CREATE TABLE IF NOT EXISTS Reserva_Historial (
  HistorialID    INT           NOT NULL AUTO_INCREMENT,
  ReservaID      INT           NOT NULL,
  Estado_Antes   VARCHAR(25)   NULL,
  Estado_Nuevo   VARCHAR(25)   NOT NULL,
  -- NULL si el cambio lo hizo el cliente (cancelación por token)
  EmpleadoID     INT           NULL,
  -- 'cliente_web' | 'admin' | 'sistema' (recordatorio automático)
  Origen         VARCHAR(20)   NOT NULL DEFAULT 'admin',
  Observacion    VARCHAR(300)  NULL,
  Fecha_Cambio   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (HistorialID),
  INDEX idx_rh_reserva (ReservaID),
  CONSTRAINT fk_rh_reserva  FOREIGN KEY (ReservaID)  REFERENCES Reserva  (ReservaID) ON DELETE CASCADE,
  CONSTRAINT fk_rh_empleado FOREIGN KEY (EmpleadoID) REFERENCES Empleado (EmpleadoID)
) ENGINE=InnoDB
COMMENT='Auditoría de cada cambio de estado de una reserva.
         Origen=cliente_web cuando cancela por token sin login.
         Origen=sistema para cancelaciones automáticas por no-show.';

-- ── Seed: configuración inicial ───────────────────────────────
INSERT IGNORE INTO Reserva_Config
  (ConfigID, Senal_Por_Persona, Porcentaje_Senal_Platos,
   Anticipacion_Min_Hrs, Anticipacion_Max_Dias, Tolerancia_Min,
   Duracion_Estimada_Min, Max_Comensales, Requiere_Confirmacion,
   Mensaje_Confirmacion, Mensaje_Cancelacion)
VALUES
  (1, 11.00, 0.30, 2, 30, 15, 90, 20, 1,
   'Tu reserva en Chifa Matsue está confirmada. Te esperamos el {fecha} a las {hora}. ¡Que disfrutes!',
   'Tu reserva ha sido cancelada. Si tienes dudas llámanos al 072-123456.');

-- ── Vista útil: Reservas con detalles completos ───────────────
CREATE OR REPLACE VIEW v_reservas_completas AS
SELECT 
  r.ReservaID,
  r.Token_Cancelacion,
  r.Fecha_Reserva,
  r.Hora_Reserva,
  r.Num_Comensales,
  r.Estado,
  r.Monto_Senal,
  r.Observaciones,
  r.Nombre_Contacto,
  r.Telefono_Contacto,
  r.Correo_Contacto,
  COALESCE(c.Nombre_Apellidos, r.Nombre_Contacto) AS Cliente_Nombre,
  COALESCE(c.Correo, r.Correo_Contacto) AS Cliente_Correo,
  COALESCE(c.Telefono, r.Telefono_Contacto) AS Cliente_Telefono,
  m.Codigo_Mesa,
  m.Numero_Mesa,
  GROUP_CONCAT(
    CONCAT(p.Nombre, ' (x', drp.Cantidad, ')') 
    SEPARATOR ', '
  ) AS Platos_Pre_Ordenados,
  SUM(drp.Precio_Unitario * drp.Cantidad) AS Total_Platos,
  r.Fecha_Creacion
FROM Reserva r
LEFT JOIN Cliente c ON r.ClienteID = c.ClienteID
LEFT JOIN Mesa m ON r.MesaID = m.MesaID
LEFT JOIN Detalle_Reserva_Plato drp ON r.ReservaID = drp.ReservaID
LEFT JOIN Plato p ON drp.PlatoID = p.PlatoID
GROUP BY r.ReservaID;

-- ══════════════════════════════════════════════════════════════
-- VERIFICACIÓN
-- ══════════════════════════════════════════════════════════════

SELECT '✅ MÓDULO DE RESERVAS INSTALADO' AS Status;
SELECT 'Verificando tablas...' AS Info;

-- Verificar tablas creadas
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'db_restaurante' 
  AND TABLE_NAME IN ('Reserva_Config', 'Reserva', 'Detalle_Reserva_Plato', 'Reserva_Historial')
ORDER BY TABLE_NAME;

-- Verificar configuración
SELECT 
  ConfigID,
  Senal_Por_Persona,
  Porcentaje_Senal_Platos * 100 AS Porcentaje_Platos_Pct,
  Anticipacion_Min_Hrs,
  Max_Comensales,
  Estado
FROM Reserva_Config
WHERE Estado = 1;

SELECT '🎉 Sistema de reservas listo' AS Mensaje;
