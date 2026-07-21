<?php

class CookieConsentimientoModel extends Model
{
    public function registrar(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cookies_consentimiento (id_usuario, aceptada, fecha)
             VALUES (:id_usuario, 1, NOW())'
        );
        $stmt->execute([
            ':id_usuario' => $datos['id_usuario'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }
}
