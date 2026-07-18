<?php

class FacturaModel extends Model
{
    public function crear(int $idVenta, string $rutaPdf): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO facturas (id_venta, ruta_pdf, fecha_generacion)
             VALUES (:id_venta, :ruta_pdf, NOW())'
        );
        $stmt->execute([
            ':id_venta' => $idVenta,
            ':ruta_pdf' => $rutaPdf,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function buscarPorVenta(int $idVenta): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM facturas WHERE id_venta = :id_venta ORDER BY fecha_generacion DESC LIMIT 1'
        );
        $stmt->execute([':id_venta' => $idVenta]);
        return $stmt->fetch();
    }
}