<?php

class DashboardController extends Controller
{

    public function index(): void
    {
        $this->verificarAdmin();

        $ventaModel  = $this->model('VentaModel');
        $visitaModel = $this->model('VisitaModel');

        /* ── KPIs del mes ── */
        $resumenMes            = $ventaModel->resumenMes();
        $kpiVentasConteo       = (int) ($resumenMes['total_ventas'] ?? 0);
        $kpiVentasSuma         = (float) ($resumenMes['suma_ventas'] ?? 0);
        $kpiGanancia           = $ventaModel->gananciaNetaMes();
        $kpiProductosVendidos  = $ventaModel->productosVendidosMes();
        $kpiVisitantes         = $visitaModel->totalVisitas();

        /* Variación de visitas vs mes anterior */
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM visitas
             WHERE MONTH(fecha) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
               AND YEAR(fecha) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)"
        );
        $stmt->execute();
        $visitasAnterior = (int) $stmt->fetchColumn();
        $variacionVisitas = $visitasAnterior > 0
            ? round(($kpiVisitantes - $visitasAnterior) / $visitasAnterior * 100, 1)
            : 0;

        /* ── Datos para gráficas ── */
        $topCategorias = $ventaModel->topCategorias(5);
        $ventasPorMes  = $ventaModel->ventasPorMes(6);
        $ultimasVentas = $ventaModel->ultimasVentas(5);

        $this->view('dashboard/index', [
            'kpiVentasConteo'      => $kpiVentasConteo,
            'kpiVentasSuma'        => $kpiVentasSuma,
            'kpiGanancia'          => $kpiGanancia,
            'kpiProductosVendidos' => $kpiProductosVendidos,
            'kpiVisitantes'        => $kpiVisitantes,
            'variacionVisitas'     => $variacionVisitas,
            'topCategorias'        => $topCategorias,
            'ventasPorMes'         => $ventasPorMes,
            'ultimasVentas'        => $ultimasVentas,
        ]);
    }
}