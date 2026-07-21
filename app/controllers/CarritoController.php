<?php

class CarritoController extends Controller
{
    public function index(): void
    {
        $this->ver();
    }

    private function requiereSesion(): void
    {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'carrito/ver';
            $_SESSION['errores'] = ['Debe iniciar sesión para usar el carrito.'];
            $this->redirect('auth/login');
        }
    }

    public function agregar(int $idProducto): void
    {
        $this->requiereSesion();
        $this->verificarCliente();

        $productoModel = $this->model('Producto');
        $producto = $productoModel->buscarPorId($idProducto);

        if (!$producto || !$producto['activo']) {
            $_SESSION['errores'] = ['El producto no existe o no está disponible.'];
            $this->redirect('producto');
            return;
        }

        if ((int) $producto['cantidad'] < 1) {
            $_SESSION['errores'] = ['El producto no tiene stock disponible.'];
            $this->redirect('producto');
            return;
        }

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        $precio = (!empty($producto['precio_oferta']) && $producto['precio_oferta'] > 0)
            ? (float) $producto['precio_oferta']
            : (float) $producto['precio'];

        $cantidad = Sanitizer::entero($_POST['cantidad'] ?? 1);
        if (!Validator::enteroPositivo($cantidad)) {
            $cantidad = 1;
        }
        if ($cantidad > (int) $producto['cantidad']) {
            $cantidad = (int) $producto['cantidad'];
        }

        if (isset($_SESSION['carrito'][$idProducto])) {
            $nuevaCant = $_SESSION['carrito'][$idProducto]['cantidad'] + $cantidad;
            if ($nuevaCant > (int) $producto['cantidad']) {
                $_SESSION['errores'] = ['No hay suficiente stock para agregar más unidades.'];
                $this->redirect('producto');
                return;
            }
            $_SESSION['carrito'][$idProducto]['cantidad'] = $nuevaCant;
        } else {
            $_SESSION['carrito'][$idProducto] = [
                'cantidad'       => $cantidad,
                'precio_unitario'=> $precio,
                'nombre'         => $producto['nombre'],
                'imagen'         => $producto['imagen'] ?? '',
            ];
        }

        $_SESSION['exito'] = 'Producto agregado al carrito.';
        $this->redirect('carrito/ver');
    }

    public function actualizar(int $idProducto): void
    {
        $this->requiereSesion();
        $this->verificarCliente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('carrito/ver');
            return;
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $cantidad = Sanitizer::entero($_POST['cantidad'] ?? 0);

        if (!isset($_SESSION['carrito'][$idProducto])) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'El producto no está en el carrito.']);
                return;
            }
            $_SESSION['errores'] = ['El producto no está en el carrito.'];
            $this->redirect('carrito/ver');
            return;
        }

        if (!Validator::enteroPositivo($cantidad)) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'La cantidad debe ser un número entero positivo.']);
                return;
            }
            $_SESSION['errores'] = ['La cantidad debe ser un número entero positivo.'];
            $this->redirect('carrito/ver');
            return;
        }

        $productoModel = $this->model('Producto');
        $producto = $productoModel->buscarPorId($idProducto);

        if (!$producto) {
            unset($_SESSION['carrito'][$idProducto]);
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'El producto ya no está disponible.']);
                return;
            }
            $_SESSION['errores'] = ['El producto ya no está disponible.'];
            $this->redirect('carrito/ver');
            return;
        }

        if ($cantidad > (int) $producto['cantidad']) {
            $maxStock = (int) $producto['cantidad'];
            if ($isAjax) {
                $this->json(['success' => false, 'error' => "Stock disponible: $maxStock unidades."]);
                return;
            }
            $_SESSION['errores'] = ["Stock disponible: $maxStock unidades. No puede agregar más."];
            $this->redirect('carrito/ver');
            return;
        }

        $precio = (!empty($producto['precio_oferta']) && $producto['precio_oferta'] > 0)
            ? (float) $producto['precio_oferta']
            : (float) $producto['precio'];

        $_SESSION['carrito'][$idProducto] = [
            'cantidad'       => $cantidad,
            'precio_unitario'=> $precio,
            'nombre'         => $producto['nombre'],
            'imagen'         => $producto['imagen'] ?? '',
        ];

        $subtotal = $cantidad * $precio;
        $total = 0;
        foreach ($_SESSION['carrito'] as $id => $item) {
            $total += $item['cantidad'] * $item['precio_unitario'];
        }

        if ($isAjax) {
            $this->json([
                'success'  => true,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'total'    => number_format($total, 2, '.', ''),
            ]);
            return;
        }

        $_SESSION['exito'] = 'Cantidad actualizada.';
        $this->redirect('carrito/ver');
    }

    public function eliminar(int $idProducto): void
    {
        $this->requiereSesion();
        $this->verificarCliente();

        if (isset($_SESSION['carrito'][$idProducto])) {
            unset($_SESSION['carrito'][$idProducto]);
            $_SESSION['exito'] = 'Producto eliminado del carrito.';
        }

        $this->redirect('carrito/ver');
    }

    public function ver(): void
    {
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['redirect_after_login'] = 'carrito/ver';
            $this->redirect('auth/login');
            return;
        }
        $this->verificarCliente();

        $carrito = $_SESSION['carrito'] ?? [];
        $items = [];
        $total = 0;

        foreach ($carrito as $idP => $item) {
            $subtotal = $item['cantidad'] * $item['precio_unitario'];
            $total += $subtotal;
            $items[$idP] = $item;
            $items[$idP]['subtotal'] = $subtotal;
        }

        $this->view('carrito/ver', [
            'carrito'  => $items,
            'total'    => $total,
            'errores'  => $_SESSION['errores'] ?? [],
            'exito'    => $_SESSION['exito'] ?? '',
        ]);
        unset($_SESSION['errores'], $_SESSION['exito']);
    }
}
