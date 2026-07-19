<?php

class ReporteController extends Controller
{

    public function index(): void
    {
        $this->verificarAdmin();

        $ventaModel  = $this->model('VentaModel');
        $visitaModel = $this->model('VisitaModel');

        $desde = Sanitizer::texto($_GET['desde'] ?? '');
        $hasta = Sanitizer::texto($_GET['hasta'] ?? '');

        if ($desde !== '' && !Validator::noVacio($desde)) {
            $desde = '';
        }
        if ($hasta !== '' && !Validator::noVacio($hasta)) {
            $hasta = '';
        }

        if ($desde !== '') {
            $ts = strtotime($desde);
            if ($ts === false) {
                $desde = '';
            } else {
                $desde = date('Y-m-d', $ts);
            }
        }
        if ($hasta !== '') {
            $ts = strtotime($hasta);
            if ($ts === false) {
                $hasta = '';
            } else {
                $hasta = date('Y-m-d', $ts);
            }
        }

        if ($desde === '' || $hasta === '') {
            $hasta = date('Y-m-d');
            $desde = date('Y-m-d', strtotime('-30 days'));
        }

        if ($desde > $hasta) {
            $tmp    = $desde;
            $desde  = $hasta;
            $hasta  = $tmp;
        }

        $resumen       = $ventaModel->resumenPorRango($desde, $hasta);
        $topProductos  = $ventaModel->topProductosVendidos($desde, $hasta, 5);
        $topMas        = $visitaModel->topMasVisitados($desde, $hasta, 10);
        $topMenos      = $visitaModel->topMenosVisitados($desde, $hasta, 10);

        $this->view('reporte/index', [
            'desde'          => $desde,
            'hasta'          => $hasta,
            'resumen'        => $resumen,
            'topProductos'   => $topProductos,
            'topMas'         => $topMas,
            'topMenos'       => $topMenos,
        ]);
    }
}