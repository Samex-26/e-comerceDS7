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
        $inicioActual = date('Y-m-01 00:00:00');
        $inicioSiguiente = date('Y-m-01 00:00:00', strtotime('+1 month'));
        $inicioAnterior = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $visitasActual = $visitaModel->resumenPeriodo($inicioActual, $inicioSiguiente);
        $visitasPrevio = $visitaModel->resumenPeriodo($inicioAnterior, $inicioActual);
        $kpiVisitantes = (int) ($visitasActual['visitantes'] ?? 0);

        /* Variación de visitas vs mes anterior */
        $visitasAnterior = (int) ($visitasPrevio['visitantes'] ?? 0);
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
