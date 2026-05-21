-- =============================================================
--  SISTEMA DE GESTIÓN DE RESTAURANTE (CHIFA) — v2.0
--  Motor    : MySQL 8.0+
--  Charset  : utf8mb4
--  Enfoque  : Omnicanal (POS local + E-commerce web)
--  Auth     : Usuario universal desacoplado (Enfoque B)
--  Módulos  : Punto de Venta | Personal | Inventario | Delivery
-- =============================================================
--
--  CAMBIOS v2 RESPECTO A v1
--  ─────────────────────────────────────────────────────────────
--  [1] Usuario universal: ningún módulo (Empleado, Cliente) tiene
--      credenciales propias. Ambos apuntan a Usuario.UsuarioID.
--      Esto permite login web de clientes SIN meterlos en Empleado.
--
--  [2] Plato.Imagen_URL: campo para carta visual en web/app.
--      Guarda ruta relativa, nunca BLOB en BD.
--
--  [3] Pedido.Origen + Pedido.Estado_Pago: diferencia si el pedido
--      viene del salón, la web o una app, y si ya fue pagado online.
--
--  [4] Kardex.EmpleadoID: trazabilidad de quién registró cada
--      movimiento (entradas, salidas, mermas y ajustes).
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

DROP DATABASE IF EXISTS db_restaurante;
CREATE DATABASE db_restaurante
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_restaurante;

-- =============================================================
--  BLOQUE 0 — AUTENTICACIÓN UNIVERSAL
--  Se crea primero porque Empleado y Cliente lo referencian
-- =============================================================

CREATE TABLE Tipo_Usuario (
  TipUsuID    INT           NOT NULL AUTO_INCREMENT,
  Descripcion VARCHAR(100)  NOT NULL,
  -- Scope indica a qué parte del sistema tiene acceso
  -- 'backoffice' = admin/cajero/mozo | 'web' = cliente final
  Scope       VARCHAR(20)   NOT NULL DEFAULT 'backoffice',
  PRIMARY KEY (TipUsuID)
) ENGINE=InnoDB COMMENT='Roles de acceso al sistema';

CREATE TABLE Usuario (
  UsuarioID   INT           NOT NULL AUTO_INCREMENT,
  Login       VARCHAR(60)   NOT NULL UNIQUE,
  -- Hash bcrypt/argon2 generado en PHP, nunca texto plano
  Contrasena  VARCHAR(255)  NOT NULL,
  Estado      VARCHAR(20)   NOT NULL DEFAULT 'Activo',
  -- Token para recuperación de contraseña / confirmación de correo
  Token_Reset VARCHAR(100)  NULL,
  Fecha_Token DATETIME      NULL,
  TipUsuID    INT           NOT NULL,
  PRIMARY KEY (UsuarioID),
  CONSTRAINT fk_usu_tipo FOREIGN KEY (TipUsuID) REFERENCES Tipo_Usuario (TipUsuID)
) ENGINE=InnoDB COMMENT='Tabla central de autenticación para empleados y clientes web';

-- =============================================================
--  BLOQUE 1 — CONTROL DE PERSONAL
--  Empleado referencia Usuario (no al revés)
-- =============================================================

CREATE TABLE Tipo_Rol (
  RolID       INT           NOT NULL AUTO_INCREMENT,
  Nombre      VARCHAR(50)   NOT NULL,
  Descripcion VARCHAR(150)  NOT NULL,
  PRIMARY KEY (RolID)
) ENGINE=InnoDB;

CREATE TABLE Turno (
  TurnoID     INT           NOT NULL AUTO_INCREMENT,
  Descripcion VARCHAR(50)   NOT NULL,
  Hora_Inicio TIME          NOT NULL,
  Hora_Fin    TIME          NOT NULL,
  PRIMARY KEY (TurnoID)
) ENGINE=InnoDB;

CREATE TABLE Empleado (
  EmpleadoID         INT           NOT NULL AUTO_INCREMENT,
  Nombre_Apellidos   VARCHAR(100)  NOT NULL,
  DNI                CHAR(8)       NOT NULL UNIQUE,
  Telefono           VARCHAR(15)   NOT NULL,
  Sueldo             DECIMAL(8,2)  NOT NULL,
  Estado             VARCHAR(20)   NOT NULL DEFAULT 'Activo',
  Fecha_Contratacion DATETIME      NOT NULL,
  RolID              INT           NOT NULL,
  TurnoID            INT           NOT NULL,
  -- Un empleado puede no tener acceso al sistema (NULL permitido)
  UsuarioID          INT           NULL UNIQUE,
  PRIMARY KEY (EmpleadoID),
  CONSTRAINT fk_emp_rol    FOREIGN KEY (RolID)     REFERENCES Tipo_Rol (RolID),
  CONSTRAINT fk_emp_turno  FOREIGN KEY (TurnoID)   REFERENCES Turno    (TurnoID),
  CONSTRAINT fk_emp_usu    FOREIGN KEY (UsuarioID) REFERENCES Usuario  (UsuarioID)
) ENGINE=InnoDB COMMENT='Personal del restaurante. UsuarioID NULL = sin acceso al sistema';

CREATE TABLE Asistencia (
  AsistenciaID  INT           NOT NULL AUTO_INCREMENT,
  EmpleadoID    INT           NOT NULL,
  Fecha         DATE          NOT NULL,
  Hora_Entrada  TIME          NULL,
  Hora_Salida   TIME          NULL,
  -- DECIMAL permite SUM/AVG en SQL; TIME no lo permite correctamente
  Horas_Extra   DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
  Observaciones VARCHAR(200)  NULL,
  PRIMARY KEY (AsistenciaID),
  CONSTRAINT fk_asis_emp FOREIGN KEY (EmpleadoID) REFERENCES Empleado (EmpleadoID)
) ENGINE=InnoDB;

-- =============================================================
--  BLOQUE 2 — PUNTO DE VENTA
-- =============================================================

