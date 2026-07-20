<?php
// Controlador de autenticación: registro, login, logout.

class AuthController extends Controller
{
    public function registro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarRegistro();
        } else {
            $this->mostrarFormularioRegistro();
        }
    }

    private function mostrarFormularioRegistro(): void
    {
        $idiomaModel = $this->model('Idioma');
        $idiomas = $idiomaModel->listarTodos();

        $this->view('auth/registro', [
            'idiomas'     => $idiomas,
            'csrf_token'  => $this->generarTokenCsrf(),
            'errores'     => $_SESSION['errores'] ?? [],
            'old'         => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['old']);
    }

    private function procesarRegistro(): void
    {
        $errores = [];

        // CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        // Sanitizar entradas
        $nombre     = Sanitizer::nombrePropio(Sanitizer::texto($_POST['nombre'] ?? ''));
        $email      = Sanitizer::email($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $id_idioma  = Sanitizer::entero($_POST['id_idioma'] ?? 0);

        // Validar
        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (!Validator::email($email)) {
            $errores[] = 'Correo electrónico inválido.';
        }
        if (!Validator::longitud($password, 8, 100)) {
            $errores[] = $this->lang['password_length_error'];
        }

        // Validar que el idioma exista
        $idiomaModel = $this->model('Idioma');
        $idioma = $idiomaModel->buscarPorId($id_idioma);
        if (!$idioma) {
            $errores[] = 'Idioma seleccionado no válido.';
        }

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = ['nombre' => $nombre, 'email' => $email, 'id_idioma' => $id_idioma];
            $this->redirect('auth/registro');
            return;
        }

        // Verificar email duplicado
        $usuarioModel = $this->model('Usuario');
        if ($usuarioModel->buscarPorEmail($email) !== false) {
            $_SESSION['errores'] = [$this->lang['error_email_duplicado']];
            $_SESSION['old'] = ['nombre' => $nombre, 'email' => $email, 'id_idioma' => $id_idioma];
            $this->redirect('auth/registro');
            return;
        }

        // Hashear contraseña y crear usuario
        $hasher = new PasswordHasherService();
        $passwordHash = $hasher->procesar($password);

        $usuarioModel->crear([
            'nombre'        => $nombre,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'id_idioma'     => $id_idioma,
            'rol'           => 'cliente',
        ]);

        $_SESSION['exito'] = $this->lang['exito_registro'];
        $this->redirect('auth/login');
    }

    public function login(): void
    {
        if ($this->estaLogueado()) {
            $this->redirect('producto');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarLogin();
        } else {
            $this->mostrarFormularioLogin();
        }
    }

    private function mostrarFormularioLogin(): void
    {
        $this->view('auth/login', [
            'csrf_token' => $this->generarTokenCsrf(),
            'errores'    => $_SESSION['errores'] ?? [],
            'exito'      => $_SESSION['exito'] ?? '',
            'old'        => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['exito'], $_SESSION['old']);
    }

    private function procesarLogin(): void
    {
        $errores = [];

        // Validaciones previas a BD (evitar consultas innecesarias)
        $email    = Sanitizer::email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $token    = $_POST['csrf_token'] ?? '';

        if (empty($email) || empty($password)) {
            $errores[] = $this->lang['error_credenciales'];
            $_SESSION['errores'] = $errores;
            $this->redirect('auth/login');
            return;
        }

        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
            $_SESSION['errores'] = $errores;
            $this->redirect('auth/login');
            return;
        }

        // Buscar usuario
        $usuarioModel = $this->model('Usuario');
        $usuario = $usuarioModel->buscarPorEmail($email);

        if ($usuario === false) {
            // Mensaje genérico: no revelar si el email existe o no
            $errores[] = $this->lang['error_credenciales'];
            $_SESSION['errores'] = $errores;
            $this->redirect('auth/login');
            return;
        }

        // Verificar contraseña
        $hasher = new PasswordHasherService();
        if (!$hasher->verificar($password, $usuario['password_hash'])) {
            $errores[] = $this->lang['error_credenciales'];
            $_SESSION['errores'] = $errores;
            $this->redirect('auth/login');
            return;
        }

        // Iniciar sesión
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['id_usuario']    = (int) $usuario['id_usuario'];
        $_SESSION['nombre']        = $usuario['nombre'];
        $_SESSION['email']         = $usuario['email'];
        $_SESSION['rol']           = $usuario['rol'];
        $_SESSION['id_idioma']     = (int) $usuario['id_idioma'];
        $_SESSION['activo']        = (int) ($usuario['activo'] ?? 1);
        $_SESSION['bloqueado']     = (int) ($usuario['bloqueado'] ?? 0);
        if (($usuario['rol'] ?? '') !== 'cliente') {
            unset($_SESSION['carrito'], $_SESSION['checkout_key']);
        }

        // Cargar el código de idioma para evitar consultas en cada página
        $idiomaModel = $this->model('Idioma');
        $idioma = $idiomaModel->buscarPorId($usuario['id_idioma']);
        $_SESSION['idioma_codigo'] = $idioma ? $idioma['codigo'] : 'es';

        $this->redirect('producto');
    }

    public function logout(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            return;
        }
        if (!$this->verificarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            http_response_code(422);
            return;
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
        $this->redirect('auth/login');
    }

    private function estaLogueado(): bool
    {
        return isset($_SESSION['id_usuario']);
    }
}
