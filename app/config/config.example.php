<?php

// Copie este archivo como config.php y reemplace solo con valores locales.
define('APP_NAME', 'Inventario de Partes');
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/ruta-del-proyecto/public/');

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'venta_productos');
define('DB_USER', 'usuario_local');
define('DB_PASS', 'contraseña_local');
define('DB_CHARSET', 'utf8mb4');

define('DEBUG', false);
define('SECRET_KEY', 'reemplace-con-una-clave-aleatoria-de-al-menos-32-bytes');

// Correo: deje SMTP_HOST vacío para escribir mensajes de prueba solo con DEBUG=true.
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USER', 'usuario-smtp');
define('SMTP_PASS', 'contraseña-smtp');
define('SMTP_SECURE', 'tls');
define('MAIL_FROM_ADDRESS', 'no-reply@example.test');
define('MAIL_FROM_NAME', APP_NAME);
define('MAIL_TEST_DIR', dirname(__DIR__, 2) . '/storage/mail');
