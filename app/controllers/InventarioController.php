<?php

class InventarioController extends Controller
{

    public function admin(): void
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarGuardar();
            return;
        }

        $model = $this->model('InventarioModel');
        $entradas = $model->listarTodos();

        $productoModel = $this->model('Producto');
        $proveedorModel = $this->model('ProveedorModel');
        $productos = $productoModel->listarActivos();
        $proveedores = $proveedorModel->listarTodos();

        $editarId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
        $entrada = $editarId ? $model->buscarPorId($editarId) : null;

        $this->view('inventario/admin_listado', [
            'entradas'    => $entradas,
            'productos'   => $productos,
            'proveedores' => $proveedores,
            'entrada'     => $entrada,
            'csrf_token'  => $this->generarTokenCsrf(),
            'errores'     => $_SESSION['errores'] ?? [],
            'exito'       => $_SESSION['exito'] ?? '',
            'old'         => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['exito'], $_SESSION['old']);
    }

    private function procesarGuardar(): void
    {
        $errores = [];
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        $id               = Sanitizer::entero($_POST['id'] ?? 0);
        $idProducto       = Sanitizer::entero($_POST['id_producto'] ?? 0);
        $idProveedor      = Sanitizer::entero($_POST['id_proveedor'] ?? 0);
        $cantidad         = filter_var($_POST['cantidad_ingresada'] ?? '', FILTER_VALIDATE_INT);
        $costoUnitario    = Sanitizer::decimal($_POST['costo_unitario'] ?? 0);
        $fechaEntrada     = Sanitizer::texto($_POST['fecha_entrada'] ?? '');
        $detalle          = Sanitizer::texto($_POST['detalle'] ?? '');

        if (!Validator::enteroPositivo($idProducto)) {
            $errores[] = 'Debe seleccionar un producto válido.';
        }
        if (!Validator::enteroPositivo($idProveedor)) {
            $errores[] = 'Debe seleccionar un proveedor válido.';
        }
        if ($cantidad === false || $cantidad <= 0 || $cantidad > 2147483647) {
            $errores[] = 'La cantidad debe ser un número entero positivo.';
        }
        if (!Validator::numerico($costoUnitario) || $costoUnitario < 0 || $costoUnitario > 99999999.99) {
            $errores[] = 'El costo unitario debe ser un número no negativo.';
        }
        if (empty($fechaEntrada)) {
            $errores[] = 'La fecha de entrada es obligatoria.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEntrada)
            || !checkdate((int) substr($fechaEntrada, 5, 2), (int) substr($fechaEntrada, 8, 2), (int) substr($fechaEntrada, 0, 4))) {
            $errores[] = 'La fecha de entrada debe tener formato AAAA-MM-DD.';
        }
        $producto = $this->model('Producto')->buscarPorId($idProducto);
        if (!$producto || !(int) $producto['activo']) $errores[] = 'El producto no existe o no está activo.';
        if (!$this->model('ProveedorModel')->buscarPorId($idProveedor)) $errores[] = 'El proveedor no existe.';

        if (!Validator::noVacio($detalle) || !Validator::longitud($detalle, 2, 1000)) {
            $errores[] = 'El detalle es obligatorio y debe tener entre 2 y 1000 caracteres.';
        }
        // El costo enviado por el navegador no es fuente de verdad.
        if ($producto) $costoUnitario = (float) $producto['costo'];

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = $_POST;
            $redirect = 'inventario/admin';
            if ($id) {
                $redirect .= '?editar=' . $id;
            }
            $this->redirect($redirect);
            return;
        }

        $model = $this->model('InventarioModel');
        $datos = [
            'id_producto'        => $idProducto,
            'id_proveedor'       => $idProveedor,
            'cantidad_ingresada' => $cantidad,
            'costo_unitario'     => $costoUnitario,
            'fecha_entrada'      => $fechaEntrada,
            'detalle'           => $detalle,
        ];

        if ($id) {
            $model->actualizar($id, $datos);
            $_SESSION['exito'] = $this->lang['exito_actualizado'];
        } else {
            $model->crear($datos);
            $_SESSION['exito'] = $this->lang['exito_creado'];
        }

        $this->redirect('inventario/admin');
    }

    public function eliminar(int $id): void
    {
        $this->verificarAdmin();
        $this->exigirPostConCsrf();
        $model = $this->model('InventarioModel');

        try {
            $model->eliminar($id);
            $_SESSION['exito'] = $this->lang['exito_eliminado'];
        } catch (\Exception $e) {
            $_SESSION['errores'] = ['Error al eliminar la entrada de inventario.'];
        }

        $this->redirect('inventario/admin');
    }
}
