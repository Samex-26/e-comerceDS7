<?php
// Clase base abstracta para todos los controladores.
// Proporciona métodos para cargar vistas (con idioma), modelos, redireccionar y CSRF.

abstract class Controller
{
    protected array $lang = [];

    public function __construct()
    {
        $this->lang = IdiomaHelper::cargar();
    }

    protected function view(string $viewName, array $data = []): void
    {
        // Poner el array de idioma disponible en toda vista
        $data['lang'] = $this->lang;
        $data['csrf_token'] = $data['csrf_token'] ?? $this->generarTokenCsrf();

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

    protected function requerirPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Método no permitido.');
        }
    }

    protected function requerirCsrf(): void
    {
        $this->requerirPost();
        if (!$this->verificarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            http_response_code(403);
            exit('Solicitud inválida.');
        }
    }

    protected function requerirAdmin(): void
    {
        if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? '') !== 'admin') {
            $_SESSION['errores'] = [$this->lang['acceso_denegado'] ?? 'Acceso denegado.'];
            $this->redirect('auth/login');
        }
    }

    protected function requerirClienteActivo(string $destino = 'carrito/ver'): void
    {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['redirect_after_login'] = $destino;
            $_SESSION['errores'] = ['Debe iniciar sesión como cliente para usar el carrito y realizar compras.'];
            $this->redirect('auth/login');
        }
        $usuario = $this->model('Usuario')->buscarPorId((int) $_SESSION['id_usuario']);
        if (!$usuario || !(bool) $usuario['activo'] || (bool) $usuario['bloqueado'] || $usuario['rol'] !== 'cliente') {
            unset($_SESSION['carrito']);
            http_response_code(403);
            header('Content-Type: text/plain; charset=UTF-8');
            exit('Acceso denegado. El carrito y el proceso de compra son exclusivos para clientes activos.');
        }
    }
}