CREATE TABLE Cliente (
  ClienteID        INT           NOT NULL AUTO_INCREMENT,
  Nombre_Apellidos VARCHAR(150)  NOT NULL,
  Tipo_Documento   CHAR(5)       NOT NULL DEFAULT 'DNI',
  Num_Documento    VARCHAR(11)   NOT NULL,
  Telefono         VARCHAR(15)   NULL,
  Direccion        VARCHAR(200)  NULL,
  Correo           VARCHAR(100)  NULL UNIQUE,
  Fecha_Creacion   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- NULL = cliente presencial anónimo sin cuenta web
  UsuarioID        INT           NULL UNIQUE,
  PRIMARY KEY (ClienteID),
  CONSTRAINT fk_cli_usu FOREIGN KEY (UsuarioID) REFERENCES Usuario (UsuarioID)
) ENGINE=InnoDB COMMENT='Correo UNIQUE permite registro web; NULL para clientes anónimos presenciales';

CREATE TABLE Mesa (
  MesaID      INT           NOT NULL AUTO_INCREMENT,
  -- Código legible separado del ID interno para la UI
  Codigo_Mesa VARCHAR(10)   NOT NULL UNIQUE,
  Numero_Mesa SMALLINT      NOT NULL,
  Capacidad   TINYINT       NOT NULL,
  Estado      VARCHAR(20)   NOT NULL DEFAULT 'Disponible',
  PRIMARY KEY (MesaID)
) ENGINE=InnoDB;

CREATE TABLE Categoria (
  CatID       INT           NOT NULL AUTO_INCREMENT,
  Nombre      VARCHAR(150)  NOT NULL,
  Descripcion VARCHAR(200)  NULL,
  -- Distingue si la categoría agrupa Platos o Insumos de almacén
  Tipo        VARCHAR(20)   NOT NULL DEFAULT 'Plato',
  PRIMARY KEY (CatID)
) ENGINE=InnoDB;

CREATE TABLE Plato (
  PlatoID     INT           NOT NULL AUTO_INCREMENT,
  Nombre      VARCHAR(150)  NOT NULL,
  Descripcion VARCHAR(500)  NULL,
  -- [v2] Ruta relativa a la imagen, nunca BLOB en BD
  -- Valor por defecto para platos sin foto aún cargada
  Imagen_URL  VARCHAR(255)  NULL DEFAULT 'assets/img/platos/default.png',
  Estado      VARCHAR(20)   NOT NULL DEFAULT 'Disponible',
  -- Orden de aparición en la carta web/app
  Orden       SMALLINT      NOT NULL DEFAULT 0,
  CatID       INT           NOT NULL,
  PRIMARY KEY (PlatoID),
  CONSTRAINT fk_plato_cat FOREIGN KEY (CatID) REFERENCES Categoria (CatID)
) ENGINE=InnoDB COMMENT='Carta del restaurante. Imagen_URL para e-commerce y carta digital';

CREATE TABLE Plato_Variante (
  VarianteID   INT           NOT NULL AUTO_INCREMENT,
  PlatoID      INT           NOT NULL,
  -- Ej: 'Porción personal', 'Para dos', 'Sin arroz'
  Nombre       VARCHAR(100)  NOT NULL,
  Precio_Venta DECIMAL(8,2)  NOT NULL,
  Estado       TINYINT       NOT NULL DEFAULT 1,
  PRIMARY KEY (VarianteID),
  CONSTRAINT fk_var_plato FOREIGN KEY (PlatoID) REFERENCES Plato (PlatoID)
) ENGINE=InnoDB;

CREATE TABLE Modificador_Grupo (
  GrupoID     INT           NOT NULL AUTO_INCREMENT,
  PlatoID     INT           NOT NULL,
  Nombre      VARCHAR(100)  NOT NULL,
  -- 'unico' = radio button | 'multiple' = checkboxes en la web
  Tipo        VARCHAR(20)   NOT NULL DEFAULT 'multiple',
  Obligatorio TINYINT       NOT NULL DEFAULT 0,
  PRIMARY KEY (GrupoID),
  CONSTRAINT fk_grupo_plato FOREIGN KEY (PlatoID) REFERENCES Plato (PlatoID)
) ENGINE=InnoDB;

CREATE TABLE Modificador_Opcion (
  OpcionID     INT           NOT NULL AUTO_INCREMENT,
  GrupoID      INT           NOT NULL,
  Nombre       VARCHAR(100)  NOT NULL,
  -- 0.00 = sin costo adicional, >0 = ingrediente extra con precio
  Precio_Extra DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
  Estado       TINYINT       NOT NULL DEFAULT 1,
  PRIMARY KEY (OpcionID),
  CONSTRAINT fk_opcion_grupo FOREIGN KEY (GrupoID) REFERENCES Modificador_Grupo (GrupoID)
) ENGINE=InnoDB;

CREATE TABLE Metodo_Pago (
  MetPagID            INT           NOT NULL AUTO_INCREMENT,
  Nombre              VARCHAR(50)   NOT NULL,
  -- Nombre del archivo de ícono para mostrar en la UI web
  Icono               VARCHAR(50)   NULL,
  -- Si el cajero debe pedir número de operación al cobrar
  Requiere_Referencia TINYINT       NOT NULL DEFAULT 0,
  Estado              TINYINT       NOT NULL DEFAULT 1,
  PRIMARY KEY (MetPagID)
) ENGINE=InnoDB COMMENT='Catálogo configurable por el admin. Escalable a nuevas empresas';

