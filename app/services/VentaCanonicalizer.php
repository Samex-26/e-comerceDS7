<?php

final class VentaCanonicalizer
{
    public static function serializar(int $idUsuario, string $fecha, float $total, array $detalle): string
    {
        usort($detalle, fn(array $a, array $b) => ((int) $a['id_producto']) <=> ((int) $b['id_producto']));
        $normalizado = array_map(fn(array $d) => [
            'id_producto' => (int) $d['id_producto'],
            'cantidad' => (int) $d['cantidad'],
            'precio_unitario' => number_format((float) $d['precio_unitario'], 2, '.', ''),
            'subtotal' => number_format((float) $d['subtotal'], 2, '.', ''),
            'costo_unitario' => number_format((float) ($d['costo_unitario'] ?? 0), 2, '.', ''),
        ], $detalle);
        return json_encode([
            'version' => 2,
            'id_usuario' => $idUsuario,
            'fecha' => $fecha,
            'total' => number_format($total, 2, '.', ''),
            'detalle' => $normalizado,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
