<?php

class IdiomaController extends Controller
{
    public function cambiar(string $codigo): void
    {
        if (!in_array($codigo, ['es', 'en'], true)) {
            http_response_code(404);
            return;
        }
        $idioma = $this->model('Idioma')->buscarPorCodigo($codigo);
        if (!$idioma) {
            http_response_code(404);
            return;
        }
        $_SESSION['idioma_codigo'] = $codigo;
        $_SESSION['id_idioma'] = (int) $idioma['id_idioma'];
        if (isset($_SESSION['id_usuario'])) {
            $this->model('Usuario')->actualizar((int) $_SESSION['id_usuario'], ['id_idioma' => (int) $idioma['id_idioma']]);
        }
        $destino = (string) ($_SERVER['HTTP_REFERER'] ?? BASE_URL);
        if (!str_starts_with($destino, BASE_URL)) {
            $destino = BASE_URL;
        }
        header('Location: ' . $destino);
        exit;
    }
}
