<?php
require dirname(__DIR__) . '/app/config/config.php';
require dirname(__DIR__) . '/vendor/autoload.php';

function check(bool $ok, string $label): void { echo ($ok ? '[OK] ' : '[FAIL] ') . $label . PHP_EOL; if (!$ok) throw new RuntimeException($label); }

$db = Database::getInstance()->getConnection();
$suffix = bin2hex(random_bytes(5));
$email = "phase2-{$suffix}@example.test";
$provider = "Proveedor Phase2 {$suffix}";
$usuarioId = 0;
$providerId = 0;
try {
    $idioma = (new Idioma())->buscarPorCodigo('es');
    $oldPassword = 'Anterior1';
    $newPassword = 'NuevaPass1';
    $usuarioId = (new Usuario())->crear(['nombre' => 'Phase Two', 'email' => $email, 'password_hash' => password_hash($oldPassword, PASSWORD_BCRYPT), 'id_idioma' => $idioma['id_idioma'], 'rol' => 'cliente', 'activo' => 1]);
    $providerId = (new ProveedorModel())->crear(['nombre' => $provider, 'telefono' => '', 'celular' => '', 'email' => '', 'direccion' => '', 'ciudad' => '', 'sitio_web' => 'https://example.test', 'calificacion_estrellas' => 0, 'notas' => '']);
    check((bool) (new ProveedorModel())->buscarPorId($providerId), 'Proveedor válido almacenado y visible en listado/modelo');

    $model = new PasswordResetToken();
    $token1 = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $model->crear($usuarioId, hash('sha256', $token1), '127.0.0.1', $usuarioId);
    $row = $db->query('SELECT * FROM password_reset_tokens WHERE usuario_id=' . $usuarioId . ' ORDER BY id DESC LIMIT 1')->fetch();
    check($row['token_hash'] === hash('sha256', $token1) && $row['token_hash'] !== $token1, 'La base almacena solo el hash SHA-256');
    check(!$model->buscarValido(hash('sha256', $token1 . 'alterado')), 'Token alterado rechazado');

    $token2 = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $model->crear($usuarioId, hash('sha256', $token2), '127.0.0.1', $usuarioId);
    check(!$model->buscarValido(hash('sha256', $token1)), 'Token anterior invalidado');
    $valid = $model->buscarValido(hash('sha256', $token2));
    check($valid && strtotime($valid['fecha_expiracion']) <= time() + 1805, 'Token caduca en 30 minutos');

    $expired = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $model->crear($usuarioId, hash('sha256', $expired), '127.0.0.1', $usuarioId);
    $db->prepare('UPDATE password_reset_tokens SET fecha_expiracion=DATE_SUB(NOW(), INTERVAL 1 MINUTE) WHERE token_hash=:h')->execute([':h' => hash('sha256', $expired)]);
    check(!$model->buscarValido(hash('sha256', $expired)), 'Token vencido rechazado');

    $final = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $model->crear($usuarioId, hash('sha256', $final), '127.0.0.1', $usuarioId);
    $valid = $model->buscarValido(hash('sha256', $final));
    check($model->consumir((int) $valid['id'], $usuarioId, password_hash($newPassword, PASSWORD_BCRYPT), '127.0.0.1'), 'Token utilizado una vez');
    check(!$model->consumir((int) $valid['id'], $usuarioId, password_hash('OtraPass1', PASSWORD_BCRYPT), '127.0.0.1'), 'Segundo uso rechazado');
    $user = (new Usuario())->buscarPorId($usuarioId);
    check(password_verify($newPassword, $user['password_hash']) && !password_verify($oldPassword, $user['password_hash']), 'Nueva contraseña funciona y anterior deja de funcionar');
    check((int) $user['bloqueado'] === 0 && (int) $user['intentos_fallidos'] === 0, 'Bloqueo e intentos limpiados');

    $mailToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $before = glob(MAIL_TEST_DIR . '/*.html') ?: [];
    (new MailService())->enviarRestablecimiento($user, BASE_URL . 'auth/restablecer/' . $mailToken);
    $after = glob(MAIL_TEST_DIR . '/*.html') ?: [];
    check(count($after) > count($before), 'Correo HTML escrito en modo de prueba');
} finally {
    if ($providerId) $db->prepare('DELETE FROM proveedores WHERE id_proveedor=:id')->execute([':id' => $providerId]);
    if ($usuarioId) $db->prepare('DELETE FROM usuarios WHERE id_usuario=:id')->execute([':id' => $usuarioId]);
}
