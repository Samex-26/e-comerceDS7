<?php
// Modelo de la entidad usuarios

class Usuario extends Model
{
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, id_idioma, rol, activo, created_at)
             VALUES (:nombre, :email, :password_hash, :id_idioma, :rol, :activo, NOW())'
        );
        $stmt->execute([
            ':nombre'        => $datos['nombre'],
            ':email'         => $datos['email'],
            ':password_hash' => $datos['password_hash'],
            ':id_idioma'     => $datos['id_idioma'],
            ':rol'           => $datos['rol'] ?? 'cliente',
            ':activo'        => $datos['activo'] ?? 1,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function buscarPorEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id_usuario = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function listarTodos(): array
    {
        $stmt = $this->db->query(
            'SELECT u.id_usuario, u.nombre, u.email, u.id_idioma, u.rol, u.activo,
                    u.bloqueado, u.intentos_fallidos, u.created_at, u.updated_at,
                    i.nombre AS idioma_nombre
             FROM usuarios u JOIN idiomas i ON i.id_idioma = u.id_idioma
             ORDER BY u.nombre ASC'
        );
        return $stmt->fetchAll();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE usuarios SET nombre = :nombre, email = :email,
                id_idioma = :id_idioma, rol = :rol';
        $params = [
            ':id' => $id,
            ':nombre' => $datos['nombre'],
            ':email' => $datos['email'],
            ':id_idioma' => $datos['id_idioma'],
            ':rol' => $datos['rol'],
        ];
        if (!empty($datos['password_hash'])) {
            $sql .= ', password_hash = :password_hash';
            $params[':password_hash'] = $datos['password_hash'];
        }
        $sql .= ' WHERE id_usuario = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET activo = :activo, bloqueado = 0, intentos_fallidos = 0
             WHERE id_usuario = :id'
        );
        $stmt->execute([':activo' => $activo ? 1 : 0, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function contarAdministradoresActivos(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM usuarios WHERE rol = 'admin' AND activo = 1 AND bloqueado = 0"
        )->fetchColumn();
    }

    public function registrarIntento(string $email, string $ip, string $resultado): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO intentos_login (email, ip, resultado) VALUES (:email, :ip, :resultado)'
        );
        $stmt->execute([':email' => $email, ':ip' => $ip, ':resultado' => $resultado]);
    }

    public function registrarFallo(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET intentos_fallidos = intentos_fallidos + 1,
                 bloqueado = IF(intentos_fallidos + 1 >= 3, 1, bloqueado)
             WHERE id_usuario = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    public function restablecerIntentos(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET intentos_fallidos = 0, bloqueado = 0 WHERE id_usuario = :id'
        );
        $stmt->execute([':id' => $id]);
    }
}
