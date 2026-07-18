<?php

class VentaModel extends Model
{
    public function crear(array $datosVenta, array $detalle): int
    {
        $this->db->beginTransaction();
        try {
            foreach ($detalle as $linea) {
                $stmtCheck = $this->db->prepare(
                    'SELECT cantidad FROM productos WHERE id_producto = :id FOR UPDATE'
                );
                $stmtCheck->execute([':id' => $linea['id_producto']]);
                $stock = (int) $stmtCheck->fetchColumn();

                if ($stock < $linea['cantidad']) {
                    throw new \RuntimeException(
                        "Stock insuficiente para el producto ID {$linea['id_producto']}. Disponible: $stock, solicitado: {$linea['cantidad']}"
                    );
                }
            }

            $stmtVenta = $this->db->prepare(
                'INSERT INTO ventas (id_usuario, total, hash_datos, firma_digital, estado)
                 VALUES (:id_usuario, :total, :hash_datos, :firma_digital, :estado)'
            );
            $stmtVenta->execute([
                ':id_usuario'    => $datosVenta['id_usuario'],
                ':total'         => $datosVenta['total'],
                ':hash_datos'    => $datosVenta['hash_datos'],
                ':firma_digital' => $datosVenta['firma_digital'],
                ':estado'        => $datosVenta['estado'] ?? 'confirmada',
            ]);
            $idVenta = (int) $this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare(
                'INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal)
                 VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal)'
            );

            $stmtStock = $this->db->prepare(
                'UPDATE productos SET cantidad = cantidad - :cantidad WHERE id_producto = :id_producto'
            );

            foreach ($detalle as $linea) {
                $stmtDetalle->execute([
                    ':id_venta'       => $idVenta,
                    ':id_producto'    => $linea['id_producto'],
                    ':cantidad'       => $linea['cantidad'],
                    ':precio_unitario'=> $linea['precio_unitario'],
                    ':subtotal'       => $linea['subtotal'],
                ]);

                $stmtStock->execute([
                    ':cantidad'    => $linea['cantidad'],
                    ':id_producto' => $linea['id_producto'],
                ]);
            }

            $this->db->commit();
            return $idVenta;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, u.nombre AS usuario_nombre, u.email AS usuario_email
             FROM ventas v
             JOIN usuarios u ON v.id_usuario = u.id_usuario
             WHERE v.id_venta = :id'
        );
        $stmt->execute([':id' => $id]);
        $venta = $stmt->fetch();
        if (!$venta) {
            return false;
        }

        $stmtDet = $this->db->prepare(
            'SELECT dv.*, p.nombre AS producto_nombre
             FROM detalle_ventas dv
             JOIN productos p ON dv.id_producto = p.id_producto
             WHERE dv.id_venta = :id'
        );
        $stmtDet->execute([':id' => $id]);
        $venta['detalle'] = $stmtDet->fetchAll();

        return $venta;
    }

    public function listarPorUsuario(int $idUsuario): array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, COUNT(dv.id_detalle) AS lineas
             FROM ventas v
             LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
             WHERE v.id_usuario = :id_usuario
             GROUP BY v.id_venta
             ORDER BY v.fecha DESC'
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }
}
