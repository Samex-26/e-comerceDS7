<?php

class VisitaController extends Controller
{
    public function registrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        if (($_COOKIE['analytics_consent'] ?? '') !== 'accepted') {
            http_response_code(403);
            echo json_encode(['error' => 'Analytics consent required']);
            return;
        }

        $pagina = Sanitizer::texto($_POST['pagina'] ?? '');
        $idProducto = Sanitizer::entero($_POST['id_producto'] ?? 0);
        $idProducto = $idProducto > 0 ? $idProducto : null;
        $ruta = (string) parse_url($pagina, PHP_URL_PATH);
        if (!preg_match('#/(public/?)?$|/producto/?$|/producto/detalle/\d+/?$#', $ruta)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid page']);
            return;
        }
        if ($idProducto !== null && !$this->model('Producto')->buscarPorId($idProducto)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid product']);
            return;
        }
        $ahora = time();
        if (($ahora - (int) ($_SESSION['ultima_visita_registrada'] ?? 0)) < 2) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests']);
            return;
        }
        $_SESSION['ultima_visita_registrada'] = $ahora;

        $datos = [
            'pagina'      => $pagina,
            'id_producto' => $idProducto,
            'id_usuario'  => $_SESSION['id_usuario'] ?? null,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
        ];

        $model = $this->model('VisitaModel');
        $idVisita = $model->registrar($datos);
        $token = bin2hex(random_bytes(32));
        $_SESSION['visitas_tokens'][$idVisita] = ['token' => $token, 'creada' => $ahora, 'actualizada' => false];
        echo json_encode(['id_visita' => $idVisita, 'token' => $token]);
    }

    public function actualizarTiempo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $idVisita = (int) ($_POST['id_visita'] ?? 0);
        $segundos = (int) ($_POST['tiempo_segundos'] ?? 0);
        $token = (string) ($_POST['token'] ?? '');

        if ($idVisita <= 0 || $segundos < 0 || $segundos > 86400) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }
        $visitaSesion = $_SESSION['visitas_tokens'][$idVisita] ?? null;
        if (!$visitaSesion || !hash_equals($visitaSesion['token'], $token) || $visitaSesion['actualizada'] || time() - $visitaSesion['creada'] > 86400) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid visit token']);
            return;
        }

        $model = $this->model('VisitaModel');
        $model->actualizarTiempo($idVisita, $segundos);
        $_SESSION['visitas_tokens'][$idVisita]['actualizada'] = true;
        echo json_encode(['ok' => true]);
    }
}
