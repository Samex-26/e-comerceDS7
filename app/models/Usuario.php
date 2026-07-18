<?php
// Modelo de la entidad usuarios

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
}
