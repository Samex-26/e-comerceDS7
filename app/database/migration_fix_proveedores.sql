-- Para instalaciones que ya ejecutaron migration_phase1.sql antes de esta corrección.
USE venta_productos;
ALTER TABLE proveedores
  DROP CHECK chk_proveedores_estrellas,
  ALTER calificacion_estrellas SET DEFAULT 0,
  ADD CONSTRAINT chk_proveedores_estrellas CHECK (calificacion_estrellas BETWEEN 0 AND 5);
