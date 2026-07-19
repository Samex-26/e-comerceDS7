<?php

class ProveedorController extends Controller
{
    public function admin(): void
    {
        $this->requerirAdmin();
        $this->requerirMetodo('GET');
        $this->view('proveedor/admin_listado', [
            'proveedores' => $this->model('ProveedorModel')->listarTodos(),
            'errores' => $_SESSION['errores'] ?? [],
            'exito' => $_SESSION['exito'] ?? '',
        ]);
        unset($_SESSION['errores'], $_SESSION['exito']);
    }

    public function crear(): void
    {
        $this->requerirAdmin();
        $this->requerirMetodo('GET');
        $this->mostrarFormulario(null);
    }

    public function guardar(): void
    {
        $this->requerirAdmin();
        $this->requerirPost();
        $this->procesarGuardar(null);
    }

    public function editar(int $id): void
    {
        $this->requerirAdmin();
        $this->requerirMetodo('GET');
        $proveedor = $this->model('ProveedorModel')->buscarPorId($id);
        if (!$proveedor) {
            $this->proveedorNoEncontrado();
            return;
        }
        $this->mostrarFormulario($proveedor);
    }

    public function actualizar(int $id): void
    {
        $this->requerirAdmin();
        $this->requerirPost();
        if (!$this->model('ProveedorModel')->buscarPorId($id)) {
            $this->proveedorNoEncontrado();
            return;
        }
        $this->procesarGuardar($id);
    }

    private function mostrarFormulario(?array $proveedor): void
    {
        $this->view('proveedor/form', [
            'proveedor' => $proveedor,
            'valores' => $_SESSION['old'] ?? $proveedor ?? [],
            'erroresCampos' => $_SESSION['errores_campos'] ?? [],
            'errores' => $_SESSION['errores'] ?? [],
        ]);
        unset($_SESSION['old'], $_SESSION['errores_campos'], $_SESSION['errores']);
    }

    private function procesarGuardar(?int $id): void
    {
        if (!$this->verificarTokenCsrf((string) ($_POST['csrf_token'] ?? ''))) {
            http_response_code(403);
            exit('Solicitud inválida. Actualice la página e inténtelo nuevamente.');
        }

        $datos = $this->sanitizar();
        if ($id !== null && !array_key_exists('calificacion_estrellas', $_POST)) {
            $actual = $this->model('ProveedorModel')->buscarPorId($id);
            $datos['calificacion_estrellas'] = (int) ($actual['calificacion_estrellas'] ?? 0);
        }
        $errores = $this->validar($datos);
        if ($errores) {
            $_SESSION['errores_campos'] = $errores;
            $_SESSION['errores'] = ['Revise los campos señalados.'];
            $_SESSION['old'] = $datos;
            $this->redirect($id === null ? 'proveedor/crear' : 'proveedor/editar/' . $id);
        }

        try {
            $model = $this->model('ProveedorModel');
            if ($id === null) {
                $model->crear($datos);
                $_SESSION['exito'] = 'Proveedor registrado correctamente.';
            } else {
                $model->actualizar($id, $datos);
                $_SESSION['exito'] = 'Proveedor actualizado correctamente.';
            }
        } catch (PDOException $e) {
            error_log('Error al guardar proveedor: ' . $e->getMessage());
            $_SESSION['errores'] = [DEBUG ? 'No se pudo guardar: ' . $e->getMessage() : 'No se pudo guardar el proveedor. Intente nuevamente.'];
            $_SESSION['old'] = $datos;
            $this->redirect($id === null ? 'proveedor/crear' : 'proveedor/editar/' . $id);
        }
        $this->redirect('proveedor/admin');
    }

    private function sanitizar(): array
    {
        $sitio = Sanitizer::url($_POST['sitio_web'] ?? '');
        if ($sitio !== '' && !preg_match('#^https?://#i', $sitio)) $sitio = 'https://' . $sitio;
        return [
            'nombre' => Sanitizer::texto($_POST['nombre'] ?? ''),
            'telefono' => Sanitizer::telefono($_POST['telefono'] ?? ''),
            'celular' => Sanitizer::telefono($_POST['celular'] ?? ''),
            'email' => Sanitizer::email($_POST['email'] ?? ''),
            'sitio_web' => $sitio,
            'direccion' => Sanitizer::texto($_POST['direccion'] ?? ''),
            'ciudad' => Sanitizer::texto($_POST['ciudad'] ?? ''),
            'calificacion_estrellas' => Sanitizer::entero($_POST['calificacion_estrellas'] ?? 0),
            'notas' => Sanitizer::texto($_POST['notas'] ?? ''),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
    }

    private function validar(array $datos): array
    {
        $errores = [];
        if (!Validator::longitud($datos['nombre'], 2, 255)) $errores['nombre'] = 'Ingrese un nombre de 2 a 255 caracteres.';
        if ($datos['email'] !== '' && !Validator::email($datos['email'])) $errores['email'] = 'Ingrese un correo electrónico válido.';
        if ($datos['sitio_web'] !== '' && !Validator::url($datos['sitio_web'])) $errores['sitio_web'] = 'Ingrese una dirección web válida.';
        if (!Validator::rangoNumerico($datos['calificacion_estrellas'], 0, 5)) $errores['calificacion_estrellas'] = 'Seleccione una calificación entre 0 y 5.';
        return $errores;
    }

    public function eliminar(int $id): void
    {
        $this->requerirAdmin();
        $this->requerirPost();
        $this->requerirCsrf();
        $model = $this->model('ProveedorModel');
        if (!$model->buscarPorId($id)) { $this->proveedorNoEncontrado(); return; }
        if ($model->tieneInventario($id)) {
            $_SESSION['errores'] = ['No se puede eliminar porque tiene entradas de inventario asociadas.'];
        } elseif ($model->eliminar($id)) {
            $_SESSION['exito'] = 'Proveedor eliminado correctamente.';
        } else {
            $_SESSION['errores'] = ['No se pudo eliminar el proveedor.'];
        }
        $this->redirect('proveedor/admin');
    }

    private function requerirMetodo(string $metodo): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $metodo) {
            http_response_code(405);
            header('Allow: ' . $metodo);
            exit('Método no permitido.');
        }
    }

    private function proveedorNoEncontrado(): void
    {
        http_response_code(404);
        $this->view('proveedor/no_encontrado');
    }
}