CREATE TABLE Pedido (
  PedidoID    INT           NOT NULL AUTO_INCREMENT,
  EmpleadoID  INT           NOT NULL,
  -- NULL = pedido web sin mesa (delivery u online)
  MesaID      INT           NULL,
  -- NULL = cliente anónimo en mostrador
  ClienteID   INT           NULL,
  Fecha_Hora  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- [v2] Origen diferencia el canal de venta
  -- 'Local' | 'Web' | 'App' | 'Telefono'
  Origen      VARCHAR(20)   NOT NULL DEFAULT 'Local',
  Estado      VARCHAR(20)   NOT NULL DEFAULT 'Pendiente',
  -- [v2] Estado_Pago separado del estado de preparación
  -- 'Pendiente' | 'Pagado_Online' | 'Pago_Contra_Entrega' | 'Pagado_Local'
  Estado_Pago VARCHAR(30)   NOT NULL DEFAULT 'Pendiente',
  Tipo_Pedido VARCHAR(20)   NOT NULL,
  Observaciones TEXT        NULL,
  PRIMARY KEY (PedidoID),
  CONSTRAINT fk_ped_emp  FOREIGN KEY (EmpleadoID) REFERENCES Empleado (EmpleadoID),
  CONSTRAINT fk_ped_mesa FOREIGN KEY (MesaID)     REFERENCES Mesa     (MesaID),
  CONSTRAINT fk_ped_cli  FOREIGN KEY (ClienteID)  REFERENCES Cliente  (ClienteID)
) ENGINE=InnoDB COMMENT='Origen+Estado_Pago permiten diferenciar canal web vs local';

CREATE TABLE Detalle_Pedido (
  DetPedID        INT            NOT NULL AUTO_INCREMENT,
  PedidoID        INT            NOT NULL,
  VarianteID      INT            NOT NULL,
  Cantidad        SMALLINT       NOT NULL,
  -- Precio guardado al momento de la venta (histórico, inmutable)
  Precio_Unitario DECIMAL(8,2)   NOT NULL,
  Subtotal        DECIMAL(10,2)  NOT NULL,
  Notas           VARCHAR(200)   NULL,
  PRIMARY KEY (DetPedID),
  CONSTRAINT fk_detped_ped FOREIGN KEY (PedidoID)  REFERENCES Pedido         (PedidoID),
  CONSTRAINT fk_detped_var FOREIGN KEY (VarianteID) REFERENCES Plato_Variante (VarianteID)
) ENGINE=InnoDB;

CREATE TABLE Detalle_Modificador (
  DetModID        INT           NOT NULL AUTO_INCREMENT,
  DetPedID        INT           NOT NULL,
  OpcionID        INT           NOT NULL,
  -- Precio al momento exacto de la venta, no el actual de la tabla
  Precio_Aplicado DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
  PRIMARY KEY (DetModID),
  CONSTRAINT fk_detmod_det FOREIGN KEY (DetPedID) REFERENCES Detalle_Pedido    (DetPedID),
  CONSTRAINT fk_detmod_op  FOREIGN KEY (OpcionID) REFERENCES Modificador_Opcion (OpcionID)
) ENGINE=InnoDB COMMENT='Snapshots de precios de modificadores al momento de la venta';

CREATE TABLE Comprobante_Pago (
  ComPagID           INT            NOT NULL AUTO_INCREMENT,
  PedidoID           INT            NOT NULL,
  -- NULL = boleta a consumidor final sin datos
  ClienteID          INT            NULL,
  MetPagID           INT            NOT NULL,
  Tipo_Comprobante   VARCHAR(20)    NOT NULL,
  Serie              CHAR(4)        NOT NULL,
  Numero             VARCHAR(20)    NOT NULL,
  IGV                DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  Total              DECIMAL(10,2)  NOT NULL,
  Fecha_Emision      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- ID del sistema externo de facturación electrónica
  Referencia_Externa VARCHAR(100)   NULL,
  PRIMARY KEY (ComPagID),
  CONSTRAINT fk_comp_ped    FOREIGN KEY (PedidoID)  REFERENCES Pedido      (PedidoID),
  CONSTRAINT fk_comp_cli    FOREIGN KEY (ClienteID) REFERENCES Cliente     (ClienteID),
  CONSTRAINT fk_comp_metpag FOREIGN KEY (MetPagID)  REFERENCES Metodo_Pago (MetPagID)
) ENGINE=InnoDB;

CREATE TABLE Caja (
  CajaID              INT            NOT NULL AUTO_INCREMENT,
  EmpleadoID          INT            NOT NULL,
  -- NOT NULL DEFAULT 0.00: evita NULLs en sumas de saldo
  Monto_Apertura      DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  Monto_Cierre        DECIMAL(10,2)  NULL,
  Fecha_hora_Apertura DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Fecha_hora_Cierre   DATETIME       NULL,
  Estado              VARCHAR(15)    NOT NULL DEFAULT 'Abierta',
  Observaciones       VARCHAR(200)   NULL,
  PRIMARY KEY (CajaID),
  CONSTRAINT fk_caja_emp FOREIGN KEY (EmpleadoID) REFERENCES Empleado (EmpleadoID)
) ENGINE=InnoDB;

-- =============================================================
--  BLOQUE 3 — INVENTARIO
-- =============================================================

CREATE TABLE Insumo (
  InsumoID      INT            NOT NULL AUTO_INCREMENT,
  Nombre        VARCHAR(150)   NOT NULL,
  Descripcion   VARCHAR(500)   NULL,
  Precio_Costo  DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
  Stock_Actual  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  Stock_Minimo  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  -- 'kg' | 'lt' | 'unidad' | 'bolsa' | 'caja'
  Unidad_Medida VARCHAR(20)    NOT NULL,
  Estado        VARCHAR(15)    NOT NULL DEFAULT 'Activo',
  CatID         INT            NOT NULL,
  PRIMARY KEY (InsumoID),
  CONSTRAINT fk_ins_cat FOREIGN KEY (CatID) REFERENCES Categoria (CatID)
) ENGINE=InnoDB COMMENT='Insumos de almacén. Nunca mezclar con Plato (carta del cliente)';

