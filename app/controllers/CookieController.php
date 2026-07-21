<?php

class CookieController extends Controller
{
    public function consentir(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $model = $this->model('CookieConsentimientoModel');
        $model->registrar([
            'id_usuario' => $_SESSION['id_usuario'] ?? null,
        ]);

        setcookie('cookie_consent', '1', time() + 365 * 86400, '/', '', false, false);

        echo json_encode(['ok' => true]);
    }
}
