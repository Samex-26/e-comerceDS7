<?php
// Front Controller - Punto de entrada único del sistema

require_once __DIR__ . '/../app/config/config.php';

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
    session_start();
}

// Despachar la petición
$router = new Router();
$router->dispatch();
