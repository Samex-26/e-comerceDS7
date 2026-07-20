<?php

class VentaController extends Controller
{
    private function requiereSesion(): void
    {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['redirect_after_login'] = 'venta/checkout';
            $_SESSION['errores'] = ['Debe iniciar sesión para continuar.'];
            $this->redirect('auth/login');
        }
    }

    public function checkout(): void
    {
        if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'POST'], true)) {
            http_response_code(405);
            header('Allow: GET, POST');
            return;
        }
        $this->requiereClienteActivo();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCheckout();
            return;
        }

        $carrito = $_SESSION['carrito'] ?? [];

        if (empty($carrito)) {
            $_SESSION['errores'] = ['El carrito está vacío.'];
            $this->redirect('carrito/ver');
            return;
        }

        $productoModel = $this->model('Producto');
        $items = [];
        $total = 0;

        foreach ($carrito as $idP => $item) {
            $producto = $productoModel->buscarPorId($idP);
            if (!$producto || !$producto['activo']) {
                unset($_SESSION['carrito'][$idP]);
                continue;
            }

            $precio = (!empty($producto['precio_oferta']) && $producto['precio_oferta'] > 0)
                ? (float) $producto['precio_oferta']
                : (float) $producto['precio'];

            $subtotal = $precio * $item['cantidad'];
            $total += $subtotal;

            $items[] = [
                'id_producto'    => $idP,
                'nombre'         => $producto['nombre'],
                'imagen'         => $producto['imagen'] ?? '',
                'cantidad'       => $item['cantidad'],
                'precio_unitario'=> $precio,
                'subtotal'       => $subtotal,
            ];
        }

        if (empty($items)) {
            $_SESSION['errores'] = ['El carrito está vacío o los productos ya no están disponibles.'];
            $this->redirect('carrito/ver');
            return;
        }

        $this->view('venta/checkout', [
            'items'       => $items,
            'total'       => $total,
            'errores'     => $_SESSION['errores'] ?? [],
            'csrf_token'  => $this->generarTokenCsrf(),
            'idempotency_key' => $_SESSION['checkout_key'] ??= bin2hex(random_bytes(32)),
        ]);
        unset($_SESSION['errores']);
    }

    private function procesarCheckout(): void
    {
        $carrito = $_SESSION['carrito'] ?? [];

        if (empty($carrito)) {
            $_SESSION['errores'] = ['El carrito está vacío.'];
            $this->redirect('carrito/ver');
            return;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $_SESSION['errores'] = [$this->lang['error_csrf']];
            $this->redirect('venta/checkout');
            return;
        }
        $idempotencyKey = (string) ($_POST['idempotency_key'] ?? '');
        if (!preg_match('/^[a-f0-9]{64}$/', $idempotencyKey) || !hash_equals((string) ($_SESSION['checkout_key'] ?? ''), $idempotencyKey)) {
            $_SESSION['errores'] = ['La solicitud de compra ya fue utilizada o no es válida.'];
            $this->redirect('venta/checkout');
            return;
        }

        $productoModel = $this->model('Producto');
        $detalle = [];
        $total = 0;

        foreach ($carrito as $idP => $item) {
            $producto = $productoModel->buscarPorId($idP);
            if (!$producto || !$producto['activo']) {
                $_SESSION['errores'] = ["El producto ID $idP ya no está disponible."];
                $this->redirect('carrito/ver');
                return;
            }

            if ((int) $producto['cantidad'] < $item['cantidad']) {
                $_SESSION['errores'] = [
                    "Stock insuficiente para '{$producto['nombre']}'. " .
                    "Disponible: {$producto['cantidad']}, solicitado: {$item['cantidad']}."
                ];
                $this->redirect('carrito/ver');
                return;
            }

            $precioReal = (!empty($producto['precio_oferta']) && $producto['precio_oferta'] > 0)
                ? (float) $producto['precio_oferta']
                : (float) $producto['precio'];

            $subtotal = $precioReal * $item['cantidad'];
            $total += $subtotal;

            $detalle[] = [
                'id_producto'    => $idP,
                'cantidad'       => $item['cantidad'],
                'precio_unitario'=> $precioReal,
                'subtotal'       => $subtotal,
                'costo_unitario' => (float) $producto['costo'],
            ];
        }

        $idUsuario = (int) $_SESSION['id_usuario'];
        $fecha = date('Y-m-d H:i:s');

        $datosCadena = VentaCanonicalizer::serializar($idUsuario, $fecha, $total, $detalle);

        $firmaService = new FirmaDigitalService();
        $hashDatos = hash('sha256', $datosCadena);
        $firmaDigital = $firmaService->procesar($datosCadena);

        $ventaModel = $this->model('VentaModel');

        try {
            $idVenta = $ventaModel->crear([
                'id_usuario'    => $idUsuario,
                'total'         => $total,
                'hash_datos'    => $hashDatos,
                'firma_digital' => $firmaDigital,
                'estado'        => 'confirmada',
                'fecha'         => $fecha,
                'idempotency_key' => $idempotencyKey,
                'firma_version' => 2,
            ], $detalle);

            unset($_SESSION['carrito'], $_SESSION['checkout_key']);
            $this->redirect('venta/exito/' . $idVenta);
        } catch (\RuntimeException $e) {
            $_SESSION['errores'] = [$e->getMessage()];
            $this->redirect('carrito/ver');
        } catch (\Exception $e) {
            error_log('Error al crear venta: ' . $e->getMessage());
            $_SESSION['errores'] = ['Error al procesar la compra. Intente nuevamente.'];
            $this->redirect('carrito/ver');
        }
    }

    public function exito(int $idVenta): void
    {
        $this->requiereClienteActivo();

        $ventaModel = $this->model('VentaModel');
        $venta = $ventaModel->buscarPorId($idVenta);

        if (!$venta || (int) $venta['id_usuario'] !== (int) $_SESSION['id_usuario']) {
            $this->redirect('producto');
            return;
        }

        $this->view('venta/exito', [
            'venta' => $venta,
        ]);
    }

    public function verificarIntegridad(int $idVenta): void
    {
        $this->verificarAdmin();

        $ventaModel = $this->model('VentaModel');
        $venta = $ventaModel->buscarPorId($idVenta);

        if (!$venta) {
            $_SESSION['errores'] = ['Venta no encontrada.'];
            $this->redirect('producto/admin');
            return;
        }

        if ((int) ($venta['firma_version'] ?? 1) >= 2) {
            $datosActuales = VentaCanonicalizer::serializar(
                (int) $venta['id_usuario'],
                $venta['fecha'],
                (float) $venta['total'],
                array_map(fn($d) => [
                    'id_producto' => (int) $d['id_producto'],
                    'cantidad' => (int) $d['cantidad'],
                    'precio_unitario' => (float) $d['precio_unitario'],
                    'subtotal' => (float) $d['subtotal'],
                    'costo_unitario' => (float) ($d['costo_unitario_historico'] ?? 0),
                ], $venta['detalle'])
            );
        } else {
        $datosActuales = json_encode([
            'id_usuario' => (int) $venta['id_usuario'],
            'fecha'      => $venta['fecha'],
            'total'      => (float) $venta['total'],
            'detalle'    => array_map(function ($d) {
                return [
                    'id_producto'    => (int) $d['id_producto'],
                    'cantidad'       => (int) $d['cantidad'],
                    'precio_unitario'=> (float) $d['precio_unitario'],
                    'subtotal'       => (float) $d['subtotal'],
                ];
            }, $venta['detalle']),
        ]);
        }

        $firmaService = new FirmaDigitalService();
        $hashActual = hash('sha256', $datosActuales);
        $firmaValida = $firmaService->verificar($datosActuales, $venta['firma_digital']);

        $hashCoincide = ($hashActual === $venta['hash_datos']);

        $this->view('venta/detalle', [
            'venta'        => $venta,
            'hashCoincide' => $hashCoincide,
            'firmaValida'  => $firmaValida,
            'hashActual'   => $hashActual,
        ]);
    }

    public function historial(): void
    {
        $this->requiereClienteActivo();

        $ventaModel = $this->model('VentaModel');
        $ventas = $ventaModel->listarPorUsuario((int) $_SESSION['id_usuario']);

        $this->view('venta/historial', [
            'ventas'  => $ventas,
            'errores' => $_SESSION['errores'] ?? [],
            'exito'   => $_SESSION['exito'] ?? '',
        ]);
        unset($_SESSION['errores'], $_SESSION['exito']);
    }
}
