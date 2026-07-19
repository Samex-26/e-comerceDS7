<?php

use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    public function enviarRestablecimiento(array $usuario, string $enlace): string
    {
        $minutos = 30;
        $asunto = APP_NAME . ' - enlace para cambiar contraseña';
        $nombre = htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8');
        $html = "<h2>" . htmlspecialchars(APP_NAME) . "</h2><p>Hola {$nombre},</p><p>Use este enlace para establecer una nueva contraseña:</p><p><a href=\"{$url}\">Cambiar contraseña</a></p><p>Caduca en {$minutos} minutos. Si no solicitó este cambio, ignore el mensaje.</p>";
        $texto = APP_NAME . "\n\nHola {$usuario['nombre']},\n\nAbra este enlace para establecer una nueva contraseña:\n{$enlace}\n\nCaduca en {$minutos} minutos. Si no solicitó este cambio, ignore el mensaje.";

        if (!(defined('SMTP_HOST') && SMTP_HOST !== '')) {
            if (!DEBUG || !defined('MAIL_TEST_DIR') || MAIL_TEST_DIR === '') {
                throw new RuntimeException('El correo SMTP no está configurado.');
            }
            $dir = MAIL_TEST_DIR;
            if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
                throw new RuntimeException('No se pudo crear el directorio de correos de prueba.');
            }
            $archivo = $dir . DIRECTORY_SEPARATOR . 'password-reset-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.html';
            if (file_put_contents($archivo, $html, LOCK_EX) === false) {
                throw new RuntimeException('No se pudo guardar el correo de prueba.');
            }
            return 'archivo de prueba';
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($usuario['email'], $usuario['nombre']);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $html;
        $mail->AltBody = $texto;
        $mail->send();
        return 'SMTP';
    }
}
