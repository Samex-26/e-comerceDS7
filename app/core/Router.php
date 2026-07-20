<?php

class Router
{
    private string $url;

    public function __construct()
    {

        $url = '';

        if (isset($_GET['url']) && !empty($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
        } elseif (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
            $url = ltrim($_SERVER['PATH_INFO'], '/');
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $base = dirname($_SERVER['SCRIPT_NAME']);
            $base = $base === '/' ? '' : $base;
            $uri  = strtok($_SERVER['REQUEST_URI'], '?');
            $uri  = rtrim($uri, '/');
            if ($base && strpos($uri, $base) === 0) {
                $url = ltrim(substr($uri, strlen($base)), '/');
            } else {
                $url = ltrim($uri, '/');
            }
        }

        $this->url = $url;
    }

    public function dispatch(): void
    {
        $segments = !empty($this->url) ? explode('/', $this->url) : [];

        $isDefaultController = empty($segments[0]);

        $controllerName = $isDefaultController
            ? 'InicioController'
            : ucfirst(strtolower($segments[0])) . 'Controller';

        $action = !empty($segments[1])
            ? strtolower($segments[1])
            : ($isDefaultController ? 'index' : 'index');

        $params = array_slice($segments, 2);

        $controllerFile = BASE_PATH . '/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->notFound('Controlador no encontrado: ' . $controllerName);
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            $this->notFound('Clase controlador no existe: ' . $controllerName);
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            $this->notFound('Acción no encontrada: ' . $action . ' en ' . $controllerName);
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function notFound(string $message = ''): void
    {
        http_response_code(404);
        $lang = IdiomaHelper::cargar();
        require_once BASE_PATH . '/views/layouts/header.php';
        echo '<div class="container mt-5"><div class="alert alert-danger">';
        echo '<h2>Error 404 - Página no encontrada</h2>';
        if ($message !== '') error_log('Router 404: ' . $message);
        echo '<a href="' . BASE_URL . '" class="btn btn-primary">Volver al inicio</a>';
        echo '</div></div>';
        require_once BASE_PATH . '/views/layouts/footer.php';
    }
}
