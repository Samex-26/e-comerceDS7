<?php

if (PHP_SAPI !== 'cli') exit(1);

$root = dirname(__DIR__);
define('BASE_PATH', $root . '/app');
define('DB_HOST', getenv('TEST_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('TEST_DB_NAME') ?: 'venta_productos_audit_phase1');
define('DB_USER', getenv('TEST_DB_USER') ?: 'root');
define('DB_PASS', getenv('TEST_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
define('SECRET_KEY', 'test-only-key-not-for-production');
define('BASE_URL', '/');
define('APP_NAME', 'Test');
define('DEBUG', false);
require $root . '/vendor/autoload.php';

session_start();
class Phase1TestController extends Controller
{
    public function validarCsrf(): void { $this->requerirCsrf(); }
}

function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
    echo "OK: $message\n";
}

$db = Database::getInstance()->getConnection();
$suffix = bin2hex(random_bytes(5));
$email = "smoke-$suffix@example.test";
$userId = 0;
$productId = 0;
$categoryId = 0;

try {
    $hash = (new PasswordHasherService())->procesar('Prueba123');
    check(password_verify('Prueba123', $hash), 'password_hash/password_verify');

    $idiomaId = (int) $db->query("SELECT id_idioma FROM idiomas WHERE codigo='es'")->fetchColumn();
    $usuarios = new Usuario();
    $userId = $usuarios->crear([
        'nombre' => 'Smoke Test', 'email' => $email, 'password_hash' => $hash,
        'id_idioma' => $idiomaId, 'rol' => 'cliente', 'activo' => 1,
    ]);
    for ($i = 1; $i <= 3; $i++) {
        $usuarios->registrarFallo($userId);
        $usuarios->registrarIntento($email, '127.0.0.1', 'fallido');
        $actual = $usuarios->buscarPorId($userId);
        check((int) $actual['intentos_fallidos'] === $i, "intento fallido $i registrado");
    }
    check((bool) $usuarios->buscarPorId($userId)['bloqueado'], 'bloqueo en tercer intento');
    check((int) $db->query("SELECT COUNT(*) FROM intentos_login WHERE email=" . $db->quote($email))->fetchColumn() === 3, 'IP, fecha y resultado auditados');
    $usuarios->restablecerIntentos($userId);
    check(!(bool) $usuarios->buscarPorId($userId)['bloqueado'], 'restablecimiento tras acceso/activación');
    $usuarios->cambiarEstado($userId, false);
    check(!(bool) $usuarios->buscarPorId($userId)['activo'], 'desactivación lógica de usuario');
    $usuarios->cambiarEstado($userId, true);
    check((bool) $usuarios->buscarPorId($userId)['activo'], 'activación de usuario');

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_POST['csrf_token'] = $_SESSION['csrf_token'];
    (new Phase1TestController())->validarCsrf();
    check(true, 'POST con CSRF válido aceptado');

    $stmt = $db->prepare('INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)');
    $stmt->execute([':nombre' => "Smoke $suffix", ':descripcion' => 'Temporal']);
    $categoryId = (int) $db->lastInsertId();
    $stmt = $db->prepare('INSERT INTO productos (nombre, precio, costo, cantidad, id_categoria, activo) VALUES (:nombre, 10, 5, 5, :categoria, 1)');
    $stmt->execute([':nombre' => "Parte $suffix", ':categoria' => $categoryId]);
    $productId = (int) $db->lastInsertId();

    $cadena = json_encode(['prueba' => $suffix]);
    $firma = (new FirmaDigitalService())->procesar($cadena);
    check((new FirmaDigitalService())->verificar($cadena, $firma), 'firma HMAC válida');

    $ventas = new VentaModel();
    $ventaId = $ventas->crear([
        'id_usuario' => $userId, 'fecha' => date('Y-m-d H:i:s'), 'total' => 20,
        'hash_datos' => hash('sha256', $cadena), 'firma_digital' => $firma,
        'estado' => 'confirmada',
    ], [['id_producto' => $productId, 'cantidad' => 2, 'precio_unitario' => 10, 'subtotal' => 20]]);
    check((int) $db->query("SELECT cantidad FROM productos WHERE id_producto=$productId")->fetchColumn() === 3, 'venta descuenta stock');

    try {
        $ventas->crear([
            'id_usuario' => $userId, 'fecha' => date('Y-m-d H:i:s'), 'total' => 990,
            'hash_datos' => hash('sha256', 'rechazo'), 'firma_digital' => $firma,
            'estado' => 'confirmada',
        ], [['id_producto' => $productId, 'cantidad' => 99, 'precio_unitario' => 10, 'subtotal' => 990]]);
        throw new RuntimeException('La venta sin stock no fue rechazada.');
    } catch (RuntimeException $e) {
        check(str_contains($e->getMessage(), 'Stock insuficiente'), 'venta sin stock rechazada');
    }
    check((int) $db->query("SELECT cantidad FROM productos WHERE id_producto=$productId")->fetchColumn() === 3, 'rollback conserva stock');
} finally {
    if ($productId) {
        $db->exec("DELETE FROM facturas WHERE id_venta IN (SELECT id_venta FROM ventas WHERE id_usuario=$userId)");
        $db->exec("DELETE FROM detalle_ventas WHERE id_venta IN (SELECT id_venta FROM ventas WHERE id_usuario=$userId)");
        $db->exec("DELETE FROM ventas WHERE id_usuario=$userId");
        $db->exec("DELETE FROM productos WHERE id_producto=$productId");
    }
    if ($categoryId) $db->exec("DELETE FROM categorias WHERE id_categoria=$categoryId");
    if ($userId) {
        $stmt = $db->prepare('DELETE FROM intentos_login WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $db->exec("DELETE FROM usuarios WHERE id_usuario=$userId");
    }
}

echo "Smoke test completado.\n";
