-- Migración incremental, no destructiva. Ejecutar una sola vez sobre venta_productos.
-- Hacer backup lógico antes. No modifica firmas históricas.
START TRANSACTION;

ALTER TABLE ventas
  ADD COLUMN idempotency_key CHAR(64) NULL,
  ADD COLUMN firma_version TINYINT UNSIGNED NOT NULL DEFAULT 1;

ALTER TABLE detalle_ventas
  ADD COLUMN costo_unitario_historico DECIMAL(10,2) NULL;

-- Las ventas anteriores conservan NULL y los reportes usan el costo actual como
-- fallback explícito; no se inventa un costo histórico que no fue almacenado.
CREATE UNIQUE INDEX uq_ventas_idempotency_key ON ventas (idempotency_key);

COMMIT;

-- Reversión estructural opcional (solo después de verificar que no existen ventas v2):
-- DROP INDEX uq_ventas_idempotency_key ON ventas;
-- ALTER TABLE detalle_ventas DROP COLUMN costo_unitario_historico;
-- ALTER TABLE ventas DROP COLUMN firma_version, DROP COLUMN idempotency_key;
