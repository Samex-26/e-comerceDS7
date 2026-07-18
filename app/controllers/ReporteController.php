<?php

class ReporteController extends Controller
{
    private function verificarAdmin(): void
    {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['errores'] = [$this->lang['acceso_denegado']];
            $this->redirect('auth/login');
        }
    }

    public function index(): void
    {
        $this->verificarAdmin();
        $this->view('reporte/index');
    }
}