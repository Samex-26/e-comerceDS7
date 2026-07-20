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
        $this->exigirMetodoPost();
        $this->requiereClienteActivo();
        $this->exigirCsrf();

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

        if (isset($_SESSION['carrito'][$idProducto])) {
            $nuevaCant = $_SESSION['carrito'][$idProducto]['cantidad'] + 1;
            if ($nuevaCant > (int) $producto['cantidad']) {
                $_SESSION['errores'] = ['No hay suficiente stock para agregar más unidades.'];
                $this->redirect('producto');
                return;
            }
            $_SESSION['carrito'][$idProducto]['cantidad'] = $nuevaCant;
        } else {
            $_SESSION['carrito'][$idProducto] = [
                'cantidad'       => 1,
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
        $this->exigirMetodoPost();
        $this->requiereClienteActivo();
        $this->exigirCsrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('carrito/ver');
            return;
        }

        $cantidad = Sanitizer::entero($_POST['cantidad'] ?? 0);

        if (!isset($_SESSION['carrito'][$idProducto])) {
            $_SESSION['errores'] = ['El producto no está en el carrito.'];
            $this->redirect('carrito/ver');
            return;
        }

        if (!Validator::enteroPositivo($cantidad)) {
            $_SESSION['errores'] = ['La cantidad debe ser un número entero positivo.'];
            $this->redirect('carrito/ver');
            return;
        }

        $productoModel = $this->model('Producto');
        $producto = $productoModel->buscarPorId($idProducto);

        if (!$producto) {
            unset($_SESSION['carrito'][$idProducto]);
            $_SESSION['errores'] = ['El producto ya no está disponible.'];
            $this->redirect('carrito/ver');
            return;
        }

        if ($cantidad > (int) $producto['cantidad']) {
            $maxStock = (int) $producto['cantidad'];
            $_SESSION['errores'] = ["Stock disponible: $maxStock unidades. No puede agregar más."];
            $this->redirect('carrito/ver');
            return;
        }

        $_SESSION['carrito'][$idProducto]['cantidad'] = $cantidad;
        $_SESSION['exito'] = 'Cantidad actualizada.';
        $this->redirect('carrito/ver');
    }

    public function eliminar(int $idProducto): void
    {
        $this->exigirMetodoPost();
        $this->requiereClienteActivo();
        $this->exigirCsrf();

        if (isset($_SESSION['carrito'][$idProducto])) {
            unset($_SESSION['carrito'][$idProducto]);
            $_SESSION['exito'] = 'Producto eliminado del carrito.';
        }

        $this->redirect('carrito/ver');
    }

    public function ver(): void
    {
        $this->requiereClienteActivo();
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['redirect_after_login'] = 'carrito/ver';
            $this->redirect('auth/login');
            return;
        }

        $carrito = $_SESSION['carrito'] ?? [];
        $total = 0;

        foreach ($carrito as &$item) {
            $item['subtotal'] = $item['cantidad'] * $item['precio_unitario'];
            $total += $item['subtotal'];
        }
        unset($item);

        $this->view('carrito/ver', [
            'carrito'  => $carrito,
            'total'    => $total,
            'errores'  => $_SESSION['errores'] ?? [],
            'exito'    => $_SESSION['exito'] ?? '',
            'csrf_token' => $this->generarTokenCsrf(),
        ]);
        unset($_SESSION['errores'], $_SESSION['exito']);
    }
}
