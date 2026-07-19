<?php

class ContactoController extends Controller
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesar();
        } else {
            $this->view('contacto/index', [
                'csrf_token' => $this->generarTokenCsrf(),
                'exito'      => $_SESSION['exito_contacto'] ?? '',
                'errores'    => $_SESSION['errores_contacto'] ?? [],
            ]);
            unset($_SESSION['exito_contacto'], $_SESSION['errores_contacto']);
        }
    }

    private function procesar(): void
    {
        $errores = [];

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = 'Error de seguridad. Intente nuevamente.';
        }

        $nombre   = Sanitizer::texto($_POST['nombre'] ?? '');
        $email    = Sanitizer::email($_POST['email'] ?? '');
        $asunto   = Sanitizer::texto($_POST['asunto'] ?? '');
        $mensaje  = Sanitizer::texto($_POST['mensaje'] ?? '');

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (!Validator::noVacio($mensaje)) {
            $errores[] = 'El mensaje no puede estar vacio.';
        }
        if (!Validator::email($email)) {
            $errores[] = 'El correo electronico no es valido.';
        }

        if (!empty($errores)) {
            $_SESSION['errores_contacto'] = $errores;
            $_SESSION['old_contacto'] = [
                'nombre'  => $nombre,
                'email'   => $email,
                'asunto'  => $asunto,
                'mensaje' => $mensaje,
            ];
            $this->redirect('contacto/index');
            return;
        }

        $_SESSION['exito_contacto'] = 'Gracias por tu mensaje, te contactaremos pronto.';
        $this->redirect('contacto/index');
    }
}
