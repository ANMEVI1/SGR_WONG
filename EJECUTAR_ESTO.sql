-- =====================================================
-- EJECUTAR ESTE SCRIPT EN PHPMYADMIN
-- Base de datos: db_restaurante
-- =====================================================

USE db_restaurante;

-- ══════════════════════════════════════════════════════
-- PARTE 1: Agregar campos Es_Top y Es_Promo a Plato
-- ══════════════════════════════════════════════════════
ALTER TABLE Plato
    ADD COLUMN IF NOT EXISTS Es_Top   TINYINT NOT NULL DEFAULT 0 AFTER Orden,
    ADD COLUMN IF NOT EXISTS Es_Promo TINYINT NOT NULL DEFAULT 0 AFTER Es_Top;

-- Marcar algunos platos de prueba como Top/Promo
UPDATE Plato SET Es_Top = 1 WHERE PlatoID IN (1, 2);
UPDATE Plato SET Es_Promo = 1 WHERE PlatoID IN (1, 3);

SELECT 'Parte 1 completada: Campos Es_Top y Es_Promo agregados' AS Status;

-- ══════════════════════════════════════════════════════
-- PARTE 2: Sistema de Menú del Día (12pm-4pm)
-- ══════════════════════════════════════════════════════

-- Tabla principal: Menú del Día (combo económico)
CREATE TABLE IF NOT EXISTS Menu_Dia (
  MenuDiaID   INT           NOT NULL AUTO_INCREMENT,
  Nombre      VARCHAR(150)  NOT NULL COMMENT 'Ej: Menú Ejecutivo, Menú del Día',
  Descripcion VARCHAR(500)  NULL COMMENT 'Breve descripción del menú',
  Precio      DECIMAL(8,2)  NOT NULL COMMENT 'Precio único del combo',
  Estado      VARCHAR(20)   NOT NULL DEFAULT 'Disponible' COMMENT 'Disponible | Oculto',
  -- Horario configurable
  Hora_Inicio TIME          NOT NULL DEFAULT '12:00:00' COMMENT 'Hora inicio de disponibilidad',
  Hora_Fin    TIME          NOT NULL DEFAULT '16:00:00' COMMENT 'Hora fin de disponibilidad',
  -- Imagen y orden
  Imagen_URL  VARCHAR(255)  NULL DEFAULT 'assets/img/menu-dia/default.jpg',
  Orden       SMALLINT      NOT NULL DEFAULT 0 COMMENT 'Orden de aparición en web',
  -- Días de semana activo (JSON array o CSV)
  Dias_Activo VARCHAR(50)   NOT NULL DEFAULT 'L,M,X,J,V,S,D' COMMENT 'Días activos: L,M,X,J,V,S,D',
  PRIMARY KEY (MenuDiaID)
) ENGINE=InnoDB COMMENT='Menú económico del día (12pm-4pm) - Combos';

-- Tabla de componentes del menú
CREATE TABLE IF NOT EXISTS Menu_Dia_Componente (
  ComponenteID INT           NOT NULL AUTO_INCREMENT,
  MenuDiaID    INT           NOT NULL,
  -- Tipo de componente
  Tipo         VARCHAR(20)   NOT NULL COMMENT 'entrada | sopa | segundo | bebida',
  -- Plato específico o descripción libre
  PlatoID      INT           NULL COMMENT 'NULL = opción libre/variable',
  Descripcion  VARCHAR(200)  NOT NULL COMMENT 'Ej: Wantan pequeño O wantan frito',
  Orden        TINYINT       NOT NULL DEFAULT 0 COMMENT 'Orden de presentación',
  PRIMARY KEY (ComponenteID),
  CONSTRAINT fk_menu_dia FOREIGN KEY (MenuDiaID) 
    REFERENCES Menu_Dia (MenuDiaID) ON DELETE CASCADE,
  CONSTRAINT fk_menu_plato FOREIGN KEY (PlatoID) 
    REFERENCES Plato (PlatoID) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Componentes del menú del día';

SELECT 'Parte 2 completada: Tablas Menu_Dia creadas' AS Status;

-- ══════════════════════════════════════════════════════
-- PARTE 3: Datos de prueba para Menú del Día
-- ══════════════════════════════════════════════════════

-- Insertar menú de ejemplo
INSERT INTO Menu_Dia (Nombre, Descripcion, Precio, Estado, Hora_Inicio, Hora_Fin, Imagen_URL, Orden, Dias_Activo)
VALUES 
  ('Menú Ejecutivo', 
   'Incluye entrada + sopa + segundo. Disponible de lunes a viernes de 12pm a 4pm', 
   15.00, 
   'Disponible', 
   '12:00:00', 
   '16:00:00', 
   'assets/img/menu-dia/ejecutivo.jpg', 
   1,
   'L,M,X,J,V'),
  ('Menú del Día', 
   'Incluye entrada + sopa wantan + plato de fondo + refresco', 
   12.00, 
   'Disponible', 
   '12:00:00', 
   '16:00:00', 
   'assets/img/menu-dia/economico.jpg', 
   2,
   'L,M,X,J,V,S,D');

-- Obtener IDs insertados (asumimos que son 1 y 2 si la tabla estaba vacía)
SET @menuEjecutivo = 1;
SET @menuDia = 2;

-- Componentes del Menú Ejecutivo
INSERT INTO Menu_Dia_Componente (MenuDiaID, Tipo, PlatoID, Descripcion, Orden)
VALUES 
  (@menuEjecutivo, 'entrada', NULL, 'Entrada del día (variable)', 1),
  (@menuEjecutivo, 'sopa', 3, 'Sopa Wantán pequeña', 2),
  (@menuEjecutivo, 'segundo', NULL, 'Plato de fondo a elegir', 3);

-- Componentes del Menú del Día
INSERT INTO Menu_Dia_Componente (MenuDiaID, Tipo, PlatoID, Descripcion, Orden)
VALUES 
  (@menuDia, 'entrada', NULL, 'Entrada del día', 1),
  (@menuDia, 'sopa', NULL, 'Wantan pequeño O wantan frito', 2),
  (@menuDia, 'segundo', NULL, 'Arroz chaufa O tallarín saltado', 3),
  (@menuDia, 'bebida', 6, 'Inca Kola 500ml', 4);

SELECT 'Parte 3 completada: Datos de prueba insertados' AS Status;

-- ══════════════════════════════════════════════════════
-- VERIFICACIÓN FINAL
-- ══════════════════════════════════════════════════════

SELECT '✅ SCRIPT COMPLETADO EXITOSAMENTE' AS Status;
SELECT 'Verificando estructura...' AS Info;

-- Verificar tabla Plato
SELECT 'Tabla Plato:' AS Tabla, COUNT(*) AS Registros FROM Plato;
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'db_restaurante' 
  AND TABLE_NAME = 'Plato' 
  AND COLUMN_NAME IN ('Es_Top', 'Es_Promo');

-- Verificar tabla Menu_Dia
SELECT 'Tabla Menu_Dia:' AS Tabla, COUNT(*) AS Registros FROM Menu_Dia;
SELECT 'Tabla Menu_Dia_Componente:' AS Tabla, COUNT(*) AS Registros FROM Menu_Dia_Componente;

-- Ver menús activos
SELECT MenuDiaID, Nombre, Precio, Hora_Inicio, Hora_Fin, Estado 
FROM Menu_Dia 
ORDER BY Orden;

SELECT '🎉 Sistema listo para usar' AS Mensaje;
