<?php

class InventarioController extends Controller
{
    private function verificarAdmin(): void
    {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['errores'] = [$this->lang['acceso_denegado']];
            $this->redirect('auth/login');
        }
    }

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
        $cantidad         = Sanitizer::entero($_POST['cantidad_ingresada'] ?? 0);
        $costoUnitario    = Sanitizer::decimal($_POST['costo_unitario'] ?? 0);
        $fechaEntrada     = Sanitizer::texto($_POST['fecha_entrada'] ?? '');
        $detalle          = Sanitizer::texto($_POST['detalle'] ?? '');

        if (!Validator::enteroPositivo($idProducto)) {
            $errores[] = 'Debe seleccionar un producto válido.';
        }
        if (!Validator::enteroPositivo($idProveedor)) {
            $errores[] = 'Debe seleccionar un proveedor válido.';
        }
        if (!Validator::enteroPositivo($cantidad)) {
            $errores[] = 'La cantidad debe ser un número entero positivo.';
        }
        if (!Validator::numerico($costoUnitario) || $costoUnitario < 0) {
            $errores[] = 'El costo unitario debe ser un número no negativo.';
        }
        if (empty($fechaEntrada)) {
            $errores[] = 'La fecha de entrada es obligatoria.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEntrada)) {
            $errores[] = 'La fecha de entrada debe tener formato AAAA-MM-DD.';
        }

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
        $this->requerirPost();
        $this->verificarAdmin();
        $this->requerirCsrf();
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
