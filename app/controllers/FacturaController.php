<?php

class FacturaController extends Controller
{
    private function requiereSesion(): void
    {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['errores'] = ['Debe iniciar sesión para descargar facturas.'];
            $this->redirect('auth/login');
        }
    }

    public function generar(int $idVenta): void
    {
        $this->requiereSesion();

        $ventaModel = $this->model('VentaModel');
        $venta = $ventaModel->buscarPorId($idVenta);

        if (!$venta) {
            $_SESSION['errores'] = ['Venta no encontrada.'];
            $this->redirect('producto');
            return;
        }

        $esPropietario = (int) $venta['id_usuario'] === (int) $_SESSION['id_usuario'];
        $esAdmin = ($_SESSION['rol'] ?? '') === 'admin';

        if (!$esPropietario && !$esAdmin) {
            $_SESSION['errores'] = ['No tiene permiso para descargar esta factura.'];
            $this->redirect('producto');
            return;
        }

        $facturaModel = $this->model('FacturaModel');

        $rutaRelativa = 'assets/facturas/factura_' . $idVenta . '.pdf';
        $rutaAbsoluta = BASE_PATH . '/../public/' . $rutaRelativa;

        $existente = $facturaModel->buscarPorVenta($idVenta);
        if ($existente && file_exists($rutaAbsoluta)) {
            $this->servirPDF($rutaAbsoluta, $idVenta);
            return;
        }

        $this->generarPDF($venta, $rutaAbsoluta, $rutaRelativa, $facturaModel, $idVenta);
    }

    private function generarPDF(array $venta, string $rutaAbsoluta, string $rutaRelativa, FacturaModel $facturaModel, int $idVenta): void
    {
        $dir = dirname($rutaAbsoluta);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('TiendaUTP');
        $pdf->SetAuthor('TiendaUTP');
        $pdf->SetTitle('Factura #UTP-' . str_pad((string) $idVenta, 5, '0', STR_PAD_LEFT));
        $pdf->SetSubject('Factura de compra');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        $pdf->SetFont('Helvetica', '', 10);

        $primary = '#1e293b';
        $secondary = '#fd761a';
        $muted = '#64748b';
        $border = '#e2e8f0';

        $pdf->SetFillColor(30, 41, 59);
        $pdf->SetTextColor(30, 41, 59);

        $pdf->SetFont('Helvetica', 'B', 22);
        $pdf->Cell(0, 14, 'TiendaUTP', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 6, 'Universidad Tecnologica de Panama', 0, 1, 'L');
        $pdf->Cell(0, 6, 'Factura electronica', 0, 1, 'L');
        $pdf->Ln(4);

        $pdf->SetDrawColor(226, 232, 240);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(6);

        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(253, 118, 26);
        $pdf->Cell(0, 10, 'FACTURA', 0, 1, 'R');

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(100, 116, 139);
        $numFactura = '#UTP-' . str_pad((string) $idVenta, 5, '0', STR_PAD_LEFT);
        $pdf->Cell(0, 5, 'Factura ' . $numFactura, 0, 1, 'R');
        $pdf->Cell(0, 5, 'Fecha: ' . date('d/m/Y H:i', strtotime($venta['fecha'])), 0, 1, 'R');
        $pdf->Ln(8);

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 7, 'DATOS DEL CLIENTE', 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(0, 5, 'Nombre: ' . htmlspecialchars($venta['usuario_nombre'] ?? ''), 0, 1, 'L');
        $pdf->Cell(0, 5, 'Email: ' . htmlspecialchars($venta['usuario_email'] ?? ''), 0, 1, 'L');
        $pdf->Ln(6);

        $pdf->SetDrawColor(226, 232, 240);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 7, 'PRODUCTOS', 0, 1, 'L');
        $pdf->Ln(2);

        $header = ['Producto', 'Cant.', 'P. Unitario', 'Subtotal'];
        $w = [90, 20, 40, 40];

        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetFillColor(30, 41, 59);
        $pdf->SetTextColor(255, 255, 255);
        for ($i = 0; $i < count($header); $i++) {
            $pdf->Cell($w[$i], 8, $header[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(30, 41, 59);
        $fill = false;

        if (!empty($venta['detalle'])) {
            foreach ($venta['detalle'] as $item) {
                $pdf->SetFillColor(247, 249, 251);
                $nombre = htmlspecialchars($item['producto_nombre'] ?? '');
                if (strlen($nombre) > 45) {
                    $nombre = substr($nombre, 0, 42) . '...';
                }
                $pdf->Cell($w[0], 7, $nombre, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[1], 7, (string) (int) ($item['cantidad'] ?? 0), 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 7, '$' . number_format((float) ($item['precio_unitario'] ?? 0), 2, '.', ''), 'LR', 0, 'R', $fill);
                $pdf->Cell($w[3], 7, '$' . number_format((float) ($item['subtotal'] ?? 0), 2, '.', ''), 'LR', 0, 'R', $fill);
                $pdf->Ln();
                $fill = !$fill;
            }
        }

        $pdf->SetDrawColor(226, 232, 240);
        $pdf->Cell(array_sum($w), 0, '', 'T');
        $pdf->Ln();

        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell($w[0] + $w[1] + $w[2], 12, 'TOTAL', 'LR', 0, 'R');
        $pdf->SetTextColor(253, 118, 26);
        $pdf->Cell($w[3], 12, '$' . number_format((float) ($venta['total'] ?? 0), 2, '.', ''), 'LR', 0, 'R');
        $pdf->Ln();
        $pdf->Cell(array_sum($w), 0, '', 'T');
        $pdf->Ln(10);

        $pdf->SetDrawColor(226, 232, 240);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(4);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 6, 'Verificacion de Integridad', 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Courier', '', 7);
        $pdf->SetTextColor(148, 163, 184);
        $pdf->MultiCell(0, 4, 'Hash: ' . ($venta['hash_datos'] ?? '—'), 0, 'L');
        $pdf->MultiCell(0, 4, 'Firma: ' . ($venta['firma_digital'] ?? '—'), 0, 'L');
        $pdf->Ln(4);

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(148, 163, 184);
        $pdf->Cell(0, 5, 'Gracias por su compra', 0, 1, 'C');
        $pdf->Cell(0, 5, 'TiendaUTP - Universidad Tecnologica de Panama', 0, 1, 'C');

        $pdf->Output($rutaAbsoluta, 'F');

        $facturaModel->crear($idVenta, $rutaRelativa);

        $this->servirPDF($rutaAbsoluta, $idVenta);
    }

    private function servirPDF(string $rutaAbsoluta, int $idVenta): void
    {
        if (!file_exists($rutaAbsoluta)) {
            $_SESSION['errores'] = ['El archivo de factura no se encuentra en el servidor.'];
            $this->redirect('venta/historial');
            return;
        }

        $numFactura = 'UTP-' . str_pad((string) $idVenta, 5, '0', STR_PAD_LEFT);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="factura_' . $numFactura . '.pdf"');
        header('Content-Length: ' . filesize($rutaAbsoluta));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($rutaAbsoluta);
        exit;
    }
}