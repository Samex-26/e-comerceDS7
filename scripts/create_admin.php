<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$config = $root . '/app/config/config.php';
if (!is_file($config)) {
    fwrite(STDERR, "Falta app/config/config.php. Configure la aplicación primero.\n");
    exit(1);
}

require $config;
require $root . '/vendor/autoload.php';

$email = Sanitizer::email($argv[1] ?? 'admin@localhost.test');
if (!Validator::email($email)) {
    fwrite(STDERR, "Indique un correo válido como primer argumento.\n");
    exit(1);
}

function leerPassword(): string
{
    fwrite(STDOUT, "Contraseña inicial (8 a 12 caracteres): ");
    if (DIRECTORY_SEPARATOR === '\\') {
        $command = 'powershell -NoProfile -Command "$s=Read-Host -AsSecureString; '
            . '$b=[Runtime.InteropServices.Marshal]::SecureStringToBSTR($s); '
            . 'try {[Runtime.InteropServices.Marshal]::PtrToStringBSTR($b)} '
            . 'finally {[Runtime.InteropServices.Marshal]::ZeroFreeBSTR($b)}"';
        $value = shell_exec($command);
        fwrite(STDOUT, PHP_EOL);
        return rtrim((string) $value, "\r\n");
    }
    shell_exec('stty -echo');
    $value = rtrim((string) fgets(STDIN), "\r\n");
    shell_exec('stty echo');
    fwrite(STDOUT, PHP_EOL);
    return $value;
}

$password = leerPassword();
if (!Validator::longitud($password, 8, 12)) {
    fwrite(STDERR, "La contraseña debe tener entre 8 y 12 caracteres.\n");
    exit(1);
}

$usuarioModel = new Usuario();
if ($usuarioModel->buscarPorEmail($email)) {
    fwrite(STDERR, "Ya existe un usuario con ese correo; no se creó un duplicado.\n");
    exit(1);
}

$idioma = (new Idioma())->buscarPorCodigo('es');
if (!$idioma) {
    fwrite(STDERR, "Falta el idioma 'es'. Importe venta_productos.sql.\n");
    exit(1);
}

$usuarioModel->crear([
    'nombre' => 'admin',
    'email' => $email,
    'password_hash' => (new PasswordHasherService())->procesar($password),
    'id_idioma' => (int) $idioma['id_idioma'],
    'rol' => 'admin',
    'activo' => 1,
]);

$password = str_repeat("\0", strlen($password));
fwrite(STDOUT, "Administrador creado. Cambie la contraseña inicial después del primer acceso.\n");
