-- Ejecutar una sola vez sobre una base creada con la versión 1.2 del proyecto.
USE venta_productos;

ALTER TABLE usuarios
  ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER rol,
  ADD COLUMN intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER activo,
  ADD COLUMN bloqueado TINYINT(1) NOT NULL DEFAULT 0 AFTER intentos_fallidos,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE TABLE intentos_login (
  id_intento BIGINT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  resultado ENUM('exitoso', 'fallido', 'bloqueado', 'inactivo') NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_intentos_email_fecha (email, fecha),
  INDEX idx_intentos_ip_fecha (ip, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE proveedores
  MODIFY nombre VARCHAR(255) NOT NULL,
  MODIFY telefono VARCHAR(50) NOT NULL DEFAULT '',
  MODIFY celular VARCHAR(50) NOT NULL DEFAULT '',
  CHANGE url_web sitio_web VARCHAR(255) NOT NULL DEFAULT '',
  ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '' AFTER celular,
  ADD COLUMN ciudad VARCHAR(100) NOT NULL DEFAULT '' AFTER direccion,
  ADD COLUMN notas TEXT NULL AFTER calificacion_estrellas,
  ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER notas,
  ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER activo,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE proveedores
  DROP CHECK chk_proveedores_estrellas,
  ALTER calificacion_estrellas SET DEFAULT 0,
  ADD CONSTRAINT chk_proveedores_estrellas CHECK (calificacion_estrellas BETWEEN 0 AND 5);

ALTER TABLE inventario
  CHANGE costo_producto costo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER detalle,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
