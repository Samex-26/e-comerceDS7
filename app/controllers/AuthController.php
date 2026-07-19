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
        if (!Validator::longitud($password, 8, 12)) {
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

        $_SESSION['mensaje_exito'] = $this->lang['exito_registro'];
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
            'exito'      => $_SESSION['mensaje_exito'] ?? '',
            'old'        => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['mensaje_exito'], $_SESSION['old']);
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
        $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
        $usuarioModel = $this->model('Usuario');
        $usuario = $usuarioModel->buscarPorEmail($email);

        if ($usuario === false) {
            $usuarioModel->registrarIntento($email, $ip, 'fallido');
            // Mensaje genérico: no revelar si el email existe o no
            $errores[] = $this->lang['error_credenciales'];
            $_SESSION['errores'] = $errores;
            $this->redirect('auth/login');
            return;
        }

        // Verificar contraseña
        if (!(bool) $usuario['activo']) {
            $usuarioModel->registrarIntento($email, $ip, 'inactivo');
            $_SESSION['errores'] = [$this->lang['error_credenciales']];
            $this->redirect('auth/login');
            return;
        }

        if ((bool) $usuario['bloqueado']) {
            $usuarioModel->registrarIntento($email, $ip, 'bloqueado');
            $_SESSION['errores'] = [$this->lang['error_credenciales']];
            $this->redirect('auth/login');
            return;
        }

        $hasher = new PasswordHasherService();
        if (!$hasher->verificar($password, $usuario['password_hash'])) {
            $usuarioModel->registrarFallo((int) $usuario['id_usuario']);
            $usuarioModel->registrarIntento($email, $ip, 'fallido');
            $errores[] = $this->lang['error_credenciales'];
            $_SESSION['errores'] = $errores;
            $this->redirect('auth/login');
            return;
        }

        // Iniciar sesión
        $usuarioModel->restablecerIntentos((int) $usuario['id_usuario']);
        $usuarioModel->registrarIntento($email, $ip, 'exitoso');
        session_regenerate_id(true);

        $_SESSION['id_usuario']    = (int) $usuario['id_usuario'];
        $_SESSION['nombre']        = $usuario['nombre'];
        $_SESSION['rol']           = $usuario['rol'];
        $_SESSION['id_idioma']     = (int) $usuario['id_idioma'];
        if ($usuario['rol'] === 'admin') {
            unset($_SESSION['carrito']);
        }

        // Cargar el código de idioma para evitar consultas en cada página
        $idiomaModel = $this->model('Idioma');
        $idioma = $idiomaModel->buscarPorId($usuario['id_idioma']);
        $_SESSION['idioma_codigo'] = $idioma ? $idioma['codigo'] : 'es';

        $destino = $_SESSION['redirect_after_login'] ?? 'producto';
        unset($_SESSION['redirect_after_login']);
        $this->redirect($destino);
    }

    public function logout(): void
    {
        $this->requerirCsrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        $this->redirect('auth/login');
    }

    public function restablecer(string $token = ''): void
    {
        $token = trim($token);
        $registro = $token !== '' ? $this->model('PasswordResetToken')->buscarValido(hash('sha256', $token)) : false;
        if (!$registro || !(bool) $registro['activo']) {
            http_response_code(400);
            $this->view('auth/restablecer', ['tokenValido' => false, 'errores' => ['El enlace no es válido, ya fue utilizado o ha vencido.']]);
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->requerirCsrf();
            $password = (string) ($_POST['password'] ?? '');
            $confirmacion = (string) ($_POST['password_confirmacion'] ?? '');
            $errores = [];
            if (!Validator::longitud($password, 8, 12)) $errores[] = 'La contraseña debe tener entre 8 y 12 caracteres.';
            if (!hash_equals($password, $confirmacion)) $errores[] = 'Las contraseñas no coinciden.';
            if ($errores) {
                $this->view('auth/restablecer', ['tokenValido' => true, 'token' => $token, 'errores' => $errores]);
                return;
            }
            $ok = $this->model('PasswordResetToken')->consumir((int) $registro['id'], (int) $registro['usuario_id'], (new PasswordHasherService())->procesar($password), (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
            $password = $confirmacion = '';
            if (!$ok) {
                $this->view('auth/restablecer', ['tokenValido' => false, 'errores' => ['El enlace dejó de ser válido. Solicite uno nuevo.']]);
                return;
            }
            if (isset($_SESSION['id_usuario']) && (int) $_SESSION['id_usuario'] === (int) $registro['usuario_id']) {
                unset($_SESSION['id_usuario'], $_SESSION['nombre'], $_SESSION['rol'], $_SESSION['id_idioma'], $_SESSION['idioma_codigo']);
                session_regenerate_id(true);
            }
            $_SESSION['mensaje_exito'] = 'Contraseña actualizada. Ya puede iniciar sesión.';
            $this->redirect('auth/login');
        }
        $this->view('auth/restablecer', ['tokenValido' => true, 'token' => $token, 'errores' => []]);
    }

    private function estaLogueado(): bool
    {
        return isset($_SESSION['id_usuario']);
    }
}
