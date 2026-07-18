<?php
// Modelo de la entidad idiomas

class Idioma extends Model
{
    public function listarTodos(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM idiomas ORDER BY nombre ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscarPorCodigo(string $codigo): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM idiomas WHERE codigo = :codigo');
        $stmt->execute([':codigo' => $codigo]);
        return $stmt->fetch();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM idiomas WHERE id_idioma = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
