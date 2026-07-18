<?php
// Modelo de la entidad categorias

class Categoria extends Model
{
    public function listarTodas(): array
    {
        $stmt = $this->db->query('SELECT * FROM categorias ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM categorias WHERE id_categoria = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)'
        );
        $stmt->execute([
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] ?? '',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id'
        );
        $stmt->execute([
            ':id'          => $id,
            ':nombre'      => $datos['nombre'],
            ':descripcion' => $datos['descripcion'] ?? '',
        ]);
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categorias WHERE id_categoria = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function tieneProductos(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM productos WHERE id_categoria = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }
}
