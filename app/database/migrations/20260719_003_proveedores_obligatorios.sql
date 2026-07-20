-- Ejecutar solo después de que la consulta de precondición devuelva cero.
SELECT COUNT(*) AS registros_incompletos
FROM proveedores
WHERE TRIM(nombre) = '' OR TRIM(telefono) = '' OR TRIM(celular) = ''
   OR direccion IS NULL OR TRIM(direccion) = '' OR TRIM(sitio_web) = ''
   OR calificacion_estrellas NOT BETWEEN 1 AND 5 OR activo NOT IN (0, 1);

-- No ejecutar el ALTER si registros_incompletos > 0. Corregirlos manualmente,
-- sin eliminarlos ni inventar datos.
ALTER TABLE proveedores
  MODIFY nombre VARCHAR(255) NOT NULL,
  MODIFY telefono VARCHAR(50) NOT NULL,
  MODIFY celular VARCHAR(50) NOT NULL,
  MODIFY direccion VARCHAR(255) NOT NULL,
  MODIFY sitio_web VARCHAR(255) NOT NULL,
  MODIFY calificacion_estrellas TINYINT NOT NULL,
  MODIFY activo TINYINT(1) NOT NULL;
