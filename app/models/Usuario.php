<?php

class Usuario extends Model
{
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, id_idioma, rol, created_at)
             VALUES (:nombre, :email, :password_hash, :id_idioma, :rol, NOW())'
        );
        $stmt->execute([
            ':nombre'        => $datos['nombre'],
            ':email'         => $datos['email'],
            ':password_hash' => $datos['password_hash'],
            ':id_idioma'     => $datos['id_idioma'],
            ':rol'           => $datos['rol'] ?? 'cliente',
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
            'SELECT u.*, i.nombre AS idioma_nombre, i.codigo AS idioma_codigo
             FROM usuarios u
             LEFT JOIN idiomas i ON u.id_idioma = i.id_idioma
             ORDER BY u.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $params = [':id' => $id];

        if (isset($datos['nombre'])) {
            $campos[] = 'nombre = :nombre';
            $params[':nombre'] = $datos['nombre'];
        }
        if (isset($datos['email'])) {
            $campos[] = 'email = :email';
            $params[':email'] = $datos['email'];
        }
        if (isset($datos['id_idioma'])) {
            $campos[] = 'id_idioma = :id_idioma';
            $params[':id_idioma'] = $datos['id_idioma'];
        }
        if (isset($datos['rol'])) {
            $campos[] = 'rol = :rol';
            $params[':rol'] = $datos['rol'];
        }
        if (!empty($datos['password_hash'])) {
            $campos[] = 'password_hash = :password_hash';
            $params[':password_hash'] = $datos['password_hash'];
        }

        if (empty($campos)) {
            return false;
        }

        $sql = 'UPDATE usuarios SET ' . implode(', ', $campos) . ' WHERE id_usuario = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM usuarios WHERE id_usuario = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function contarAdmins(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'admin'");
        return (int) $stmt->fetch()['total'];
    }
}
