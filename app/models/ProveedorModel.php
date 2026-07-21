<?php

class ProveedorModel extends Model
{
    public function listarTodos(): array
    {
        $stmt = $this->db->query('SELECT * FROM proveedores ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM proveedores WHERE id_proveedor = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO proveedores (nombre, telefono, celular, direccion, url_web, calificacion_estrellas)
             VALUES (:nombre, :telefono, :celular, :direccion, :url_web, :calificacion_estrellas)'
        );
        $stmt->execute([
            ':nombre'                => $datos['nombre'],
            ':telefono'              => $datos['telefono'] ?? '',
            ':celular'               => $datos['celular'] ?? '',
            ':direccion'             => $datos['direccion'] ?? '',
            ':url_web'               => $datos['url_web'] ?? '',
            ':calificacion_estrellas'=> $datos['calificacion_estrellas'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE proveedores SET
                nombre = :nombre,
                telefono = :telefono,
                celular = :celular,
                direccion = :direccion,
                url_web = :url_web,
                calificacion_estrellas = :calificacion_estrellas
             WHERE id_proveedor = :id'
        );
        $stmt->execute([
            ':id'                    => $id,
            ':nombre'                => $datos['nombre'],
            ':telefono'              => $datos['telefono'] ?? '',
            ':celular'               => $datos['celular'] ?? '',
            ':direccion'             => $datos['direccion'] ?? '',
            ':url_web'               => $datos['url_web'] ?? '',
            ':calificacion_estrellas'=> $datos['calificacion_estrellas'] ?? 0,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM proveedores WHERE id_proveedor = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }

    public function tieneInventario(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM inventario WHERE id_proveedor = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }
}
