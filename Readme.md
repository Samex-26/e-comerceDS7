# e-comerceDS7 — Sistema de Venta de Productos con Carrito de Compras

Prototipo de sistema de e-commerce desarrollado como examen final de **Desarrollo de Software VII**, Universidad Tecnológica de Panamá — Facultad de Ingeniería en Sistemas Computacionales, Grupo 1GS133.

## Video demostrativo

> [Enlace al video explicativo del proyecto — agregar aquí]

## Enlaces del proyecto

- **Repositorio:** [URL de este repositorio de GitHub]
- **Backup de la base de datos:** [URL del archivo `.sql` de respaldo]

## Integrantes

- Samuel Ojo
- Anacelis Boniche
- Daniel Morales
- Diego Vasquez 

## Descripción

e-comerceDS7 es un sistema web de venta de productos construido desde cero con arquitectura **MVC en PHP puro** (sin frameworks como Laravel o Symfony), diseñado para demostrar comprensión práctica del patrón Modelo-Vista-Controlador, principios SOLID/DRY, recomendaciones OWASP, y un patrón de contratos (interfaces) para operaciones criptográficas.

El sistema incluye catálogo público multi-idioma, carrito de compras con ventas firmadas digitalmente (integridad verificable), generación de facturas en PDF, panel administrativo con gestión de productos/categorías/proveedores/inventario, tracking de visitas, dashboard de métricas y reportes.

## Tecnologías

- **Backend:** PHP 8+, orientado a objetos, PDO (prepared statements)
- **Base de datos:** MySQL / MariaDB (InnoDB, utf8mb4)
- **PDF:** TCPDF 6.11 (vía Composer)
- **Frontend:** Bootstrap 5, JavaScript vanilla, Material Symbols, Chart.js (solo en el Dashboard)
- **Diseño:** Sistema de diseño propio ("Academic Commerce System") prototipado en Google Stitch
- **Entorno local:** WampServer (Apache + MySQL + PHP)

## Características principales

- Multi-idioma (ES/EN) con saludo dinámico según preferencia del usuario.
- Catálogo de productos por categoría, con precios de oferta y control de stock.
- Aviso de cookies con consentimiento.
- CRUD de Productos, Categorías, Proveedores (con calificación por estrellas) e Inventario, con sincronización automática de stock.
- Autenticación con contraseñas hasheadas (`PasswordHasherService`), roles `admin`/`cliente`, y protección CSRF.
- Carrito de compras y confirmación de venta con **firma digital de integridad** (`FirmaDigitalService`) — cualquier alteración posterior a los datos de una venta es detectable.
- Generación de factura descargable en PDF (TCPDF), con el hash/firma de la venta impreso como respaldo de integridad.
- Tracking de tiempo de permanencia por página/producto (`navigator.sendBeacon`), alimentando dashboard y reportes.
- Dashboard administrativo con KPIs (ventas del mes, ganancia neta, productos vendidos, visitantes) y gráficas.
- Reportes de ventas vs. costos por rango de fechas, top 5 productos más vendidos, top 10 más/menos visitados.
- Página pública de mercadeo (landing) y página de contacto.
- Patrón de contratos (`CriptoServiceInterface`) que unifica el hashing de contraseñas y la firma digital de ventas bajo el mismo mecanismo de abstracción.

## Arquitectura

```
/app
  /config       → configuración de entorno (config.php, NO se sube a git)
  /core         → Database (PDO), Router, Controller base, Model base
  /controllers  → un controlador por módulo/flujo
  /models       → un modelo por entidad de base de datos
  /views        → vistas organizadas por módulo + layouts compartidos
  /helpers      → Sanitizer, Validator
  /contracts    → CriptoServiceInterface
  /services     → PasswordHasherService, FirmaDigitalService
  /lang         → es.php, en.php
/public
  /assets       → css, js, imágenes de productos, facturas PDF generadas
  index.php     → front controller (único punto de entrada)
  .htaccess     → reescritura de URLs
```

Cada entidad de base de datos tiene su propio modelo; los controladores se organizan por flujo de interacción del usuario, no necesariamente 1:1 con los modelos.

## Instalación local (WampServer)

1. Clona el repositorio dentro de `C:\wamp64\www\` (la carpeta del proyecto debe llamarse `e-comerceDS7`, o ajusta `BASE_URL` si usas otro nombre).
2. Instala las dependencias de Composer (TCPDF):
   ```bash
   composer install
   ```
3. Copia `app/config/config.example.php` a `app/config/config.php` y completa tus credenciales locales de MySQL y una clave secreta propia para `CLAVE_FIRMA_DIGITAL`.
4. Importa el script de base de datos en phpMyAdmin (crea la base `venta_productos` con todas las tablas, relaciones y semillas iniciales de idiomas/categorías):
   ```
   venta_productos.sql
   ```
5. Verifica en WAMP que el módulo `rewrite_module` de Apache esté activo y que `AllowOverride` esté en `All` para que el `.htaccess` funcione.
6. Inicia los servicios de WampServer y entra a:
   ```
   http://localhost/e-comerceDS7/public/
   ```

## Acceso como administrador

Por diseño, el registro público **no permite** seleccionar el rol — todo usuario nuevo se crea como `cliente` (evita que cualquier visitante se autoasigne privilegios de administrador). Para crear tu primer usuario admin:

1. Regístrate normalmente desde el sitio.
2. Promueve tu usuario directamente en la base de datos:
   ```sql
   UPDATE usuarios SET rol = 'admin' WHERE email = 'tu_correo@ejemplo.com';
   ```
3. Inicia sesión de nuevo — el menú "Admin" aparecerá en la navbar con acceso a Dashboard, Productos, Categorías, Proveedores, Inventario, Ventas y Reportes.

## Seguridad — buenas prácticas aplicadas

- Prepared statements (PDO) en el 100% de las consultas.
- Contraseñas hasheadas, nunca almacenadas ni registradas en texto plano.
- Protección CSRF en formularios de autenticación y checkout.
- Sanitización y validación centralizadas (`Sanitizer`, `Validator`) para toda entrada de usuario.
- Totales de venta recalculados siempre en el servidor, nunca confiados desde el cliente.
- Cada venta confirmada queda firmada digitalmente; la integridad es verificable en cualquier momento posterior.

## Licencia

Proyecto académico semestral desarrollado para la Universidad Tecnológica de Panamá. Uso educativo.