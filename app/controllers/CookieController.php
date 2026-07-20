<?php

class CookieController extends Controller
{
    public function consentimiento(): void
    {
        $this->exigirPostConCsrf();
        $decision = (string) ($_POST['decision'] ?? '');
        if (!in_array($decision, ['accepted', 'rejected', 'revoked'], true)) {
            http_response_code(422);
            return;
        }
        $valor = $decision === 'accepted' ? 'accepted' : 'rejected';
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('analytics_consent', $valor, ['expires' => time() + 31536000, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
        if ($valor !== 'accepted') unset($_SESSION['visitas_tokens']);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'decision' => $valor]);
    }
}
