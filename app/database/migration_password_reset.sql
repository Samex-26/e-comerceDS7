USE venta_productos;
CREATE TABLE password_reset_tokens (
  id BIGINT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NOT NULL, token_hash CHAR(64) NOT NULL UNIQUE,
  fecha_expiracion DATETIME NOT NULL, usado TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, used_at DATETIME NULL,
  INDEX idx_reset_usuario_estado (usuario_id, usado, fecha_expiracion),
  CONSTRAINT fk_reset_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE password_reset_audit (
  id BIGINT AUTO_INCREMENT PRIMARY KEY, usuario_id INT NOT NULL, solicitado_por INT NULL,
  evento ENUM('solicitado', 'completado') NOT NULL, ip VARCHAR(45) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reset_audit_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_reset_audit_actor FOREIGN KEY (solicitado_por) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
