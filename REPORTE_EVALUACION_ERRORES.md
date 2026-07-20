# REPORTE DE EVALUACIÓN DE ERRORES

## 1. Alcance y método

Auditoría inmediata, funcional, técnica y de seguridad realizada el 19 de julio de 2026 exclusivamente sobre los 21 puntos solicitados. Se revisaron código PHP/JavaScript/CSS, esquemas SQL, estructura de directorios, dependencias, base de datos local activa y respuestas HTTP de lectura. No se crearon, modificaron ni eliminaron registros; no se ejecutaron compras; no se hicieron ataques destructivos.

Estados usados:

- **Confirmado:** existe evidencia directa en código, esquema, filesystem, respuesta HTTP o consulta de solo lectura.
- **Probable:** el flujo vulnerable está demostrado por código, pero no se reprodujo contra datos reales para evitar alterarlos.
- **No reproducido:** el control fue revisado y no se obtuvo evidencia suficiente de fallo.

Resumen: **4 críticos, 10 altos, 10 medios y 3 bajos**, además de controles informativos sin fallo confirmado. La base local contenía 4 productos, 3 ventas, 1 factura y 85 visitas al momento de la consulta; solo se usaron conteos. Una de las tres ventas no superó la verificación de integridad reconstruida.

## 2. Hallazgos de código y diseño

### ERR-001 — Facturas descargables sin autenticación por ruta pública predecible

- **Módulo:** Facturación PDF / control de acceso
- **Nivel de peligro:** CRÍTICO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/FacturaController.php:37-38`, `app/controllers/FacturaController.php:190-207`
- **Pasos para reproducirlo:** 1) Cerrar sesión o usar un cliente HTTP sin cookies. 2) Solicitar `GET /public/assets/facturas/factura_2.pdf`.
- **Datos introducidos:** identificador de prueba `2`, deducible por secuencia.
- **Resultado esperado:** 401/403 o entrega únicamente después de comprobar propietario/rol.
- **Resultado obtenido:** HTTP 200, `Content-Type: application/pdf`, 8.351 bytes, sin sesión.
- **Riesgo:** exposición de identidad, correo, productos, importes, hash y firma de ventas de otros clientes (IDOR).
- **Evidencia:** el controlador guarda en `public/assets/facturas/`; existe `factura_2.pdf` y Apache la sirvió anónimamente. La autorización de `generar()` se omite al acceder al archivo estático.
- **Recomendación:** almacenar facturas fuera del document root y servirlas solo mediante un controlador autorizado; usar identificadores no predecibles como defensa adicional.
- **Prioridad de corrección:** P0 — inmediata.

### ERR-002 — Manipulación anónima de métricas y permanencia (IDOR)

- **Módulo:** Visitas, dashboard y reportes
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/VisitaController.php:5-26`, `app/controllers/VisitaController.php:29-48`, `app/models/VisitaModel.php:20-29`
- **Pasos para reproducirlo:** enviar POST repetidos a `visita/registrar`; luego enviar a `visita/actualizarTiempo` un `id_visita` ajeno y un valor entre 0 y 86400.
- **Datos introducidos:** `pagina=/producto`, `id_producto=1`; `id_visita=<otro ID>`, `tiempo_segundos=86400` (prueba descrita, no ejecutada para no contaminar métricas).
- **Resultado esperado:** consentimiento previo, CSRF o token ligado a la visita, límites de frecuencia y comprobación de pertenencia.
- **Resultado obtenido:** los endpoints no exigen sesión, consentimiento, CSRF ni relación entre visita y solicitante; el UPDATE usa solo el ID.
- **Riesgo:** cualquier tercero puede inflar o alterar visitas, permanencia, top 10 y KPI administrativos.
- **Evidencia:** controladores y UPDATE citados; la base activa contiene 85 filas de visitas, pero no se alteraron.
- **Recomendación:** emitir un token aleatorio por visita, comprobarlo al actualizar, exigir consentimiento, aplicar rate limiting y validar producto/página contra listas permitidas.
- **Prioridad de corrección:** P0.

### ERR-003 — Operaciones destructivas administrativas por GET y sin CSRF