CREATE TABLE Receta (
  RecetaID      INT            NOT NULL AUTO_INCREMENT,
  VarianteID    INT            NOT NULL,
  InsumoID      INT            NOT NULL,
  -- 4 decimales para precisión en gramos y mililitros
  Cantidad      DECIMAL(10,4)  NOT NULL,
  Unidad_Medida VARCHAR(20)    NOT NULL,
  PRIMARY KEY (RecetaID),
  -- Una variante no puede tener el mismo insumo dos veces
  UNIQUE KEY uq_receta (VarianteID, InsumoID),
  CONSTRAINT fk_rec_var FOREIGN KEY (VarianteID) REFERENCES Plato_Variante (VarianteID),
  CONSTRAINT fk_rec_ins FOREIGN KEY (InsumoID)   REFERENCES Insumo         (InsumoID)
) ENGINE=InnoDB COMMENT='Fórmula de producción: variante → insumos necesarios y cantidades';

CREATE TABLE Proveedor (
  ProveedorID   INT           NOT NULL AUTO_INCREMENT,
  Razon_Social  VARCHAR(200)  NOT NULL,
  Ruc           CHAR(11)      NOT NULL UNIQUE,
  Contacto      VARCHAR(150)  NOT NULL,
  Telefono      VARCHAR(15)   NOT NULL,
  Direccion     VARCHAR(200)  NULL,
  Tipo_Producto VARCHAR(100)  NULL,
  Correo        VARCHAR(100)  NULL,
  -- 'A'ctivo | 'I'nactivo
  Estado        CHAR(1)       NOT NULL DEFAULT 'A',
  PRIMARY KEY (ProveedorID)
) ENGINE=InnoDB;

CREATE TABLE Kardex (
  KardexID        INT            NOT NULL AUTO_INCREMENT,
  InsumoID        INT            NOT NULL,
  -- NULL permitido: mermas y ajustes no tienen proveedor
  ProveedorID     INT            NULL,
  -- [v2] Trazabilidad: quién registró este movimiento
  EmpleadoID      INT            NULL,
  -- 'Entrada' | 'Salida' | 'Ajuste' | 'Merma'
  Tipo_Movimiento VARCHAR(20)    NOT NULL,
  Cantidad        DECIMAL(10,2)  NOT NULL,
  Precio_Unitario DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
  Fecha           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Motivo          VARCHAR(200)   NOT NULL,
  -- NULL en salidas/mermas que no vienen de un lote específico
  Lote            VARCHAR(50)    NULL,
  PRIMARY KEY (KardexID),
  CONSTRAINT fk_kar_ins  FOREIGN KEY (InsumoID)   REFERENCES Insumo    (InsumoID),
  CONSTRAINT fk_kar_prov FOREIGN KEY (ProveedorID) REFERENCES Proveedor (ProveedorID),
  CONSTRAINT fk_kar_emp  FOREIGN KEY (EmpleadoID)  REFERENCES Empleado  (EmpleadoID)
) ENGINE=InnoDB COMMENT='EmpleadoID registra responsabilidad en mermas y ajustes';

-- =============================================================
--  BLOQUE 4 — DELIVERY (BD lista, desarrollo en Fase 2)
-- =============================================================

CREATE TABLE Zona_Cobertura (
  ZonaID              INT           NOT NULL AUTO_INCREMENT,
  Nombre              VARCHAR(100)  NOT NULL,
  Precio_Delivery     DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
  Tiempo_Estimado_Min INT           NOT NULL DEFAULT 30,
  Estado              TINYINT       NOT NULL DEFAULT 1,
  PRIMARY KEY (ZonaID)
) ENGINE=InnoDB;

CREATE TABLE Repartidor (
  RepartidorID  INT           NOT NULL AUTO_INCREMENT,
  -- NULL = repartidor externo (Rappi, PedidosYa) no es empleado
  EmpleadoID    INT           NULL,
  Nombre        VARCHAR(150)  NOT NULL,
  Telefono      VARCHAR(15)   NOT NULL,
  Vehiculo      VARCHAR(50)   NULL,
  Estado        VARCHAR(20)   NOT NULL DEFAULT 'Disponible',
  PRIMARY KEY (RepartidorID),
  CONSTRAINT fk_rep_emp FOREIGN KEY (EmpleadoID) REFERENCES Empleado (EmpleadoID)
) ENGINE=InnoDB;

CREATE TABLE Delivery (
  DeliveryID           INT            NOT NULL AUTO_INCREMENT,
  PedidoID             INT            NOT NULL,
  RepartidorID         INT            NULL,
  ZonaID               INT            NULL,
  Direccion_Entrega    VARCHAR(300)   NOT NULL,
  -- 'Frente al parque', 'Casa azul con reja negra'
  Referencia_Direccion VARCHAR(200)   NULL,
  Estado               VARCHAR(30)    NOT NULL DEFAULT 'Pendiente',
  Costo_Delivery       DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
  Fecha_Asignacion     DATETIME       NULL,
  Fecha_Entrega        DATETIME       NULL,
  PRIMARY KEY (DeliveryID),
  CONSTRAINT fk_del_ped  FOREIGN KEY (PedidoID)     REFERENCES Pedido         (PedidoID),
  CONSTRAINT fk_del_rep  FOREIGN KEY (RepartidorID) REFERENCES Repartidor     (RepartidorID),
  CONSTRAINT fk_del_zona FOREIGN KEY (ZonaID)       REFERENCES Zona_Cobertura (ZonaID)
) ENGINE=InnoDB;

