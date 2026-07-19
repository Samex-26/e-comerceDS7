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
            'INSERT INTO proveedores (nombre, telefono, celular, email, direccion, ciudad, sitio_web, calificacion_estrellas, notas)
             VALUES (:nombre, :telefono, :celular, :email, :direccion, :ciudad, :sitio_web, :calificacion_estrellas, :notas)'
        );
        $stmt->execute([
            ':nombre'                => $datos['nombre'],
            ':telefono'              => $datos['telefono'] ?? '',
            ':celular'               => $datos['celular'] ?? '',
            ':email'                 => $datos['email'] ?? '',
            ':direccion'             => $datos['direccion'] ?? '',
            ':ciudad'                => $datos['ciudad'] ?? '',
            ':sitio_web'             => $datos['sitio_web'] ?? '',
            ':calificacion_estrellas'=> $datos['calificacion_estrellas'] ?? 0,
            ':notas'                => $datos['notas'] ?? '',
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
                email = :email,
                direccion = :direccion,
                ciudad = :ciudad,
                sitio_web = :sitio_web,
                calificacion_estrellas = :calificacion_estrellas,
                notas = :notas,
                activo = :activo
             WHERE id_proveedor = :id'
        );
        $stmt->execute([
            ':id'                    => $id,
            ':nombre'                => $datos['nombre'],
            ':telefono'              => $datos['telefono'] ?? '',
            ':celular'               => $datos['celular'] ?? '',
            ':email'                 => $datos['email'] ?? '',
            ':direccion'             => $datos['direccion'] ?? '',
            ':ciudad'                => $datos['ciudad'] ?? '',
            ':sitio_web'             => $datos['sitio_web'] ?? '',
            ':calificacion_estrellas'=> $datos['calificacion_estrellas'] ?? 0,
            ':notas'                => $datos['notas'] ?? '',
            ':activo'               => $datos['activo'] ?? 1,
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
