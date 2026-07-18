<?php
// Modelo de la entidad productos

class Producto extends Model
{
    public function listarActivos(?int $idCategoria = null): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                JOIN categorias c ON p.id_categoria = c.id_categoria
                WHERE p.activo = 1';

        $params = [];
        if ($idCategoria !== null) {
            $sql .= ' AND p.id_categoria = :id_categoria';
            $params[':id_categoria'] = $idCategoria;
        }

        $sql .= ' ORDER BY p.nombre ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.nombre AS categoria_nombre
             FROM productos p
             JOIN categorias c ON p.id_categoria = c.id_categoria
             WHERE p.id_producto = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO productos (nombre, descripcion, imagen, precio, precio_oferta, costo, cantidad, id_categoria, activo)
             VALUES (:nombre, :descripcion, :imagen, :precio, :precio_oferta, :costo, :cantidad, :id_categoria, 1)'
        );
        $stmt->execute([
            ':nombre'        => $datos['nombre'],
            ':descripcion'   => $datos['descripcion'] ?? '',
            ':imagen'        => $datos['imagen'] ?? '',
            ':precio'        => $datos['precio'],
            ':precio_oferta' => !empty($datos['precio_oferta']) ? $datos['precio_oferta'] : null,
            ':costo'         => $datos['costo'] ?? 0,
            ':cantidad'      => $datos['cantidad'] ?? 0,
            ':id_categoria'  => $datos['id_categoria'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE productos SET
                    nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    precio_oferta = :precio_oferta,
                    costo = :costo,
                    cantidad = :cantidad,
                    id_categoria = :id_categoria';

        $params = [
            ':id'            => $id,
            ':nombre'        => $datos['nombre'],
            ':descripcion'   => $datos['descripcion'] ?? '',
            ':precio'        => $datos['precio'],
            ':precio_oferta' => !empty($datos['precio_oferta']) ? $datos['precio_oferta'] : null,
            ':costo'         => $datos['costo'] ?? 0,
            ':cantidad'      => $datos['cantidad'],
            ':id_categoria'  => $datos['id_categoria'],
        ];

        if (!empty($datos['imagen'])) {
            $sql .= ', imagen = :imagen';
            $params[':imagen'] = $datos['imagen'];
        }

        $sql .= ' WHERE id_producto = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function eliminar(int $id): bool
    {
        // Borrado lógico: desactiva el producto sin eliminar el registro
        $stmt = $this->db->prepare('UPDATE productos SET activo = 0 WHERE id_producto = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