CREATE TABLE Integracion_Externa (
  IntExtID   INT           NOT NULL AUTO_INCREMENT,
  PedidoID   INT           NOT NULL,
  -- 'Rappi' | 'PedidosYa' | 'Web' | 'WhatsApp'
  Plataforma VARCHAR(50)   NOT NULL,
  ID_Externo VARCHAR(100)  NOT NULL,
  Estado     VARCHAR(30)   NOT NULL DEFAULT 'Recibido',
  Comision   DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
  Fecha_Sync DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (IntExtID),
  CONSTRAINT fk_int_ped FOREIGN KEY (PedidoID) REFERENCES Pedido (PedidoID)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
--  SEED DATA — DATOS DE PRUEBA
--  Contexto: Chifa "El Dragón de Oro", Tumbes, Perú
-- =============================================================

-- ── Bloque 0: Autenticación ───────────────────────────────────

INSERT INTO Tipo_Usuario (Descripcion, Scope) VALUES
  ('Administrador del sistema', 'backoffice'),  -- 1
  ('Cajero / Vendedor',         'backoffice'),  -- 2
  ('Mozo',                      'backoffice'),  -- 3
  ('Almacenero',                'backoffice'),  -- 4
  ('Cliente web',               'web');         -- 5

-- Contraseñas de prueba (bcrypt de 'password123' simulado)
-- En producción PHP: password_hash('texto', PASSWORD_BCRYPT)
INSERT INTO Usuario (Login, Contrasena, Estado, TipUsuID) VALUES
  ('admin',        '$2y$10$hashAdmin000000000000000000000000000000000000000000000', 'Activo', 1),
  ('cajera.maria', '$2y$10$hashMaria000000000000000000000000000000000000000000000', 'Activo', 2),
  ('mozo.jose',    '$2y$10$hashJose0000000000000000000000000000000000000000000000', 'Activo', 3),
  ('mozo.ana',     '$2y$10$hashAna00000000000000000000000000000000000000000000000', 'Activo', 3),
  ('almacen.rosa', '$2y$10$hashRosa0000000000000000000000000000000000000000000000', 'Activo', 4),
  -- Clientes web registrados desde la página
  ('pedro.salas',  '$2y$10$hashPedro000000000000000000000000000000000000000000000', 'Activo', 5),
  ('lucia.ramos',  '$2y$10$hashLucia000000000000000000000000000000000000000000000', 'Activo', 5);

-- ── Bloque 1: Personal ───────────────────────────────────────

INSERT INTO Tipo_Rol (Nombre, Descripcion) VALUES
  ('Administrador', 'Acceso total al sistema'),
  ('Cajero',        'Gestión de caja, pedidos y comprobantes'),
  ('Mozo',          'Toma de pedidos en sala'),
  ('Cocinero',      'Preparación de platos en cocina'),
  ('Almacenero',    'Control de inventario e insumos');

INSERT INTO Turno (Descripcion, Hora_Inicio, Hora_Fin) VALUES
  ('Mañana',  '08:00:00', '14:00:00'),
  ('Tarde',   '14:00:00', '20:00:00'),
  ('Noche',   '20:00:00', '23:59:00'),
  ('Completo','08:00:00', '20:00:00');

-- UsuarioID referencia la tabla Usuario (IDs 1-5 del seed anterior)
INSERT INTO Empleado (Nombre_Apellidos, DNI, Telefono, Sueldo, Estado, Fecha_Contratacion, RolID, TurnoID, UsuarioID) VALUES
  ('Carlos Mendoza Ríos',  '45231876', '987654321', 2500.00, 'Activo', '2023-01-15 08:00:00', 1, 4, 1),
  ('María Torres Huanca',  '52341987', '976543218', 1800.00, 'Activo', '2023-03-01 08:00:00', 2, 1, 2),
  ('José Quispe Lima',     '63452198', '965432187', 1500.00, 'Activo', '2023-03-15 08:00:00', 3, 1, 3),
  ('Ana Flores Chávez',    '74563209', '954321876', 1500.00, 'Activo', '2023-04-01 08:00:00', 3, 2, 4),
  ('Luis Wong Taipe',      '85674310', '943218765', 2000.00, 'Activo', '2022-11-01 08:00:00', 4, 4, NULL),
  ('Rosa Mamani Ccopa',    '96785421', '932187654', 1600.00, 'Activo', '2024-01-10 08:00:00', 5, 1, 5);

INSERT INTO Asistencia (EmpleadoID, Fecha, Hora_Entrada, Hora_Salida, Horas_Extra, Observaciones) VALUES
  (1, '2026-05-18', '07:55:00', '20:10:00', 0.00, NULL),
  (2, '2026-05-18', '07:50:00', '14:05:00', 0.00, NULL),
  (3, '2026-05-18', '08:02:00', '14:00:00', 0.00, NULL),
  (4, '2026-05-18', '14:00:00', '20:30:00', 0.50, 'Turno extendido por evento'),
  (5, '2026-05-18', '07:45:00', '20:15:00', 0.25, NULL),
  (6, '2026-05-18', '08:00:00', '14:00:00', 0.00, NULL);

-- ── Bloque 2: Punto de Venta ─────────────────────────────────

INSERT INTO Cliente (Nombre_Apellidos, Tipo_Documento, Num_Documento, Telefono, Direccion, Correo, UsuarioID) VALUES
  -- Cliente ficticio para ventas anónimas (no tiene usuario web)
  ('Consumidor Final',    'DNI', '00000000', NULL,         NULL,                        NULL,                    NULL),
  -- Clientes con cuenta web (UsuarioID 6 y 7)
  ('Pedro Salas Vega',    'DNI', '12345678', '987001122', 'Jr. Lima 123, Tumbes',       'pedro.salas@email.com', 6),
  ('Empresa SAC',         'RUC', '20512345678', '072-1234', 'Av. Principal 456, Tumbes','factura@empresa.com',   NULL),
  ('Lucía Ramos Torres',  'DNI', '87654321', '976002233', 'Calle Los Jardines 78',      'lucia.ramos@email.com', 7);

INSERT INTO Mesa (Codigo_Mesa, Numero_Mesa, Capacidad, Estado) VALUES
  ('MESA-01', 1, 4, 'Disponible'),
  ('MESA-02', 2, 4, 'Disponible'),
  ('MESA-03', 3, 6, 'Disponible'),
  ('MESA-04', 4, 2, 'Disponible'),
  ('MESA-05', 5, 8, 'Disponible'),
  ('MESA-06', 6, 4, 'Disponible'),
  ('BARRA-01',7, 2, 'Disponible');

INSERT INTO Categoria (Nombre, Descripcion, Tipo) VALUES
  ('Arroces y Chaufas',    'Platos a base de arroz salteado al wok',  'Plato'),   -- 1
  ('Sopas y Caldos',       'Caldos y sopas preparados al momento',    'Plato'),   -- 2
  ('Carnes al Wok',        'Carnes y mariscos salteados con verduras','Plato'),   -- 3
  ('Bebidas',              'Bebidas frías y calientes',               'Plato'),   -- 4
  ('Granos y Cereales',    'Arroz, fideos y harinas',                 'Insumo'),  -- 5
  ('Carnes y Aves',        'Pollo, cerdo, res y mariscos frescos',    'Insumo'),  -- 6
  ('Verduras',             'Verduras frescas y congeladas',           'Insumo'),  -- 7
  ('Salsas y Condimentos', 'Sillao, oyster sauce, aceites y especias','Insumo');  -- 8

INSERT INTO Plato (Nombre, Descripcion, Imagen_URL, Estado, Orden, CatID) VALUES
  ('Arroz Chaufa de Pollo', 'Arroz salteado con pollo, huevo, cebolla china y sillao',         'assets/img/platos/chaufa_pollo.jpg',   'Disponible', 1, 1),
  ('Arroz Chaufa Especial', 'Arroz salteado con pollo, cerdo, camarones y verduras variadas',  'assets/img/platos/chaufa_especial.jpg','Disponible', 2, 1),
  ('Sopa Wantán',           'Caldo con wantanes de cerdo y verduras de temporada',             'assets/img/platos/sopa_wantan.jpg',    'Disponible', 3, 2),
  ('Lomo Saltado Chifa',    'Lomo de res salteado con pimientos, tomate y sillao',             'assets/img/platos/lomo_saltado.jpg',   'Disponible', 4, 3),
  ('Pollo Tipakay',         'Pollo frito crujiente bañado en salsa agridulce de la casa',      'assets/img/platos/tipakay.jpg',        'Disponible', 5, 3),
  ('Inca Kola 500ml',       'Bebida gaseosa personal',                                         'assets/img/platos/incakola.jpg',       'Disponible', 6, 4);

INSERT INTO Plato_Variante (PlatoID, Nombre, Precio_Venta, Estado) VALUES
  (1, 'Personal',        18.00, 1),  -- 1
  (1, 'Para dos',        32.00, 1),  -- 2
  (2, 'Personal',        24.00, 1),  -- 3
  (2, 'Para dos',        44.00, 1),  -- 4
  (3, 'Regular',         16.00, 1),  -- 5
  (3, 'Grande',          22.00, 1),  -- 6
  (4, 'Porción regular', 28.00, 1),  -- 7
  (5, 'Porción regular', 22.00, 1),  -- 8
  (6, 'Unidad',           4.00, 1);  -- 9

INSERT INTO Modificador_Grupo (PlatoID, Nombre, Tipo, Obligatorio) VALUES
  (1, 'Ingredientes a retirar', 'multiple', 0),  -- 1
  (1, 'Extras',                 'multiple', 0),  -- 2
  (3, 'Proteína del Wantán',    'unico',    1),  -- 3
  (4, 'Término de cocción',     'unico',    1),  -- 4
  (5, 'Salsa',                  'unico',    0);  -- 5

INSERT INTO Modificador_Opcion (GrupoID, Nombre, Precio_Extra, Estado) VALUES
  (1, 'Sin cebolla china', 0.00, 1),
  (1, 'Sin huevo',         0.00, 1),
  (1, 'Sin sillao',        0.00, 1),
  (2, 'Extra salsa sillao',2.00, 1),
  (2, 'Extra pollo',       4.00, 1),
  (3, 'Cerdo',             0.00, 1),
  (3, 'Pollo',             0.00, 1),
  (3, 'Mixto',             2.00, 1),
  (4, 'Término medio',     0.00, 1),
  (4, 'Bien cocido',       0.00, 1),
  (5, 'Agridulce',         0.00, 1),
  (5, 'Tamarindo',         0.00, 1),
  (5, 'Sin salsa',         0.00, 1);

INSERT INTO Metodo_Pago (Nombre, Icono, Requiere_Referencia, Estado) VALUES
  ('Efectivo',      'efectivo.png',     0, 1),
  ('Yape',          'yape.png',         1, 1),
  ('Plin',          'plin.png',         1, 1),
  ('Tarjeta',       'tarjeta.png',      1, 1),
  ('Transferencia', 'transferencia.png',1, 1);

-- Pedido 1: local, mesa 1, cliente anónimo
-- Pedido 2: local, mesa 2, cliente registrado
-- Pedido 3: web, sin mesa, cliente con cuenta web (Pedro Salas)
INSERT INTO Pedido (EmpleadoID, MesaID, ClienteID, Origen, Estado, Estado_Pago, Tipo_Pedido) VALUES
  (3, 1, 1, 'Local', 'Entregado',  'Pagado_Local',   'Mesa'),
  (3, 2, 2, 'Local', 'En cocina',  'Pendiente',      'Mesa'),
  (2, NULL,2, 'Web', 'Pendiente',  'Pagado_Online',  'Delivery');

INSERT INTO Detalle_Pedido (PedidoID, VarianteID, Cantidad, Precio_Unitario, Subtotal, Notas) VALUES
  (1, 1, 2, 18.00, 36.00, NULL),
  (1, 9, 2,  4.00,  8.00, NULL),
  (2, 3, 1, 24.00, 24.00, NULL),
  (2, 5, 2, 16.00, 32.00, NULL),
  (3, 7, 1, 28.00, 28.00, NULL);

INSERT INTO Detalle_Modificador (DetPedID, OpcionID, Precio_Aplicado) VALUES
  (3, 1, 0.00),   -- Pedido 2, Wantán: Sin cebolla china
  (5, 9, 0.00);   -- Pedido 3, Lomo: Término medio

INSERT INTO Comprobante_Pago (PedidoID, ClienteID, MetPagID, Tipo_Comprobante, Serie, Numero, IGV, Total, Referencia_Externa) VALUES
  (1, 1, 1, 'Boleta',  'B001', '00000001', 7.92,  44.00, NULL),
  (3, 2, 4, 'Boleta',  'B001', '00000002', 5.04,  28.00, 'TXN-CULQI-2026-001');

INSERT INTO Caja (EmpleadoID, Monto_Apertura, Fecha_hora_Apertura, Estado) VALUES
  (2, 200.00, '2026-05-19 08:00:00', 'Abierta');

-- ── Bloque 3: Inventario ─────────────────────────────────────

INSERT INTO Insumo (Nombre, Descripcion, Precio_Costo, Stock_Actual, Stock_Minimo, Unidad_Medida, Estado, CatID) VALUES
  ('Arroz largo',         'Arroz largo extra para chaufa',  2.50,  80.00, 20.00, 'kg',     'Activo', 5),
  ('Pollo entero',        'Pollo fresco por kilo',          8.50,  25.00,  5.00, 'kg',     'Activo', 6),
  ('Lomo de res',         'Lomo fino de res',              22.00,  10.00,  2.00, 'kg',     'Activo', 6),
  ('Cebolla china',       'Cebolla china fresca',           3.00,   8.00,  2.00, 'kg',     'Activo', 7),
  ('Huevo',               'Huevo de gallina fresco',        0.50,  60.00, 12.00, 'unidad', 'Activo', 7),
  ('Sillao oscuro',       'Sillao oscuro premium',          6.00,   5.00,  1.00, 'lt',     'Activo', 8),
  ('Aceite vegetal',      'Aceite para saltear al wok',     5.50,  10.00,  2.00, 'lt',     'Activo', 8),
  ('Camarones medianos',  'Camarones frescos pelados',     18.00,   4.00,  1.00, 'kg',     'Activo', 6);

INSERT INTO Proveedor (Razon_Social, Ruc, Contacto, Telefono, Direccion, Tipo_Producto, Correo, Estado) VALUES
  ('Distribuidora Wong SAC', '20411234567', 'Sr. Wong',    '072-123456', 'Av. Comercio 100, Tumbes', 'Abarrotes y salsas', 'ventas@wongdist.com',  'A'),
  ('Avícola Los Andes EIRL', '20522345678', 'Sra. Quispe', '072-234567', 'Mercado Central, Tumbes', 'Carnes y aves',      'alosandes@mail.com',   'A'),
  ('Mariscos del Norte SAC', '20633456789', 'Sr. Torres',  '072-345678', 'Puerto Pesquero, Tumbes', 'Mariscos frescos',   'mnorte@mail.com',      'A');

INSERT INTO Receta (VarianteID, InsumoID, Cantidad, Unidad_Medida) VALUES
  -- Chaufa de Pollo Personal (VarianteID 1)
  (1, 1, 0.2500, 'kg'),
  (1, 2, 0.1500, 'kg'),
  (1, 5, 1.0000, 'unidad'),
  (1, 4, 0.0300, 'kg'),
  (1, 6, 0.0200, 'lt'),
  (1, 7, 0.0300, 'lt'),
  -- Chaufa de Pollo Para dos (VarianteID 2)
  (2, 1, 0.5000, 'kg'),
  (2, 2, 0.3000, 'kg'),
  (2, 5, 2.0000, 'unidad'),
  (2, 4, 0.0600, 'kg'),
  (2, 6, 0.0400, 'lt'),
  (2, 7, 0.0600, 'lt');

-- EmpleadoID registrado en cada movimiento (trazabilidad v2)
INSERT INTO Kardex (InsumoID, ProveedorID, EmpleadoID, Tipo_Movimiento, Cantidad, Precio_Unitario, Motivo, Lote) VALUES
  (1, 1, 6, 'Entrada', 100.00, 2.50, 'Compra semanal de arroz',        'LOTE-2026-001'),
  (2, 2, 6, 'Entrada',  30.00, 8.50, 'Compra diaria de pollo',         'LOTE-2026-002'),
  (3, 2, 6, 'Entrada',  12.00,22.00, 'Compra de lomo para la semana',  'LOTE-2026-003'),
  (8, 3, 6, 'Entrada',   5.00,18.00, 'Compra de camarones frescos',    'LOTE-2026-004'),
  -- Salidas de cocina: EmpleadoID registrado, ProveedorID NULL
  (1, NULL, 5, 'Salida',  20.00, 2.50, 'Consumo cocina turno mañana',  NULL),
  (2, NULL, 5, 'Salida',   5.00, 8.50, 'Consumo cocina turno mañana',  NULL),
  -- Merma reportada por almacenero (Rosa = EmpleadoID 6)
  (5, NULL, 6, 'Merma',    3.00, 0.50, 'Huevos rotos en almacén',      NULL);

-- ── Bloque 4: Delivery (seed base) ───────────────────────────

INSERT INTO Zona_Cobertura (Nombre, Precio_Delivery, Tiempo_Estimado_Min, Estado) VALUES
  ('Zona Centro',        3.00, 20, 1),
  ('Zona Norte',         5.00, 35, 1),
  ('Zona Sur',           5.00, 35, 1),
  ('Zona Este',          7.00, 45, 1),
  ('Fuera de cobertura', 0.00,  0, 0);

INSERT INTO Repartidor (EmpleadoID, Nombre, Telefono, Vehiculo, Estado) VALUES
  (NULL, 'Miguel Sosa',  '998877665', 'Moto - MX-1234', 'Disponible'),
  (NULL, 'Carlos Rivas', '997766554', 'Bicicleta',       'Disponible');

-- Delivery del pedido 3 (origen web, ya pagado online)
INSERT INTO Delivery (PedidoID, RepartidorID, ZonaID, Direccion_Entrega, Referencia_Direccion, Estado, Costo_Delivery) VALUES
  (3, NULL, 1, 'Jr. Lima 123, Tumbes', 'Casa amarilla, puerta azul', 'Pendiente', 3.00);

-- =============================================================
--  VISTAS ÚTILES PARA PHP
-- =============================================================

-- Pedidos activos con origen y estado de pago (v2)
CREATE OR REPLACE VIEW v_pedidos_activos AS
SELECT
  p.PedidoID,
  p.Origen,
  p.Tipo_Pedido,
  p.Estado,
  p.Estado_Pago,
  p.Fecha_Hora,
  m.Codigo_Mesa,
  COALESCE(c.Nombre_Apellidos, 'Anónimo') AS Cliente,
  e.Nombre_Apellidos                       AS Empleado
FROM Pedido   p
LEFT JOIN Mesa    m ON p.MesaID     = m.MesaID
LEFT JOIN Cliente c ON p.ClienteID  = c.ClienteID
JOIN  Empleado    e ON p.EmpleadoID = e.EmpleadoID
WHERE p.Estado NOT IN ('Pagado', 'Cancelado');

-- Insumos con stock por debajo del mínimo
CREATE OR REPLACE VIEW v_stock_critico AS
SELECT
  i.InsumoID,
  i.Nombre,
  i.Stock_Actual,
  i.Stock_Minimo,
  i.Unidad_Medida,
  c.Nombre                              AS Categoria,
  (i.Stock_Minimo - i.Stock_Actual)     AS Unidades_Faltantes
FROM Insumo    i
JOIN Categoria c ON i.CatID = c.CatID
WHERE i.Stock_Actual <= i.Stock_Minimo
  AND i.Estado = 'Activo'
ORDER BY Unidades_Faltantes DESC;

-- Ventas del día agrupadas por método de pago
CREATE OR REPLACE VIEW v_ventas_dia AS
SELECT
  mp.Nombre                AS Metodo_Pago,
  COUNT(cp.ComPagID)       AS Total_Comprobantes,
  SUM(cp.Total)            AS Monto_Total
FROM Comprobante_Pago cp
JOIN Metodo_Pago      mp ON cp.MetPagID = mp.MetPagID
WHERE DATE(cp.Fecha_Emision) = CURDATE()
GROUP BY mp.MetPagID, mp.Nombre;

-- Detalle de pedido con modificadores (para comanda y precuenta)
CREATE OR REPLACE VIEW v_detalle_pedido_completo AS
SELECT
  dp.DetPedID,
  dp.PedidoID,
  pl.Nombre                                                      AS Plato,
  pv.Nombre                                                      AS Variante,
  dp.Cantidad,
  dp.Precio_Unitario,
  dp.Subtotal,
  dp.Notas,
  GROUP_CONCAT(mo.Nombre ORDER BY mo.Nombre SEPARATOR ', ')      AS Modificadores
FROM Detalle_Pedido    dp
JOIN Plato_Variante    pv ON dp.VarianteID  = pv.VarianteID
JOIN Plato             pl ON pv.PlatoID     = pl.PlatoID
LEFT JOIN Detalle_Modificador dm ON dp.DetPedID  = dm.DetPedID
LEFT JOIN Modificador_Opcion  mo ON dm.OpcionID  = mo.OpcionID
GROUP BY dp.DetPedID, dp.PedidoID, pl.Nombre, pv.Nombre,
         dp.Cantidad, dp.Precio_Unitario, dp.Subtotal, dp.Notas;

-- Carta completa para la web (platos disponibles con variantes)
CREATE OR REPLACE VIEW v_carta_web AS
SELECT
  pl.PlatoID,
  pl.Nombre                AS Plato,
  pl.Descripcion,
  pl.Imagen_URL,
  pl.Orden,
  cat.Nombre               AS Categoria,
  pv.VarianteID,
  pv.Nombre                AS Variante,
  pv.Precio_Venta
FROM Plato         pl
JOIN Categoria     cat ON pl.CatID    = cat.CatID
JOIN Plato_Variante pv ON pl.PlatoID  = pv.PlatoID
WHERE pl.Estado  = 'Disponible'
  AND pv.Estado  = 1
  AND cat.Tipo   = 'Plato'
ORDER BY cat.Nombre, pl.Orden, pv.Precio_Venta;

-- Kardex con nombre de responsable (trazabilidad v2)
CREATE OR REPLACE VIEW v_kardex_trazable AS
SELECT
  k.KardexID,
  k.Fecha,
  k.Tipo_Movimiento,
  i.Nombre                             AS Insumo,
  k.Cantidad,
  i.Unidad_Medida,
  k.Precio_Unitario,
  (k.Cantidad * k.Precio_Unitario)     AS Valor_Total,
  k.Motivo,
  k.Lote,
  COALESCE(p.Razon_Social, '—')        AS Proveedor,
  COALESCE(e.Nombre_Apellidos, '—')    AS Responsable
FROM Kardex   k
JOIN Insumo   i ON k.InsumoID    = i.InsumoID
LEFT JOIN Proveedor p ON k.ProveedorID = p.ProveedorID
LEFT JOIN Empleado  e ON k.EmpleadoID  = e.EmpleadoID
ORDER BY k.Fecha DESC;

-- =============================================================
--  FIN DEL SCRIPT v2.0
--  Tablas : 24  |  Vistas : 6  |  Módulos : 4
--  Auth   : Usuario universal (Empleado + Cliente → Usuario)
--  Canal  : POS local + E-commerce web + Delivery (fase 2)
-- =============================================================
