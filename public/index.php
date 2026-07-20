<?php
// Front Controller - Punto de entrada único del sistema

require_once __DIR__ . '/../app/config/config.php';

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
ini_set('expose_php', '0');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

// Composer autoload (TCPDF y futuras dependencias)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Autoload simple: busca clases en app/core/, app/helpers/, app/contracts/, app/services/, app/models/, app/controllers/
spl_autoload_register(function (string $class) {
    $dirs = [
        BASE_PATH . '/core/',
        BASE_PATH . '/helpers/',
        BASE_PATH . '/contracts/',
        BASE_PATH . '/services/',
        BASE_PATH . '/models/',
        BASE_PATH . '/controllers/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax']);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// Despachar la petición
$router = new Router();
$router->dispatch();
