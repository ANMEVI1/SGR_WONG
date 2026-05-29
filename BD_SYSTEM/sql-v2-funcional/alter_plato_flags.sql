-- Ejecutar en db_restaurante para agregar soporte de Top Ventas y Promociones
-- controlables desde el panel admin

ALTER TABLE Plato
    ADD COLUMN Es_Top   TINYINT NOT NULL DEFAULT 0 AFTER Orden,
    ADD COLUMN Es_Promo TINYINT NOT NULL DEFAULT 0 AFTER Es_Top;

-- Marcar algunos platos de prueba como top/promo (ajusta los IDs según tu seed)
UPDATE Plato SET Es_Top = 1 WHERE PlatoID IN (1, 3, 5);
UPDATE Plato SET Es_Promo = 1 WHERE PlatoID IN (2, 4);
