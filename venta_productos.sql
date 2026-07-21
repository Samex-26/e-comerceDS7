-- =====================================================================
-- Sistema de Venta de Productos - Script de Base de Datos
-- UTP - Facultad de Ingeniería en Sistemas Computacionales
-- Desarrollo de Software VII - Grupo 1GS133
-- Motor: MySQL / MariaDB (compatible con phpMyAdmin / WampServer)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS venta_productos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE venta_productos;

-- ---------------------------------------------------------------------
-- Tabla: idiomas
-- ---------------------------------------------------------------------
CREATE TABLE idiomas (
  id_idioma   INT AUTO_INCREMENT PRIMARY KEY,
  codigo      VARCHAR(5)  NOT NULL UNIQUE,   -- ej. 'es', 'en'
  nombre      VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: usuarios
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
  id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  id_idioma      INT NOT NULL,
  rol            ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_idioma
    FOREIGN KEY (id_idioma) REFERENCES idiomas(id_idioma)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: categorias
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
  id_categoria  INT AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(100) NOT NULL,
  descripcion   TEXT NULL,
  icono         VARCHAR(50) NOT NULL DEFAULT 'category'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: productos
-- ---------------------------------------------------------------------
CREATE TABLE productos (
  id_producto    INT AUTO_INCREMENT PRIMARY KEY,
  nombre         VARCHAR(150) NOT NULL,
  descripcion    TEXT NULL,
  imagen         VARCHAR(255) NULL,
  precio         DECIMAL(10,2) NOT NULL,
  precio_oferta  DECIMAL(10,2) NULL,
  costo          DECIMAL(10,2) NOT NULL,
  cantidad       INT NOT NULL DEFAULT 0,
  id_categoria   INT NOT NULL,
  activo         TINYINT(1) NOT NULL DEFAULT 1,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_categoria
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: proveedores
-- ---------------------------------------------------------------------
CREATE TABLE proveedores (
  id_proveedor            INT AUTO_INCREMENT PRIMARY KEY,
  nombre                  VARCHAR(150) NOT NULL,
  telefono                VARCHAR(20)  NULL,
  celular                 VARCHAR(20)  NULL,
  direccion               VARCHAR(255) NULL,
  url_web                 VARCHAR(255) NULL,
  calificacion_estrellas  TINYINT NOT NULL DEFAULT 5,
  CONSTRAINT chk_proveedores_estrellas
    CHECK (calificacion_estrellas BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: inventario
-- ---------------------------------------------------------------------
CREATE TABLE inventario (
  id_inventario       INT AUTO_INCREMENT PRIMARY KEY,
  id_producto         INT NOT NULL,
  id_proveedor        INT NOT NULL,
  costo_unitario     DECIMAL(10,2) NOT NULL,
  detalle             TEXT NULL,
  fecha_entrada       DATE NOT NULL,
  cantidad_ingresada  INT NOT NULL,
  CONSTRAINT fk_inventario_producto
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_inventario_proveedor
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: ventas
-- ---------------------------------------------------------------------
CREATE TABLE ventas (
  id_venta       INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario     INT NOT NULL,
  fecha          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  total          DECIMAL(10,2) NOT NULL,
  hash_datos     VARCHAR(255) NOT NULL,   -- hash de integridad de la venta + detalle
  firma_digital  TEXT NOT NULL,           -- firma generada por el CriptoServiceInterface
  estado         ENUM('pendiente', 'confirmada', 'anulada') NOT NULL DEFAULT 'confirmada',
  CONSTRAINT fk_ventas_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: detalle_ventas
-- ---------------------------------------------------------------------
CREATE TABLE detalle_ventas (
  id_detalle       INT AUTO_INCREMENT PRIMARY KEY,
  id_venta         INT NOT NULL,
  id_producto      INT NOT NULL,
  cantidad         INT NOT NULL,
  precio_unitario  DECIMAL(10,2) NOT NULL,
  subtotal         DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_detalle_venta
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: facturas
-- ---------------------------------------------------------------------
CREATE TABLE facturas (
  id_factura        INT AUTO_INCREMENT PRIMARY KEY,
  id_venta          INT NOT NULL UNIQUE,
  ruta_pdf          VARCHAR(255) NOT NULL,
  fecha_generacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_facturas_venta
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: visitas
-- ---------------------------------------------------------------------
CREATE TABLE visitas (
  id_visita       INT AUTO_INCREMENT PRIMARY KEY,
  id_producto     INT NULL,
  pagina          VARCHAR(255) NOT NULL,
  id_usuario      INT NULL,
  ip              VARCHAR(45) NULL,
  tiempo_segundos INT NOT NULL DEFAULT 0,
  fecha           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_visitas_producto
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_visitas_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabla: cookies_consentimiento
-- ---------------------------------------------------------------------
CREATE TABLE cookies_consentimiento (
  id_consentimiento  INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario         INT NULL,
  aceptada           TINYINT(1) NOT NULL DEFAULT 0,
  fecha              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cookies_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Índices adicionales de apoyo a reportes/consultas frecuentes
-- ---------------------------------------------------------------------
CREATE INDEX idx_productos_categoria ON productos(id_categoria);
CREATE INDEX idx_inventario_producto ON inventario(id_producto);
CREATE INDEX idx_detalle_venta ON detalle_ventas(id_venta);
CREATE INDEX idx_detalle_producto ON detalle_ventas(id_producto);
CREATE INDEX idx_visitas_producto ON visitas(id_producto);
CREATE INDEX idx_visitas_fecha ON visitas(fecha);

-- ---------------------------------------------------------------------
-- Datos semilla mínimos (necesarios para que el registro de usuarios
-- funcione desde el primer momento: todo usuario requiere id_idioma)
-- ---------------------------------------------------------------------
INSERT INTO idiomas (codigo, nombre) VALUES
  ('es', 'Español'),
  ('en', 'English');

-- Categorías de ejemplo (ajustar/ampliar según el catálogo real)
INSERT INTO categorias (nombre, descripcion) VALUES
  ('Ropa', 'Prendas de vestir en general'),
  ('Electricidad', 'Artículos y componentes eléctricos'),
  ('Hogar', 'Artículos para el hogar'),
  ('Textil', 'Productos textiles');

--- ---------------------------------------------------------------------
-- Datos semilla mínimos (necesarios para que el registro de usuarios
-- funcione desde el primer momento: todo usuario requiere id_idioma)
-- ---------------------------------------------------------------------
INSERT INTO idiomas (codigo, nombre) VALUES
  ('es', 'Español'),
  ('en', 'English');

-- Categorías de ejemplo (ajustar/ampliar según el catálogo real)
INSERT INTO categorias (nombre, descripcion) VALUES
  ('Ropa', 'Prendas de vestir en general'),
  ('Electricidad', 'Artículos y componentes eléctricos'),
  ('Hogar', 'Artículos para el hogar'),
  ('Textil', 'Productos textiles');

-- ---------------------------------------------------------------------
-- Usuarios de prueba (ver README, sección 3 — Matriz de Roles y Credenciales)
-- Contraseñas hasheadas con bcrypt, compatibles con password_verify() de PHP
-- ---------------------------------------------------------------------

-- Administrador
-- Email:      admin@correo.com
-- Contraseña: Admin12345
INSERT INTO usuarios (nombre, email, password_hash, id_idioma, rol) VALUES
  ('Administrador de Prueba', 'admin@correo.com',
   '$2b$12$c676Q03cwQ6ggP77ULeaNO5p2sh4F1Pvgsrzi19oq/BYZb8G.2stG',
   (SELECT id_idioma FROM idiomas WHERE codigo = 'es' LIMIT 1), 'admin');

-- Cliente / Estudiante-Operador
-- Email:      user@correo.com
-- Contraseña: Cliente12345
INSERT INTO usuarios (nombre, email, password_hash, id_idioma, rol) VALUES
  ('Usuario de Prueba', 'user@correo.com',
   '$2b$12$mYPFnIIsi7l/e5vPk26dguiS0pb5dHcl971Hneq54OCy9811ZFeZy',
   (SELECT id_idioma FROM idiomas WHERE codigo = 'es' LIMIT 1), 'cliente');