- **Módulo:** CRUD de productos, categorías, proveedores, inventario y usuarios
- **Nivel de peligro:** CRÍTICO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/ProductoController.php:317-324`, `CategoriaController.php:176-190`, `ProveedorController.php:102-120`, `InventarioController.php:105-117`, `UsuarioController.php:160-175`
- **Pasos para reproducirlo:** con un administrador autenticado, visitar o incrustar una URL como `/producto/eliminar/1` desde otro origen.
- **Datos introducidos:** ID de prueba `1` (no se ejecutó para no modificar datos).
- **Resultado esperado:** método POST/DELETE y token CSRF válido antes de cualquier cambio.
- **Resultado obtenido:** las acciones aceptan GET y ejecutan el cambio sin token; las vistas generan enlaces `<a>` directos.
- **Riesgo:** desactivación/eliminación involuntaria o inducida de datos administrativos.
- **Evidencia:** ausencia de validación de método y CSRF en todas las acciones citadas; enlaces en `app/views/*/admin_listado.php`.
- **Recomendación:** aceptar únicamente POST, validar CSRF y devolver 405 para otros métodos.
- **Prioridad de corrección:** P0.

### ERR-004 — CRUD de productos incompatible con SKU obligatorio y único

- **Módulo:** Catálogo / inventario / base de datos
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/models/Producto.php:37-53`, `app/controllers/ProductoController.php:84-145`; base activa `productos.sku VARCHAR(80) NOT NULL UNIQUE`
- **Pasos para reproducirlo:** abrir el alta de producto y guardar un producto válido; observar que no existe campo SKU ni parámetro SQL.
- **Datos introducidos:** nombre `AUDIT-PRODUCTO`, precio `10`, costo `5`, cantidad `1`, categoría existente.
- **Resultado esperado:** solicitar SKU, rechazar vacío/duplicado y guardar uno único.
- **Resultado obtenido:** el formulario/controlador/modelo omiten SKU; contra la base activa el INSERT no puede satisfacer la columna obligatoria.
- **Riesgo:** alta de productos inutilizable y errores PDO; no existe control de SKU vacío o duplicado.
- **Evidencia:** `SHOW COLUMNS` de solo lectura confirmó `sku NOT NULL UNIQUE`; el SQL INSERT citado no lo contiene.
- **Recomendación:** alinear formulario, entidad y migración; validar formato, longitud y unicidad con manejo de carrera/violación UNIQUE.
- **Prioridad de corrección:** P0.

### ERR-005 — Validación insuficiente en el formulario principal de productos

- **Módulo:** CRUD de productos
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/ProductoController.php:76-145`, especialmente `94-102`; `app/helpers/Sanitizer.php:17-25`
- **Pasos para reproducirlo:** POST a `producto/admin` con token válido y valores límite.
- **Datos introducidos:** `costo=-100`, `cantidad=-5` o `1.9`, `precio_oferta=999`, `precio=10`, `id_categoria=999999`.
- **Resultado esperado:** rechazo servidor de costo/stock negativos, decimales o excesivos, oferta mayor al precio y categoría inexistente.
- **Resultado obtenido:** el flujo principal solo valida nombre, precio e ID positivo; `Sanitizer::entero()` convierte `1.9` en `1`; no verifica existencia de categoría ni límites máximos.
- **Riesgo:** catálogo inconsistente, márgenes falsos, stock inválido y excepciones PDO por FK.
- **Evidencia:** las validaciones más completas de `procesarCrear()` no se aplican a `procesarGuardarProducto()`, que es el action del listado actual.
- **Recomendación:** una única rutina de validación previa a sanitización destructiva; `FILTER_VALIDATE_INT`, cotas de negocio, costo no negativo, oferta menor o igual al precio y consulta de existencia.
- **Prioridad de corrección:** P0.

### ERR-006 — Carga de imágenes no operativa y validación MIME confiada al cliente

- **Módulo:** Imágenes de productos / thumbnails
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/ProductoController.php:329-356`
- **Pasos para reproducirlo:** subir una imagen JPG válida desde el formulario de producto.
- **Datos introducidos:** archivo de prueba JPEG menor de 2 MB.
- **Resultado esperado:** crear/validar directorio escribible, comprobar contenido real y guardar thumbnail.
- **Resultado obtenido:** no existe `public/assets/img/productos`; el método no la crea y `move_uploaded_file()` falla. Además confía en `$_FILES['type']` y conserva la extensión aportada por el usuario.
- **Riesgo:** imágenes que no pueden guardarse; posible carga de contenido poliglota o con extensión engañosa si se crea la carpeta.
- **Evidencia:** comprobación de filesystem: directorio inexistente; líneas 335-352.
- **Recomendación:** crear directorio durante despliegue con permisos mínimos; verificar `finfo`/decodificación real, generar extensión propia y thumbnail, y almacenar fuera del root cuando sea posible.
- **Prioridad de corrección:** P0.

### ERR-007 — Cambio de idioma roto e inconsistente

- **Módulo:** Idioma y saludo
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/IdiomaController.php:1-2`, `app/views/auth/login.php:1-4`, `app/views/auth/registro.php:1-3`, `app/helpers/IdiomaHelper.php:18-24`
- **Pasos para reproducirlo:** solicitar `GET /idioma/cambiar/es`.
- **Datos introducidos:** `es`.
- **Resultado esperado:** cambiar preferencia, mantenerla y mostrar saludo/traducciones en español.
- **Resultado obtenido:** HTTP 404; `IdiomaController.php` solo contiene un comentario y no define la clase. Login acepta cualquier `lang`, registro solo es/en y el cambio se ejecuta dentro de vistas después de cargar traducciones.
- **Riesgo:** requisito funcional principal incumplido y estado de idioma impredecible.
- **Evidencia:** respuesta HTTP 404 y archivo de 34 bytes.
- **Recomendación:** implementar un único endpoint con allowlist `es/en`, persistencia para usuario autenticado y redirección segura; no mutar sesión desde vistas.
- **Prioridad de corrección:** P0.

### ERR-008 — Consentimiento de cookies no gobierna el tracking y tiene dos implementaciones incompatibles

- **Módulo:** Política de cookies / métricas
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/views/producto/catalogo.php:150-195`, `app/views/producto/detalle.php:199-259`, `app/views/inicio/index.php:222-246`
- **Pasos para reproducirlo:** abrir landing o catálogo en una sesión nueva sin aceptar el aviso; observar solicitudes de tracking. Aceptar en catálogo y abrir detalle.
- **Datos introducidos:** ninguna preferencia previa.
- **Resultado esperado:** no registrar visitas hasta consentimiento; una preferencia coherente y revocable.
- **Resultado obtenido:** el tracking se ejecuta independientemente del consentimiento. Catálogo usa `localStorage.cookieConsent`; detalle usa `cookies_aceptadas`; landing rastrea sin aviso. La tabla `cookies_consentimiento` no tiene código consumidor.
- **Riesgo:** métricas obtenidas sin aceptación demostrable e incumplimiento de la política declarada.
- **Evidencia:** JavaScript citado y ausencia de referencias de aplicación a `cookies_consentimiento`.
- **Recomendación:** gestor único de consentimiento, categorías necesarias/analíticas, bloqueo previo, revocación y registro auditable.
- **Prioridad de corrección:** P0.

### ERR-009 — Cookies de sesión y consentimiento sin atributos de seguridad

- **Módulo:** Sesiones / cookies
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `public/index.php:31-34`, `app/views/producto/detalle.php:226-228`
- **Pasos para reproducirlo:** solicitar cualquier página y revisar `Set-Cookie`; aceptar cookies en detalle y revisar `document.cookie`.
- **Datos introducidos:** petición HTTP local.
- **Resultado esperado:** `HttpOnly`, `SameSite=Lax/Strict`, `Secure` bajo HTTPS y alcance de path correcto.
- **Resultado obtenido:** `Set-Cookie: PHPSESSID=...; path=/`, sin los tres atributos; la cookie de consentimiento tampoco define `SameSite` ni `Secure`.
- **Riesgo:** mayor exposición ante XSS, CSRF y transporte sin cifrar.
- **Evidencia:** cabeceras HTTP observadas y asignación JavaScript citada.
- **Recomendación:** configurar parámetros de sesión antes de `session_start()`, desplegar con HTTPS y definir atributos explícitos.
- **Prioridad de corrección:** P0.

### ERR-010 — Sesión no se regenera después del login

- **Módulo:** Autenticación
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/AuthController.php:164-176`
- **Pasos para reproducirlo:** obtener PHPSESSID anónimo, iniciar sesión y comparar el ID.
- **Datos introducidos:** credenciales de una cuenta de prueba (reproducción activa no realizada).
- **Resultado esperado:** `session_regenerate_id(true)` al autenticar y al cambiar privilegios.
- **Resultado obtenido:** el código conserva la sesión existente y solo agrega atributos de usuario.
- **Riesgo:** fijación de sesión y secuestro posterior de una sesión autenticada.
- **Evidencia:** búsqueda completa sin llamadas a `session_regenerate_id`.
- **Recomendación:** regenerar ID tras autenticación y rotar tokens CSRF.
- **Prioridad de corrección:** P1.

### ERR-011 — Carrito y checkout permitidos a administradores

- **Módulo:** Autorización por rol / carrito
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/CarritoController.php:10-17`, `app/controllers/VentaController.php:5-16`
- **Pasos para reproducirlo:** iniciar sesión como admin y abrir `/carrito/ver` o `/venta/checkout` con carrito.
- **Datos introducidos:** sesión con `rol=admin`.
- **Resultado esperado:** denegar flujo comercial al rol admin según requisito.
- **Resultado obtenido:** solo se comprueba `id_usuario`; no se restringe rol.
- **Riesgo:** mezcla de funciones administrativas y de cliente, ventas internas accidentales.
- **Evidencia:** funciones `requiereSesion()` citadas.
- **Recomendación:** guard específico `requiereCliente()` y matriz centralizada de permisos.
- **Prioridad de corrección:** P1.

### ERR-012 — Falta de idempotencia: checkout puede producir ventas duplicadas

- **Módulo:** Ventas / checkout
- **Nivel de peligro:** CRÍTICO
- **Estado:** probable
- **Archivo y línea:** `app/controllers/VentaController.php:74-153`, `app/models/VentaModel.php:5-65`
- **Pasos para reproducirlo:** enviar dos POST concurrentes idénticos con el mismo token CSRF antes de que la primera respuesta vacíe la sesión.
- **Datos introducidos:** mismo carrito y mismo `csrf_token` en ambas solicitudes (no ejecutado para no crear ventas).
- **Resultado esperado:** una sola venta mediante clave idempotente de un uso.
- **Resultado obtenido:** el token CSRF es reutilizable y no existe clave/constraint de idempotencia; cada transacción puede insertar una venta si queda stock.
- **Riesgo:** doble cargo/venta y doble descuento de stock.
- **Evidencia:** ausencia de consumo/rotación del token y de identificador único de checkout.
- **Recomendación:** nonce de checkout de un uso con índice UNIQUE y estado transaccional; deshabilitar doble envío solo como apoyo visual.
- **Prioridad de corrección:** P0.

### ERR-013 — Una venta existente ya falla la verificación de integridad

- **Módulo:** Firma digital de ventas
- **Nivel de peligro:** CRÍTICO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/VentaController.php:127-150`, `app/controllers/VentaController.php:194-212`, `app/models/VentaModel.php:23-33`
- **Pasos para reproducirlo:** reconstruir el JSON exactamente como `verificarIntegridad()` para todas las ventas y comparar SHA-256/HMAC.
- **Datos introducidos:** tres ventas existentes, leídas sin mostrar datos personales.
- **Resultado esperado:** 3 válidas, 0 inválidas si no hubo alteración.
- **Resultado obtenido:** 2 válidas y 1 inválida.
- **Riesgo:** no se puede distinguir de forma fiable una alteración real de una divergencia de serialización/fecha; pérdida de confianza probatoria.
- **Evidencia:** auditoría de solo lectura: `sales_total=3 valid=2 invalid=1`. La cadena se firma con `$fecha` de PHP, pero el INSERT no guarda esa fecha y usa el timestamp de BD; el detalle tampoco tiene orden explícito al reconstruirse.
- **Recomendación:** persistir canónicamente la carga firmada o firmar después del INSERT con la fecha leída de BD; normalizar decimales y ordenar detalles; versionar el formato de firma.
- **Prioridad de corrección:** P0.

### ERR-014 — Inventario acepta fechas imposibles y no valida entidades existentes

- **Módulo:** Inventario CRUD
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/InventarioController.php:47-71`, `app/models/InventarioModel.php:30-62`
- **Pasos para reproducirlo:** POST con token válido, IDs positivos inexistentes o fecha semánticamente inválida.
- **Datos introducidos:** `fecha_entrada=2026-02-31`, `id_producto=999999`, `id_proveedor=999999`, `cantidad_ingresada=1`.
- **Resultado esperado:** errores de validación claros antes de SQL.
- **Resultado obtenido:** solo se comprueba regex de fecha e IDs positivos; la aplicación llega a la BD y depende de coerción/constraints.
- **Riesgo:** fecha transformada/rechazada de forma dependiente del modo SQL y excepción PDO por FK.
- **Evidencia:** no se usa `checkdate()` ni búsquedas de producto/proveedor.
- **Recomendación:** validar fecha con `DateTime::createFromFormat` más errores, existencia/estado de FK y fecha de negocio permitida.
- **Prioridad de corrección:** P1.

### ERR-015 — Edición/eliminación de entradas puede ocultar faltantes reales de stock

- **Módulo:** Inventario / consistencia
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/models/InventarioModel.php:65-126`, `app/models/InventarioModel.php:129-154`
- **Pasos para reproducirlo:** vender parte de una entrada y luego reducirla o eliminarla desde administración.
- **Datos introducidos:** entrada 10, stock actual 2, editar entrada a 1 o eliminarla (no ejecutado).
- **Resultado esperado:** impedir la reversión si deja inventario lógico negativo o registrar ajustes trazables.
- **Resultado obtenido:** `GREATEST(0, cantidad - ...)` fuerza cero y oculta la diferencia; se pierde trazabilidad del faltante.
- **Riesgo:** inventario y costo quedan materialmente incorrectos.
- **Evidencia:** SQL citado.
- **Recomendación:** movimientos inmutables/compensatorios, kardex y validación transaccional contra stock comprometido.
- **Prioridad de corrección:** P1.

### ERR-016 — “10 menos vistos” excluye productos con cero visitas

- **Módulo:** Reportes de visitas
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/models/VisitaModel.php:64-89`
- **Pasos para reproducirlo:** tener un producto activo nunca visitado y abrir reportes.
- **Datos introducidos:** producto de prueba con cero visitas (análisis SQL, no creado).
- **Resultado esperado:** aparecer primero con 0 visitas.
- **Resultado obtenido:** la consulta parte de `visitas` con INNER JOIN y exige `HAVING COUNT(...) > 0`.
- **Riesgo:** reporte sesgado; no identifica productos sin exposición.
- **Evidencia:** líneas 66-83.
- **Recomendación:** partir de productos con LEFT JOIN filtrado por fechas en la condición de unión y ordenar `COUNT(v.id_visita)` ascendente.
- **Prioridad de corrección:** P1.

### ERR-017 — Ganancias históricas se recalculan con el costo actual del producto

- **Módulo:** Ventas frente a costos / KPI de ganancias
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/models/VentaModel.php:108-120`, `app/models/VentaModel.php:187-204`
- **Pasos para reproducirlo:** registrar una venta, cambiar luego `productos.costo` y volver a consultar el mismo período.
- **Datos introducidos:** costo original 5, costo posterior 9 (prueba no ejecutada).
- **Resultado esperado:** la ganancia histórica permanece basada en el costo al vender.
- **Resultado obtenido:** las consultas usan `p.costo` actual, no un costo congelado en `detalle_ventas`.
- **Riesgo:** reportes y KPI cambian retroactivamente y pueden ser contablemente falsos.
- **Evidencia:** JOIN a productos y fórmulas citadas; `detalle_ventas` no tiene costo unitario histórico.
- **Recomendación:** guardar costo unitario en cada detalle de venta y usarlo en reportes.
- **Prioridad de corrección:** P1.

### ERR-018 — KPI de visitantes compara períodos incompatibles

- **Módulo:** Dashboard
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/DashboardController.php:13-32`, `app/models/VisitaModel.php:32-35`
- **Pasos para reproducirlo:** abrir dashboard con visitas acumuladas de varios meses.
- **Datos introducidos:** datos existentes.
- **Resultado esperado:** visitas del mes actual frente a visitas del mes anterior, idealmente visitantes únicos.
- **Resultado obtenido:** `$kpiVisitantes` cuenta todas las visitas históricas y se compara contra solo el mes anterior; además son eventos, no visitantes únicos.
- **Riesgo:** porcentaje y KPI engañosos.
- **Evidencia:** `totalVisitas()` no filtra fecha ni deduplica.
- **Recomendación:** definir KPI, filtrar ambos períodos iguales y deduplicar con identificador consentido/usuario.
- **Prioridad de corrección:** P1.

### ERR-019 — Manipulación del carrito por CSRF en acciones GET

- **Módulo:** Carrito
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/CarritoController.php:19-65`, `app/controllers/CarritoController.php:112-122`, `app/views/carrito/ver.php:82`
- **Pasos para reproducirlo:** con cliente autenticado, cargar desde otro sitio una imagen/enlace a `/carrito/agregar/1` o `/carrito/eliminar/1`.
- **Datos introducidos:** ID de producto existente.
- **Resultado esperado:** POST con CSRF y origen válido.
- **Resultado obtenido:** las acciones mutan la sesión mediante GET y sin token.
- **Riesgo:** carrito modificado contra la intención del usuario.
- **Evidencia:** métodos citados.
- **Recomendación:** POST + CSRF para agregar/eliminar/actualizar y 405 para GET.
- **Prioridad de corrección:** P1.

### ERR-020 — Falta thumbnail real en inventario

- **Módulo:** Inventario / imágenes thumbnail
- **Nivel de peligro:** BAJO
- **Estado:** confirmado
- **Archivo y línea:** `app/views/inventario/admin_listado.php:108-116`, `app/models/InventarioModel.php:7-13`
- **Pasos para reproducirlo:** abrir inventario con productos que tengan imagen.
- **Datos introducidos:** ninguno.
- **Resultado esperado:** miniatura de la imagen del producto.
- **Resultado obtenido:** siempre se muestra el icono `inventory_2`; la consulta ni siquiera selecciona explícitamente la imagen (solo `i.*` y nombres).
- **Riesgo:** requisito visual incumplido y menor capacidad de identificación.
- **Evidencia:** marcado estático citado.
- **Recomendación:** incluir ruta de imagen validada y un thumbnail generado, con placeholder de respaldo.
- **Prioridad de corrección:** P2.

### ERR-021 — Superficie de información del servidor y recursos externos sin endurecimiento

- **Módulo:** Cabeceras / CSS y JavaScript integrado
- **Nivel de peligro:** BAJO
- **Estado:** confirmado
- **Archivo y línea:** configuración Apache/PHP local; `app/views/layouts/header.php`, `app/views/layouts/footer.php`, `app/views/dashboard/index.php:220`
- **Pasos para reproducirlo:** solicitar la raíz e inspeccionar cabeceras.
- **Datos introducidos:** GET anónimo.
- **Resultado esperado:** ocultar versiones y usar CSP/SRI cuando aplique.
- **Resultado obtenido:** se publican `Server: Apache/2.4.65 ... PHP/8.3.28` y `X-Powered-By`; no se observaron CSP, X-Content-Type-Options, Referrer-Policy ni frame protection. Bootstrap/Chart.js se cargan desde CDN sin evidencia de SRI.
- **Riesgo:** facilita fingerprinting y reduce defensa en profundidad.
- **Evidencia:** cabeceras HTTP capturadas.
- **Recomendación:** ocultar versiones, definir cabeceras y fijar recursos con SRI o servirlos localmente.
- **Prioridad de corrección:** P2.

### ERR-022 — Ausencia de límite superior coherente para stock y costos

- **Módulo:** Productos e inventario
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/controllers/ProductoController.php:84-102`, `app/controllers/InventarioController.php:47-65`
- **Pasos para reproducirlo:** enviar enteros muy altos dentro/fuera del rango de MySQL y costos superiores a DECIMAL(10,2).
- **Datos introducidos:** `cantidad=2147483647`, `cantidad_ingresada=2147483647`, `costo_unitario=99999999999`.
- **Resultado esperado:** límites de negocio explícitos y suma segura.
- **Resultado obtenido:** no existe máximo; la suma de inventario puede desbordar INT y el costo puede desbordar DECIMAL.
- **Riesgo:** excepción PDO, stock corrupto o denegación funcional.
- **Evidencia:** solo se comprueba positividad/no negatividad.
- **Recomendación:** definir máximos, verificar suma antes del UPDATE y manejar excepciones sin detalle interno.
- **Prioridad de corrección:** P1.

### ERR-023 — Esquemas SQL del repositorio no representan una fuente de verdad única

- **Módulo:** Base de datos / despliegue
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `venta_productos.sql:51-66`, `venta_productos.sql:71-100`, `app/database/schema_prompt6.sql:1-31`, modelos `Producto.php:37-53`, `ProveedorModel.php:18-34`, `InventarioModel.php:30-45`
- **Pasos para reproducirlo:** comparar el script principal, el schema prompt6, los modelos y `SHOW COLUMNS` de la base activa.
- **Datos introducidos:** ninguno.
- **Resultado esperado:** una migración reproducible alineada con el código.
- **Resultado obtenido:** el principal no tiene SKU y usa `url_web`/`costo_producto`; prompt6 usa `sitio_web`/`costo_unitario`; la base activa agrega SKU y más columnas. Ningún artefacto por sí solo reproduce claramente el estado actual.
- **Riesgo:** instalaciones distintas, CRUD roto, errores de columna y datos incompatibles.
- **Evidencia:** comparación estática y metadatos de la base activa.
- **Recomendación:** migraciones versionadas e idempotentes, retirar/archivar schemas contradictorios y comprobarlos en CI desde una BD vacía.
- **Prioridad de corrección:** P0.

## 3. Problemas de configuración local (separados del código)

### CFG-001 — DEBUG habilitado en el entorno local

- **Módulo:** Configuración PHP/aplicación
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** `app/config/config.php` (constante `DEBUG=true`); `app/core/Router.php:71-83`
- **Pasos para reproducirlo:** solicitar una ruta inexistente.
- **Datos introducidos:** `/controlador-inexistente/accion`.
- **Resultado esperado:** 404 genérico sin nombres internos.
- **Resultado obtenido:** el router está diseñado para añadir detalles de controlador/acción cuando DEBUG está activo. En las rutas normales probadas no apareció Call Stack.
- **Riesgo:** divulgación de estructura interna; una excepción no controlada puede depender además de `display_errors` de PHP.
- **Evidencia:** configuración y rama DEBUG citadas.
- **Recomendación:** `DEBUG=false` y `display_errors=Off` fuera de desarrollo; logging protegido.
- **Prioridad de corrección:** P1 antes de publicar.

### CFG-002 — Sitio servido por HTTP, no HTTPS

- **Módulo:** Transporte y cookies
- **Nivel de peligro:** ALTO
- **Estado:** confirmado
- **Archivo y línea:** `app/config/config.php` (`BASE_URL` con `http://`); respuesta HTTP local
- **Pasos para reproducirlo:** abrir la URL configurada.
- **Datos introducidos:** ninguno.
- **Resultado esperado:** HTTPS para credenciales, sesión, checkout y factura.
- **Resultado obtenido:** HTTP local sin posibilidad efectiva de cookie `Secure`.
- **Riesgo:** credenciales y sesiones interceptables si se expone fuera de loopback/red confiable.
- **Evidencia:** BASE_URL y cabeceras observadas.
- **Recomendación:** TLS en cualquier entorno compartido/productivo y redirección obligatoria.
- **Prioridad de corrección:** P0 antes de despliegue.

### CFG-003 — Carpeta de productos ausente

- **Módulo:** Filesystem
- **Nivel de peligro:** MEDIO
- **Estado:** confirmado
- **Archivo y línea:** ruta esperada por `ProductoController.php:350`
- **Pasos para reproducirlo:** comprobar `public/assets/img/productos`.
- **Datos introducidos:** ninguno.
- **Resultado esperado:** directorio existente y escribible por la cuenta de Apache.
- **Resultado obtenido:** no existe. `public/assets/facturas` sí existe.
- **Riesgo:** todas las cargas de imágenes fallan.
- **Evidencia:** `Test-Path` devolvió falso.
- **Recomendación:** incluir creación/verificación en instalación y health check; permisos mínimos.
- **Prioridad de corrección:** P0.

### CFG-004 — Artefactos sensibles de correo de prueba en storage local

- **Módulo:** Recuperación de contraseña / almacenamiento
- **Nivel de peligro:** BAJO
- **Estado:** confirmado
- **Archivo y línea:** `storage/mail/password-reset-*.html` (archivos no versionados)
- **Pasos para reproducirlo:** listar `storage/mail` desde el filesystem local.
- **Datos introducidos:** ninguno.
- **Resultado esperado:** tokens efímeros protegidos, expirados y eliminados; storage fuera de cualquier publicación web.
- **Resultado obtenido:** múltiples correos de restablecimiento persisten localmente. No se verificó ni expuso su contenido.
- **Riesgo:** quien obtenga acceso al filesystem podría recuperar enlaces/tokens aún válidos.
- **Evidencia:** nombres de archivos observados; `storage/` está fuera de `public`, lo cual reduce exposición web directa.
- **Recomendación:** TTL, limpieza automática, permisos restrictivos y tokens de un solo uso.
- **Prioridad de corrección:** P2.

## 4. Controles revisados sin fallo confirmado

- **SQL Injection — no reproducido / informativo:** las consultas revisadas usan PDO preparado y parámetros; los fragmentos SQL dinámicos observados son listas internas, no texto del usuario. Mantener pruebas automatizadas.
- **XSS almacenado/reflejado — no reproducido / informativo:** las salidas de producto, proveedor, usuario, carrito e inventario revisadas usan `htmlspecialchars`; Chart.js recibe `json_encode`. `Sanitizer::html()` permite atributos HTML, por lo que debe evitarse imprimir su resultado sin escape en cambios futuros.
- **Stock negativo por compras simultáneas — no reproducido / informativo:** `VentaModel::crear()` usa transacción y `SELECT ... FOR UPDATE`, una defensa adecuada. Falta idempotencia (ERR-012), pero no se confirmó stock negativo concurrente.
- **Manipulación de precios desde cliente — no reproducido / informativo:** checkout vuelve a consultar precio y stock en servidor. La cantidad puede ser manipulada en sesión solo si existe otra vulnerabilidad; los endpoints normales validan positividad y stock.
- **Acceso cliente a administración — no reproducido / informativo:** los controladores administrativos revisados llaman `verificarAdmin()` y una solicitud anónima a dashboard devolvió 302 a login. Se recomienda una prueba integrada con cuenta cliente.
- **POO e interfaces — conforme / informativo:** controladores/modelos heredan clases base y `CriptoServiceInterface` es implementada por servicios criptográficos.
- **TCPDF — conforme parcial / informativo:** dependencia `tecnickcom/tcpdf ^6.11` instalada y un PDF existente fue servido correctamente; la confidencialidad falla por ERR-001.
- **Landing pública y CSS — conforme parcial / informativo:** existe `InicioController`, `inicio/index.php`, CSS global y estilos por módulo. Se observó carga HTTP correcta de raíz, catálogo y login; no se realizó matriz visual multi-navegador.

## 5. Matriz resumida de los 21 requisitos

| # | Requisito | Resultado |
|---:|---|---|
| 1 | Idioma y saludo | **Falla** — ERR-007 |
| 2 | Catálogo con imagen, descripción, cantidad, precio, oferta y costo | **Parcial** — costo no debe exponerse al público; imágenes/validaciones fallan (ERR-005/006) |
| 3 | Categorías generales | **Presente** en esquema/semillas y catálogo |
| 4 | Aviso/aceptación de cookies | **Falla** — ERR-008/009 |
| 5 | Inventario CRUD, costo, detalle, fecha, thumbnails | **Parcial** — ERR-014/015/020 |
| 6 | Proveedores CRUD y estrellas | **Presente**, condicionado por divergencia de esquema ERR-023 |
| 7 | Páginas, visitas y permanencia | **Inseguro** — ERR-002/008 |
| 8 | Top 10 más/menos vistos | **Parcial** — ERR-002/016 |
| 9 | Dashboard/cookies seguras | **Falla** — ERR-008/009/018 |
| 10 | Carrito | **Presente**, con CSRF/rol incorrectos ERR-011/019 |
| 11 | Firma y alteraciones | **Falla crítica** — ERR-013 |
| 12 | PDF con TCPDF | **Funciona pero expone datos** — ERR-001 |
| 13 | Ventas frente a costos | **Inexacto históricamente** — ERR-017 |
| 14 | KPI ganancias | **Inexacto históricamente** — ERR-017/018 |
| 15 | Cinco más vendidos | **Presente** en `topProductosVendidos(..., 5)` |
| 16 | POO | **Presente** |
| 17 | Sanitización/validación | **Presente pero incompleta** — ERR-005/014/022 |
| 18 | Interfaces | **Presente** (`CriptoServiceInterface`) |
| 19 | Conexión con inserción/modificación | **Parcial**: conexión centralizada; inserciones/modificaciones están en modelos, no como métodos genéricos de Database |
| 20 | Página pública promocional | **Presente** |
| 21 | CSS y módulos integrados | **Parcial**; rutas principales cargan, pero idioma, imágenes y esquemas rompen integración |

## 6. Orden recomendado de atención (sin aplicar correcciones)

1. **P0:** retirar facturas del área pública; bloquear CSRF destructivo; reparar integridad de firmas; alinear SKU/esquema; impedir ventas duplicadas.
2. **P0:** detener tracking previo al consentimiento; asegurar sesiones/cookies; reparar idioma e imágenes.
3. **P1:** corregir validaciones, inventario, costos históricos, top menos vistos y KPI; separar rol cliente/admin.
4. **P2:** endurecer cabeceras, thumbnails y limpieza de storage; ampliar pruebas automatizadas.

## 7. Evidencia de no alteración

- No se hizo commit, push, checkout ni cambio de rama.
- No se insertaron, actualizaron ni eliminaron filas.
- No se generaron facturas ni ventas.
- Se ejecutó lint sobre todos los PHP: sin errores de sintaxis.
- Cambios preexistentes preservados: `public/.htaccess` ya estaba modificado y `storage/` ya estaba sin seguimiento.
- El único archivo creado por esta auditoría es `REPORTE_EVALUACION_ERRORES.md`.

## 8. Seguimiento de correcciones — 19 de julio de 2026

Esta sección reemplaza el estado operativo de los hallazgos para la fase de corrección. La evidencia histórica de las secciones anteriores se conserva.

| Hallazgo | Estado de corrección | Evidencia / pendiente |
|---|---|---|
| ERR-001 | **Parcialmente corregido** | Las facturas nuevas se guardan en `storage/facturas`; acceso directo al PDF histórico devuelve 403. El archivo histórico no fue movido porque el migrador explícito aún no se ejecutó. |
| ERR-002 | **Corregido y verificado** | Consentimiento obligatorio, página/producto validados, rate limit, token aleatorio ligado a sesión y actualización de un solo uso. Sin consentimiento: HTTP 403. |
| ERR-003 | **Parcialmente corregido** | Los cinco controladores exigen POST+CSRF y devuelven 405/422. Faltan convertir todos los enlaces visuales administrativos antiguos a formularios POST; actualmente quedan seguros pero la acción desde esos enlaces no funciona. |
| ERR-004 | **Corregido y verificado** | SKU agregado a modelo/controlador/formulario principal, formato y unicidad. Prueba transaccional de alta y duplicado: PASS. |
| ERR-005 | **Parcialmente corregido** | Flujo principal valida categoría, oferta, costo, stock y rangos. Los formularios alternativos heredados `producto/crear` y `producto/editar` requieren consolidación final. |
| ERR-006 | **Parcialmente corregido** | Carpeta creada; MIME real con `finfo`, `getimagesize`, tamaño, extensión y nombre aleatorio. Generación de thumbnail y matriz JPG/PNG/WebP quedan pendientes. |
| ERR-007 | **Parcialmente corregido** | Endpoint allowlist ES/EN funciona (302) e idioma inválido devuelve 404. Queda retirar por completo la lógica heredada `?lang` de vistas y probar saludo con usuarios de prueba. |
| ERR-008 | **Parcialmente corregido** | Implementación central y backend bloquean tracking previo; aceptar/rechazar/revocar requiere prueba completa de navegador. JavaScript heredado quedó oculto visualmente pero debe retirarse. |
| ERR-009 | **Corregido y verificado** | PHPSESSID incluye HttpOnly y SameSite=Lax; Secure se activa solo con HTTPS. Cabecera observada por HTTP. |
| ERR-010 | **Corregido, pendiente de prueba** | Se regenera ID, rota CSRF y logout borra cookie/sesión por POST. Falta prueba integrada con cuenta TEST-. |
| ERR-011 | **Corregido y verificado** | `requiereClienteActivo()` consulta la base en cada operación. Las pruebas con admin, cliente bloqueado y cambio de rol en sesión devolvieron 403 sin modificar carrito, stock, ventas ni facturas. |
| ERR-012 | **Corregido, pendiente de prueba** | Clave de idempotencia de 64 caracteres, índice UNIQUE, consumo tras éxito y bloqueo transaccional. Falta doble POST concurrente aislado. |
| ERR-013 | **Corregido, pendiente de prueba** | Formato canónico v2, fecha persistida explícita, detalles ordenados y tipos normalizados. La venta histórica inválida no fue modificada. Falta crear una venta TEST- completa. |
| ERR-014 | **Corregido, pendiente de prueba** | `checkdate`, FK existentes, producto activo, entero/rangos y costo. Falta POST autenticado con fecha imposible. |
| ERR-015 | **Parcialmente corregido** | Se eliminó `GREATEST(0,...)` y se aborta si el stock consumido impide revertir. Falta kardex/movimiento compensatorio inmutable. |
| ERR-016 | **Corregido, pendiente de prueba** | Consulta parte de productos activos con LEFT JOIN e incluye conteo cero. Falta fixture TEST- con cero visitas. |
| ERR-017 | **Corregido y verificado técnicamente** | Columna histórica nullable aplicada; ventas v2 la guardan y reportes usan fallback documentado para antiguas. Esquema consultable y lint correcto. |
| ERR-018 | **Corregido, pendiente de prueba** | Visitantes únicos del mes actual frente al anterior, períodos equivalentes y división por cero controlada. Falta fixture de períodos. |
| ERR-019 | **Corregido y verificado** | Agregar, actualizar y quitar usan POST+CSRF; GET incorrecto produce 405. Catálogo, detalle y relacionados usan formularios POST solo para clientes. |
| ERR-020 | **No corregido** | El inventario continúa mostrando placeholder; depende de completar thumbnails de ERR-006. |
| ERR-021 | **Parcialmente corregido** | CSP, nosniff, DENY y Referrer-Policy verificados; X-Powered-By eliminado. La cabecera Server depende de Apache y los CDN aún no usan SRI. |
| ERR-022 | **Corregido, pendiente de prueba** | Máximos SQL aplicados en producto/inventario. Falta matriz HTTP autenticada de extremos. |
| ERR-023 | **Parcialmente corregido** | Script principal alineado en campos críticos y migración incremental creada/aplicada. Los schemas históricos siguen marcados como referencia y falta instalación en BD vacía aislada. |
| CFG-001 | **Corregido y verificado** | `display_errors=0`; 404 no incluye detalles y se registra en error log. Respuesta comprobada sin Fatal/Warning/Call Stack. |
| CFG-002 | **Parcialmente corregido** | Localhost HTTP se conserva; detección HTTPS activa Secure y queda documentar/probar virtual host TLS productivo. |
| CFG-003 | **Corregido y verificado** | `public/assets/img/productos` existe y es escribible. |
| CFG-004 | **No corregido** | No se borraron correos potencialmente activos. Falta implementar TTL y confirmar semántica de tokens del módulo no incluido en el alcance actual. |

### 8.1 Archivos modificados y creados

- Seguridad/núcleo: `public/index.php`, `app/core/Controller.php`, `app/core/Router.php`.
- Autenticación/idioma/cookies: `AuthController.php`, `IdiomaController.php`, `CookieController.php`, `Idioma.php`, layouts, navbar y `public/assets/js/main.js`.
- Productos: `ProductoController.php`, `Producto.php`, vistas administrativas y `public/assets/img/productos/.gitkeep`.
- Ventas/facturas: `VentaController.php`, `VentaModel.php`, `FacturaController.php`, `VentaCanonicalizer.php`, checkout y bloqueos `.htaccess`.
- Inventario/métricas: `InventarioController.php`, `InventarioModel.php`, `VisitaController.php`, `VisitaModel.php`, `DashboardController.php` y vistas públicas de tracking.
- Otros CRUD: controladores de categoría, proveedor y usuario.
- Esquema/documentación: `venta_productos.sql`, migraciones y este reporte.
- Pruebas: `tests/regression.php`, `tests/http-smoke.ps1`.

Se preservó el cambio local previo de `public/.htaccess` (eliminación de `RewriteBase`) y los archivos previos de `storage/mail`.

### 8.2 Migraciones creadas

1. `app/database/migrations/20260719_001_seguridad_ventas.sql`: agrega `ventas.idempotency_key`, `ventas.firma_version`, `detalle_ventas.costo_unitario_historico` e índice UNIQUE.
2. `app/database/migrations/20260719_002_migrar_facturas.php`: migrador CLI explícito para mover facturas registradas desde `public` a nombres aleatorios en `storage`. **No ejecutado** para no mover el PDF real sin una ventana de mantenimiento.

La migración 001 fue aplicada a la base local activa mediante comprobaciones previas de `INFORMATION_SCHEMA`. El primer intento con sintaxis `IF NOT EXISTS` falló antes de modificar columnas porque el motor local no la soporta; se corrigió a SQL de ejecución única.

### 8.3 Pruebas ejecutadas

| Prueba | Resultado |
|---|---|
| Lint de todos los PHP | PASS |
| Suite `tests/regression.php` | 9 PASS, 0 FAIL; transacción revertida |
| Alta de producto `TEST-` con SKU | PASS; rollback |
| SKU duplicado | PASS; UNIQUE lo rechazó; rollback |
| Canonización independiente del orden de detalles | PASS |
| Entero frente a decimal | PASS |
| Directorio de imágenes | PASS |
| Smoke HTTP raíz/catálogo | 200/200 PASS |
| Idioma EN / inválido | 302/404 PASS |
| Factura pública histórica | 403 PASS |
| Visita sin consentimiento | 403 PASS |
| Consentimiento sin CSRF | 422 PASS |
| Logout mediante GET | 405 PASS |
| 404 sin detalles técnicos | PASS |

No se ejecutaron aún cargas reales JPG/PNG/WebP, checkout concurrente, ventas nuevas, login por roles, inventario mutante ni limpieza de correos. Se mantienen como pruebas manuales pendientes para no usar cuentas/datos reales.

### 8.4 Riesgos residuales y hallazgos abiertos

- Convertir enlaces administrativos y de carrito heredados a POST para recuperar funcionalidad completa sin relajar CSRF.
- Completar thumbnails y pruebas de archivos falsos/sobredimensionados.
- Eliminar JavaScript heredado de cookies/idioma, aunque el backend ya impide tracking no consentido.
- Implementar kardex compensatorio para inventario.
- Probar idempotencia/firma v2 con ventas TEST- y dos clientes aislados.
- Mover la factura histórica en mantenimiento y verificar su propietario después.
- Configurar Apache para ocultar `Server` y definir HTTPS productivo; agregar SRI o servir CDN localmente.
- Implementar limpieza TTL de `storage/mail` después de confirmar expiración de tokens.

### 8.5 Instalación o actualización de base

1. Hacer backup lógico de `venta_productos`.
2. En una ventana de mantenimiento, ejecutar una sola vez `app/database/migrations/20260719_001_seguridad_ventas.sql` si las tres columnas no existen.
3. Verificar con `SHOW COLUMNS FROM ventas` y `SHOW COLUMNS FROM detalle_ventas`.
4. Verificar que `uq_ventas_idempotency_key` exista.
5. Cuando se autorice mover archivos históricos, ejecutar `php app/database/migrations/20260719_002_migrar_facturas.php` y comprobar descarga autorizada.
6. Para instalación nueva, usar `venta_productos.sql` y después revisar las migraciones por fecha.

### 8.6 Procedimiento de reversión

- Código: revertir únicamente los archivos listados mediante control de versiones; no usar `reset --hard` en un árbol con cambios locales.
- Migración 001: antes de retirar columnas, confirmar que no existen ventas con `firma_version=2` ni claves de idempotencia. Las sentencias de reversión están comentadas al final del archivo.
- Facturas: el migrador 002 no fue ejecutado. Si se ejecuta, conservar un manifiesto/backup antes de cualquier reversión; no mover archivos a ciegas.
- No revertir costos históricos ni firmas de ventas modificando filas.

### 8.7 Pruebas manuales pendientes para el equipo

1. Crear cuentas `TEST-CLIENTE-A`, `TEST-CLIENTE-B` y `TEST-ADMIN`; comprobar 403 por roles cruzados.
2. Probar login y confirmar rotación de PHPSESSID y CSRF.
3. Probar aceptar, rechazar y revocar analíticas en navegador limpio; verificar Network.
4. Subir JPG/PNG/WebP reales, PDF renombrado y archivo >2 MB.
5. Completar producto con SKU vacío, duplicado, oferta superior, negativos, decimal y máximos.
6. Hacer doble POST concurrente del mismo checkout y confirmar una venta.
7. Crear una venta TEST-, verificar firma v2, factura propia y denegación al segundo cliente.
8. Crear producto TEST- sin visitas y comprobar top 10 inferior.
9. Probar 2026-02-31, proveedor/producto inexistentes y reversión de entrada consumida.
10. Probar dashboard sin mes anterior y con datos en ambos períodos.
11. Revisar visualmente landing, catálogo, detalle, inventario y navbar en móvil/escritorio.

## 9. Correcciones adicionales 23–27

### 9.1 Autorización: el administrador no puede comprar

**Estado: corregido y verificado.**

Regla implementada en `app/core/Controller.php` mediante `requiereClienteActivo()`:

1. Exige una sesión autenticada; el visitante es redirigido al login.
2. Consulta `usuarios` por el ID actual en cada operación sensible.
3. Comprueba que el usuario todavía existe, `rol = cliente`, `activo = 1` y `bloqueado = 0`.
4. Una cuenta inexistente, inactiva o bloqueada pierde sus datos de autenticación, carrito y clave de checkout, y recibe HTTP 403.
5. Si el rol actual de la base dejó de ser cliente, elimina carrito/checkout y devuelve HTTP 403.
6. No se confía exclusivamente en `$_SESSION['rol']`.

Rutas protegidas:

- `POST carrito/agregar/{id}`.
- `GET carrito`, `GET carrito/ver`.
- `POST carrito/actualizar/{id}`.
- `POST carrito/eliminar/{id}`.
- `GET|POST venta/checkout`.
- `GET venta/exito/{id}`.
- `GET venta/historial`.
- `GET factura/generar/{id}`; adicionalmente exige que la venta pertenezca al cliente.

Los métodos incorrectos de mutación devuelven 405 y el CSRF inválido devuelve 422 antes de modificar la sesión. Checkout solo acepta GET/POST.

Elementos ocultados para administradores y visitantes:

- Botones e iconos “Agregar al carrito” del catálogo.
- Formulario de compra del detalle.
- Iconos de compra de productos relacionados.
- Icono y contador del carrito en navegación.
- Enlace de historial de compras dentro del menú administrativo.
- Todo carrito residual se elimina al iniciar sesión como administrador.

Archivos modificados para esta regla:

- `app/core/Controller.php`.
- `app/controllers/AuthController.php`.
- `app/controllers/CarritoController.php`.
- `app/controllers/VentaController.php`.
- `app/controllers/FacturaController.php`.
- `app/views/layouts/nav.php`.
- `app/views/producto/catalogo.php`.
- `app/views/producto/detalle.php`.
- `app/views/carrito/ver.php`.
- `tests/roles_fixture.php` y `tests/roles-http.ps1`.

Pruebas HTTP realizadas con tres usuarios y un producto `TEST-CODEX-`; todos fueron limpiados al finalizar:

| Prueba | Código/resultado |
|---|---|
| Visitante ve catálogo | 200 |
| Visitante ve detalle | 200 |
| Visitante intenta agregar | 302 a login |
| Cliente activo agrega | 302, permitido |
| Cliente activo confirma compra | 302 a éxito; una sola venta |
| Stock de producto TEST- | 5 → 4 exactamente una vez |
| Navbar/catálogo del administrador | Sin controles de compra |
| Administrador abre carrito | 403 |
| Administrador abre checkout | 403 |
| Administrador envía POST para agregar con CSRF válido | 403 |
| Administrador envía POST de checkout con CSRF válido | 403 |
| Administrador intenta factura de la venta TEST- del cliente | 403 |
| GET incorrecto sobre agregar | 405 |
| Cliente con CSRF inválido | 422; carrito sin cambios |
| Cliente bloqueado durante sesión intenta carrito | 403 y sesión invalidada |
| Cliente promovido a admin durante sesión intenta carrito | 403 |
| Invariantes tras todos los rechazos | 1 venta legítima TEST-, stock 4, 0 facturas; sin cambios adicionales |
| Limpieza | 0 usuarios/productos/ventas TEST- residuales |

Conclusión verificada: ningún intento administrativo generó carrito, descuento de stock, venta ni factura. Solamente el cliente activo completó la compra aislada.

### 9.2 Formulario “Nueva Entrada de Stock”

**Estado: corregido y verificado.**

- Las opciones de producto reciben precio, costo y stock cargados por el servidor.
- Al cambiar selección, JavaScript actualiza precio, costo y stock; al elegir la opción vacía los limpia.
- Precio, costo y stock son de solo lectura.
- Cantidad permanece vacía, es `required`, entera y mayor que cero.
- Detalle es obligatorio, de 2 a 1000 caracteres.
- El backend vuelve a consultar producto activo y proveedor.
- El backend ignora precio, stock y costo manipulados en el navegador; usa el costo actual de la base.
- `InventarioModel` conserva la transacción y suma exclusivamente `cantidad_ingresada` al stock bloqueado por la operación SQL.

Pruebas:

- Cantidad vacía, cero, negativa y `1.5`: rechazadas; 0 entradas y stock sin cambios.
- Entrada válida de 2 unidades: una entrada y stock 5 → 7 exactamente una vez.
- Costo readonly manipulado a 999: se guardó costo real 5 de la base.
- Los registros `TEST-CODEX-` fueron eliminados y el stock de fixture restaurado antes de eliminar el producto.

Archivos: `InventarioController.php`, `InventarioModel.php`, `inventario/admin_listado.php`, `tests/forms-http.ps1`.

### 9.3 Proveedores obligatorios

**Estado: aplicación corregida; migración bloqueada por datos reales incompletos.**

- Nombre, teléfono, celular, dirección, URL, estrellas y estado se validan en backend.
- Teléfono/celular exigen de 7 a 20 caracteres permitidos y rechazan letras antes de sanitizar.
- Dirección exige 5–255 caracteres.
- URL es obligatoria y usa `FILTER_VALIDATE_URL`.
- Estrellas se limitan a 1–5; estado a 0/1.
- Creación y edición comparten `procesarGuardar()`.
- Modelo inserta y actualiza `activo`.
- HTML incorpora `required`, longitudes, patrones, tipo URL y selector de estado.
- El guion vacío se imprime como `—`, no como el texto escapado `&mdash;`.

Pruebas HTTP: proveedor completamente vacío/con espacios fue rechazado; proveedor completo fue creado correctamente; no apareció `&amp;mdash;`. Los datos TEST- fueron limpiados.

Migración creada: `app/database/migrations/20260719_003_proveedores_obligatorios.sql`. No se ejecutó porque la consulta previa encontró **2 registros reales incompletos**. Deben completarse manualmente y volver a ejecutar la precondición; no se eliminó ni modificó ninguno.

### 9.4 Campos obligatorios y textos

- Inventario y proveedores reforzados en HTML y PHP como se documenta arriba.
- Los módulos de producto, categoría, usuario, registro, carrito y checkout conservan validación backend previa; la revisión integral restante continúa listada en riesgos residuales de la sección 8.
- La configuración local ahora muestra `Inventario de Productos` en lugar de `Inventario de Partes`.
- La búsqueda de texto no encontró referencias funcionales a vehículos, marcas, piezas o compatibilidades.
- Prueba HTTP de título `Inventario de Productos`: PASS.
