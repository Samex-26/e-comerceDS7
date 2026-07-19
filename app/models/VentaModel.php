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
                'INSERT INTO ventas (id_usuario, fecha, total, hash_datos, firma_digital, estado)
                 VALUES (:id_usuario, :fecha, :total, :hash_datos, :firma_digital, :estado)'
            );
            $stmtVenta->execute([
                ':id_usuario'    => $datosVenta['id_usuario'],
                ':fecha'         => $datosVenta['fecha'],
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

    public function resumenMes(): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total_ventas,
                    COALESCE(SUM(total), 0) AS suma_ventas
             FROM ventas
             WHERE MONTH(fecha) = MONTH(CURRENT_DATE)
               AND YEAR(fecha) = YEAR(CURRENT_DATE)
               AND estado != 'anulada'"
        );
        $stmt->execute();
        return $stmt->fetch();
    }

    public function gananciaNetaMes(): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM((dv.precio_unitario - p.costo) * dv.cantidad), 0)
             FROM detalle_ventas dv
             JOIN ventas v ON dv.id_venta = v.id_venta
             JOIN productos p ON dv.id_producto = p.id_producto
             WHERE MONTH(v.fecha) = MONTH(CURRENT_DATE)
               AND YEAR(v.fecha) = YEAR(CURRENT_DATE)
               AND v.estado != 'anulada'"
        );
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }

    public function productosVendidosMes(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(dv.cantidad), 0)
             FROM detalle_ventas dv
             JOIN ventas v ON dv.id_venta = v.id_venta
             WHERE MONTH(v.fecha) = MONTH(CURRENT_DATE)
               AND YEAR(v.fecha) = YEAR(CURRENT_DATE)
               AND v.estado != 'anulada'"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function topCategorias(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id_categoria, c.nombre, SUM(dv.cantidad) AS total_vendido
             FROM detalle_ventas dv
             JOIN ventas v ON dv.id_venta = v.id_venta
             JOIN productos p ON dv.id_producto = p.id_producto
             JOIN categorias c ON p.id_categoria = c.id_categoria
             WHERE v.estado != 'anulada'
             GROUP BY c.id_categoria, c.nombre
             ORDER BY total_vendido DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function ventasPorMes(int $ultimosMeses = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    COUNT(*) AS total_ventas,
                    COALESCE(SUM(total), 0) AS suma_ventas
             FROM ventas
             WHERE fecha >= DATE_FORMAT(CURRENT_DATE - INTERVAL :meses MONTH, '%Y-%m-01')
               AND estado != 'anulada'
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmt->bindValue(':meses', $ultimosMeses - 1, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function ultimasVentas(int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.id_venta, v.total, v.fecha, v.estado, u.nombre AS cliente
             FROM ventas v
             JOIN usuarios u ON v.id_usuario = u.id_usuario
             WHERE v.estado != 'anulada'
             ORDER BY v.fecha DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function resumenPorRango(string $fechaInicio, string $fechaFin): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT v.id_venta) AS total_ventas,
                    COALESCE(SUM(dv.subtotal), 0) AS suma_ventas,
                    COALESCE(SUM(p.costo * dv.cantidad), 0) AS total_costos,
                    COALESCE(SUM((dv.precio_unitario - p.costo) * dv.cantidad), 0) AS ganancia_neta
             FROM ventas v
             JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
             JOIN productos p ON dv.id_producto = p.id_producto
             WHERE v.fecha >= :inicio AND v.fecha <= :fin
               AND v.estado != 'anulada'"
        );
        $stmt->execute([
            ':inicio' => $fechaInicio,
            ':fin'    => $fechaFin . ' 23:59:59',
        ]);
        return $stmt->fetch();
    }

    public function topProductosVendidos(string $fechaInicio, string $fechaFin, int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.id_producto, p.nombre, p.imagen,
                    SUM(dv.cantidad) AS total_cantidad,
                    SUM(dv.subtotal) AS total_monto
             FROM detalle_ventas dv
             JOIN ventas v ON dv.id_venta = v.id_venta
             JOIN productos p ON dv.id_producto = p.id_producto
             WHERE v.fecha >= :inicio AND v.fecha <= :fin
               AND v.estado != 'anulada'
             GROUP BY p.id_producto, p.nombre, p.imagen
             ORDER BY total_cantidad DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':inicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindValue(':fin', $fechaFin . ' 23:59:59', PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
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
