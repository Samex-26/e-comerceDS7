<?php
// Clase base abstracta para todos los controladores.
// Proporciona métodos para cargar vistas (con idioma), modelos, redireccionar y CSRF.

abstract class Controller
{
    protected array $lang = [];

    public function __construct()
    {
        $this->lang = IdiomaHelper::cargar();
        $this->generarTokenCsrf();
    }

    protected function view(string $viewName, array $data = []): void
    {
        // Poner el array de idioma disponible en toda vista
        $data['lang'] = $this->lang;

        extract($data);

        $viewFile = BASE_PATH . '/views/' . $viewName . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vista no encontrada: " . $viewFile);
        }

        require_once BASE_PATH . '/views/layouts/header.php';
        require_once BASE_PATH . '/views/layouts/nav.php';
        require $viewFile;
        require_once BASE_PATH . '/views/layouts/footer.php';
    }

    protected function model(string $modelName): object
    {
        $modelClass = ucfirst($modelName);
        $modelFile  = BASE_PATH . '/models/' . $modelClass . '.php';

        if (!file_exists($modelFile)) {
            throw new \RuntimeException("Modelo no encontrado: " . $modelFile);
        }

        require_once $modelFile;

        if (!class_exists($modelClass)) {
            throw new \RuntimeException("Clase modelo no existe: " . $modelClass);
        }

        return new $modelClass();
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function generarTokenCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verificarTokenCsrf(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    protected function verificarAdmin(): void
    {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['errores'] = [$this->lang['acceso_denegado']];
            $this->redirect('auth/login');
        }
    }

    protected function requiereClienteActivo(): array
    {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['errores'] = ['Debe iniciar sesión como cliente.'];
            $this->redirect('auth/login');
        }

        $usuario = $this->model('Usuario')->buscarPorId((int) $_SESSION['id_usuario']);
        if (!$usuario || (int) ($usuario['activo'] ?? 1) !== 1 || (int) ($usuario['bloqueado'] ?? 0) === 1) {
            unset($_SESSION['id_usuario'], $_SESSION['nombre'], $_SESSION['email'], $_SESSION['rol'], $_SESSION['activo'], $_SESSION['carrito'], $_SESSION['checkout_key']);
            http_response_code(403);
            $this->renderAccesoDenegado('La cuenta no está disponible o está bloqueada.');
            exit;
        }
        if (($usuario['rol'] ?? '') !== 'cliente') {
            unset($_SESSION['carrito'], $_SESSION['checkout_key']);
            $_SESSION['rol'] = $usuario['rol'];
            http_response_code(403);
            $this->renderAccesoDenegado('Esta operación está disponible únicamente para clientes.');
            exit;
        }

        $_SESSION['rol'] = 'cliente';
        $_SESSION['activo'] = 1;
        return $usuario;
    }

    /** Compatibilidad interna; las rutas nuevas deben usar requiereClienteActivo(). */
    protected function verificarCliente(): void { $this->requiereClienteActivo(); }

    protected function exigirMetodoPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo 'Método no permitido.';
            exit;
        }
    }

    protected function exigirCsrf(): void
    {
        if (!$this->verificarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            http_response_code(422);
            echo 'Token de seguridad inválido.';
            exit;
        }
    }

    protected function exigirPostConCsrf(): void
    {
        $this->exigirMetodoPost();
        $this->exigirCsrf();
    }

    private function renderAccesoDenegado(string $mensaje): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Acceso denegado</title>';
        echo '<body><main><h1>Acceso denegado</h1><p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a href="' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . '">Volver al catálogo</a></main></body></html>';
    }
}
