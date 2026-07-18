<?php

class VisitaModel extends Model
{
    public function registrar(array $datos): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO visitas (id_producto, pagina, id_usuario, ip, tiempo_segundos, fecha)
             VALUES (:id_producto, :pagina, :id_usuario, :ip, 0, NOW())'
        );
        $stmt->execute([
            ':id_producto' => $datos['id_producto'] ?? null,
            ':pagina'      => $datos['pagina'] ?? '',
            ':id_usuario'  => $datos['id_usuario'] ?? null,
            ':ip'          => $datos['ip'] ?? '',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizarTiempo(int $idVisita, int $segundos): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE visitas SET tiempo_segundos = :segundos WHERE id_visita = :id'
        );
        $stmt->execute([
            ':segundos' => $segundos,
            ':id'       => $idVisita,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function totalVisitas(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM visitas');
        return (int) $stmt->fetchColumn();
    }

    public function topMasVisitados(?string $fechaInicio = null, ?string $fechaFin = null, int $limite = 10): array
    {
        $sql = 'SELECT p.id_producto, p.nombre, COUNT(v.id_visita) AS visitas
                FROM visitas v
                JOIN productos p ON v.id_producto = p.id_producto
                WHERE v.id_producto IS NOT NULL';
        $params = [':limite' => $limite];

        if ($fechaInicio !== null) {
            $sql .= ' AND v.fecha >= :inicio';
            $params[':inicio'] = $fechaInicio;
        }
        if ($fechaFin !== null) {
            $sql .= ' AND v.fecha <= :fin';
            $params[':fin'] = $fechaFin . ' 23:59:59';
        }

        $sql .= ' GROUP BY p.id_producto, p.nombre ORDER BY visitas DESC LIMIT :limite';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function topMenosVisitados(?string $fechaInicio = null, ?string $fechaFin = null, int $limite = 10): array
    {
        $sql = 'SELECT p.id_producto, p.nombre, COUNT(v.id_visita) AS visitas
                FROM visitas v
                JOIN productos p ON v.id_producto = p.id_producto
                WHERE v.id_producto IS NOT NULL';
        $params = [':limite' => $limite];

        if ($fechaInicio !== null) {
            $sql .= ' AND v.fecha >= :inicio';
            $params[':inicio'] = $fechaInicio;
        }
        if ($fechaFin !== null) {
            $sql .= ' AND v.fecha <= :fin';
            $params[':fin'] = $fechaFin . ' 23:59:59';
        }

        $sql .= ' GROUP BY p.id_producto, p.nombre
                  HAVING COUNT(v.id_visita) > 0
                  ORDER BY visitas ASC LIMIT :limite';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function visitasPorMes(): array
    {
        $stmt = $this->db->query(
            "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    COUNT(*) AS total_visitas
             FROM visitas
             GROUP BY mes
             ORDER BY mes ASC"
        );
        return $stmt->fetchAll();
    }
}