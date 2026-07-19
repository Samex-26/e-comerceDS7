<?php

class PasswordResetToken extends Model
{
    public function crear(int $usuarioId, string $tokenHash, string $ip, int $solicitadoPor): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE password_reset_tokens SET usado = 1, used_at = NOW() WHERE usuario_id = :id AND usado = 0')->execute([':id' => $usuarioId]);
            $stmt = $this->db->prepare('INSERT INTO password_reset_tokens (usuario_id, token_hash, fecha_expiracion) VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE))');
            $stmt->execute([':id' => $usuarioId, ':hash' => $tokenHash]);
            $id = (int) $this->db->lastInsertId();
            $this->auditar($usuarioId, $solicitadoPor, 'solicitado', $ip);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function buscarValido(string $tokenHash): array|false
    {
        $stmt = $this->db->prepare('SELECT t.*, u.email, u.nombre, u.activo FROM password_reset_tokens t JOIN usuarios u ON u.id_usuario = t.usuario_id WHERE t.token_hash = :hash AND t.usado = 0 AND t.fecha_expiracion > NOW() LIMIT 1');
        $stmt->execute([':hash' => $tokenHash]);
        return $stmt->fetch();
    }

    public function consumir(int $tokenId, int $usuarioId, string $passwordHash, string $ip): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('UPDATE password_reset_tokens SET usado = 1, used_at = NOW() WHERE id = :token AND usuario_id = :usuario AND usado = 0 AND fecha_expiracion > NOW()');
            $stmt->execute([':token' => $tokenId, ':usuario' => $usuarioId]);
            if ($stmt->rowCount() !== 1) { $this->db->rollBack(); return false; }
            $this->db->prepare('UPDATE usuarios SET password_hash = :hash, bloqueado = 0, intentos_fallidos = 0 WHERE id_usuario = :id')->execute([':hash' => $passwordHash, ':id' => $usuarioId]);
            $this->db->prepare('UPDATE password_reset_tokens SET usado = 1, used_at = COALESCE(used_at, NOW()) WHERE usuario_id = :id AND usado = 0')->execute([':id' => $usuarioId]);
            $this->db->prepare('DELETE FROM intentos_login WHERE email = (SELECT email FROM usuarios WHERE id_usuario = :id)')->execute([':id' => $usuarioId]);
            $this->auditar($usuarioId, null, 'completado', $ip);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function auditar(int $usuarioId, ?int $actorId, string $evento, string $ip): void
    {
        $stmt = $this->db->prepare('INSERT INTO password_reset_audit (usuario_id, solicitado_por, evento, ip) VALUES (:usuario, :actor, :evento, :ip)');
        $stmt->execute([':usuario' => $usuarioId, ':actor' => $actorId, ':evento' => $evento, ':ip' => substr($ip, 0, 45)]);
    }
}
