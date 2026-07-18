<?php

class InventarioModel extends Model
{
    public function listarTodos(): array
    {
        $stmt = $this->db->query(
            'SELECT i.*, p.nombre AS producto_nombre, pr.nombre AS proveedor_nombre
             FROM inventario i
             JOIN productos p ON i.id_producto = p.id_producto
             JOIN proveedores pr ON i.id_proveedor = pr.id_proveedor
             ORDER BY i.fecha_entrada DESC, i.id_inventario DESC'
        );
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, p.nombre AS producto_nombre, pr.nombre AS proveedor_nombre
             FROM inventario i
             JOIN productos p ON i.id_producto = p.id_producto
             JOIN proveedores pr ON i.id_proveedor = pr.id_proveedor
             WHERE i.id_inventario = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $datos): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO inventario (id_producto, id_proveedor, cantidad_ingresada, costo_unitario, fecha_entrada, detalle)
                 VALUES (:id_producto, :id_proveedor, :cantidad_ingresada, :costo_unitario, :fecha_entrada, :detalle)'
            );
            $stmt->execute([
                ':id_producto'        => $datos['id_producto'],
                ':id_proveedor'       => $datos['id_proveedor'],
                ':cantidad_ingresada' => $datos['cantidad_ingresada'],
                ':costo_unitario'     => $datos['costo_unitario'] ?? 0,
                ':fecha_entrada'      => $datos['fecha_entrada'],
                ':detalle'           => $datos['detalle'] ?? '',
            ]);

            $id = (int) $this->db->lastInsertId();

            $stmtStock = $this->db->prepare(
                'UPDATE productos SET cantidad = cantidad + :cantidad WHERE id_producto = :id_producto'
            );
            $stmtStock->execute([
                ':cantidad'    => $datos['cantidad_ingresada'],
                ':id_producto' => $datos['id_producto'],
            ]);

            $this->db->commit();
            return $id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $datos): bool
    {
        $viejo = $this->buscarPorId($id);
        if (!$viejo) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            if ((int) $datos['id_producto'] !== (int) $viejo['id_producto']) {
                $stmtRevert = $this->db->prepare(
                    'UPDATE productos SET cantidad = GREATEST(0, cantidad - :cantidad) WHERE id_producto = :id_producto'
                );
                $stmtRevert->execute([
                    ':cantidad'    => $viejo['cantidad_ingresada'],
                    ':id_producto' => $viejo['id_producto'],
                ]);

                $stmtApply = $this->db->prepare(
                    'UPDATE productos SET cantidad = cantidad + :cantidad WHERE id_producto = :id_producto'
                );
                $stmtApply->execute([
                    ':cantidad'    => $datos['cantidad_ingresada'],
                    ':id_producto' => $datos['id_producto'],
                ]);
            } else {
                $diferencia = $datos['cantidad_ingresada'] - $viejo['cantidad_ingresada'];
                $stmtStock = $this->db->prepare(
                    'UPDATE productos SET cantidad = GREATEST(0, cantidad + :diferencia) WHERE id_producto = :id_producto'
                );
                $stmtStock->execute([
                    ':diferencia'  => $diferencia,
                    ':id_producto' => $datos['id_producto'],
                ]);
            }

            $stmt = $this->db->prepare(
                'UPDATE inventario SET
                    id_producto = :id_producto,
                    id_proveedor = :id_proveedor,
                    cantidad_ingresada = :cantidad_ingresada,
                    costo_unitario = :costo_unitario,
                    fecha_entrada = :fecha_entrada,
                    detalle = :detalle
                 WHERE id_inventario = :id'
            );
            $stmt->execute([
                ':id'                => $id,
                ':id_producto'       => $datos['id_producto'],
                ':id_proveedor'      => $datos['id_proveedor'],
                ':cantidad_ingresada'=> $datos['cantidad_ingresada'],
                ':costo_unitario'    => $datos['costo_unitario'] ?? 0,
                ':fecha_entrada'     => $datos['fecha_entrada'],
                ':detalle'           => $datos['detalle'] ?? '',
            ]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function eliminar(int $id): bool
    {
        $viejo = $this->buscarPorId($id);
        if (!$viejo) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $stmtStock = $this->db->prepare(
                'UPDATE productos SET cantidad = GREATEST(0, cantidad - :cantidad) WHERE id_producto = :id_producto'
            );
            $stmtStock->execute([
                ':cantidad'    => $viejo['cantidad_ingresada'],
                ':id_producto' => $viejo['id_producto'],
            ]);

            $stmt = $this->db->prepare('DELETE FROM inventario WHERE id_inventario = :id');
            $stmt->execute([':id' => $id]);

            $this->db->commit();
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
