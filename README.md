# Sistema de Inventario o Rastro de Partes de Automóviles

Primera fase de adaptación del e-commerce TiendaUTP. Se conservan catálogo, carrito, inventario, ventas, facturas e idiomas, y se incorporan instalación reproducible, seguridad de acceso y administración de usuarios. Marcas, modelos, años compatibles, secciones y comentarios quedan para la siguiente fase.

## Tecnologías y requisitos

- PHP 8.0 o superior; verificado con PHP 8.3.
- MySQL 8 o MariaDB 11.
- Composer 2.
- Extensiones `pdo_mysql`, `mbstring`, `fileinfo`, `curl`, `session` y `openssl`.
- Apache (WampServer es compatible).

## Instalación

```bash
git clone URL_DEL_REPOSITORIO
cd e-comerceDS7
composer install
```

Copie `app/config/config.example.php` como `app/config/config.php`. Ajuste `BASE_URL`, la conexión MySQL y genere una clave HMAC local:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

No publique `config.php`: Git ya lo ignora. Si falta, la aplicación devuelve un mensaje controlado con instrucciones.

Importe `venta_productos.sql` con phpMyAdmin o con el cliente MySQL:

```bash
mysql -u USUARIO -p < venta_productos.sql
```

Si ya tiene una base creada con la versión 1.2, haga una copia de seguridad y ejecute una sola vez `app/database/migration_phase1.sql` en lugar de volver a importar el esquema completo.

Los directorios `public/assets/img/productos` y `public/assets/facturas` están versionados mediante `.gitkeep`. La aplicación también intenta crearlos de forma segura cuando los necesita. Apache debe poder escribir en ellos.

Abra la URL definida en `BASE_URL`.

## Primer administrador

Después de importar la base, ejecute exclusivamente desde terminal:

```bash
php scripts/create_admin.php admin@localhost.test
```

El comando solicita la contraseña sin guardarla en el repositorio, usa `password_hash()`, crea el nombre administrativo `admin` y rechaza correos duplicados. Introduzca la contraseña inicial de prueba requerida por su rúbrica y cámbiela tras el primer acceso. El script solo funciona en CLI y deja de ser reutilizable para el mismo correo.

## Arquitectura

- `public/index.php`: controlador frontal.
- `app/core`: router, conexión PDO y controlador/modelo base.
- `app/controllers`: casos de uso HTTP.
- `app/models`: acceso preparado a MySQL.
- `app/views`: interfaz PHP/HTML.
- `app/services`: contraseñas y firma HMAC.
- `app/helpers`: validación y sanitización.
- `app/lang`: español e inglés.
- `scripts`: utilidades CLI controladas.

## Funciones de esta fase

- Registro y login con contraseñas de 8 a 12 caracteres.
- Sesión regenerada tras login y destruida completamente al salir.
- Bloqueo tras tres fallos consecutivos y auditoría de correo, IP, fecha y resultado.
- Mensajes genéricos para cuentas inexistentes, inactivas o bloqueadas.
- CRUD administrativo de usuarios sin eliminación física.
- Roles iniciales `admin` y `cliente`; protección backend de administración.
- POST y CSRF para operaciones destructivas y cambios de estado.
- Imágenes JPG, PNG y WEBP de hasta 2 MB validadas por MIME real.
- Transacciones y bloqueo `SELECT FOR UPDATE` en ventas.
- Rechazo de ajustes de inventario que producirían cantidades negativas.
- Firma HMAC SHA-256 de ventas y facturación PDF con TCPDF.

## Verificaciones

```bash
composer validate --strict
composer check-platform-reqs
php -l public/index.php
```

Debe ejecutarse `php -l` para todos los PHP. Las pruebas dinámicas requieren una base importada y Apache activo. Compruebe manualmente: tres fallos de login, bloqueo, reactivación administrativa, rechazo de GET/CSRF inválido, carga de imágenes, venta con/sin stock, rollback, PDF e integridad HMAC.

## Próxima fase

- Marcas y modelos de automóviles.
- Años y compatibilidad de partes.
- Secciones o ubicaciones físicas.
- Comentarios y moderación.
- Roles y permisos granulares.
- Miniaturas y pruebas automatizadas ampliadas.

## Correo y restablecimiento de contraseña

En una instalación actualizada, ejecute una sola vez `app/database/migration_fix_proveedores.sql` y después `app/database/migration_password_reset.sql`.

Configure `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `SMTP_SECURE`, `MAIL_FROM_ADDRESS` y `MAIL_FROM_NAME` solamente en `config.php`. `config.example.php` contiene valores ficticios. Con `DEBUG=true` y `SMTP_HOST` vacío, el correo se escribe en `storage/mail` para pruebas locales; en producción el enlace nunca se muestra.

Los enlaces caducan en 30 minutos, se almacenan únicamente como hash SHA-256, invalidan enlaces anteriores y se consumen una sola vez. El administrador inicia el flujo desde el detalle del usuario; la edición administrativa no cambia contraseñas directamente.

Ejecute la prueba dinámica con `php tests/phase2_password_provider.php`.

No se incluyen secretos, datos personales, `vendor/`, PDFs generados ni imágenes cargadas.
