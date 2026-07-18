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

        $pagina = Sanitizer::texto($_POST['pagina'] ?? '');
        $idProducto = Sanitizer::entero($_POST['id_producto'] ?? 0);
        $idProducto = $idProducto > 0 ? $idProducto : null;

        $datos = [
            'pagina'      => $pagina,
            'id_producto' => $idProducto,
            'id_usuario'  => $_SESSION['id_usuario'] ?? null,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
        ];

        $model = $this->model('VisitaModel');
        $idVisita = $model->registrar($datos);
        echo json_encode(['id_visita' => $idVisita]);
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

        if ($idVisita <= 0 || $segundos < 0 || $segundos > 86400) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }

        $model = $this->model('VisitaModel');
        $model->actualizarTiempo($idVisita, $segundos);
        echo json_encode(['ok' => true]);
    }
}