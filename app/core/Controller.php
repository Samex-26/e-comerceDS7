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
}
