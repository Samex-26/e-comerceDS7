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

        try {
            $model = $this->model('CookieConsentimientoModel');
            $model->registrar([
                'id_usuario' => $_SESSION['id_usuario'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('Cookie consent DB error: ' . $e->getMessage());
        }

        $_SESSION['cookie_consent'] = 1;

        echo json_encode(['ok' => true]);
    }
}
