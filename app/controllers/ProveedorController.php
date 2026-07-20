<?php

class ProveedorController extends Controller
{

    public function admin(): void
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarGuardar();
            return;
        }

        $model = $this->model('ProveedorModel');
        $proveedores = $model->listarTodos();

        $editarId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
        $proveedor = $editarId ? $model->buscarPorId($editarId) : null;

        $this->view('proveedor/admin_listado', [
            'proveedores' => $proveedores,
            'proveedor'   => $proveedor,
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

        $id           = Sanitizer::entero($_POST['id'] ?? 0);
        $nombre       = Sanitizer::texto($_POST['nombre'] ?? '');
        $telefonoRaw  = trim((string) ($_POST['telefono'] ?? ''));
        $celularRaw   = trim((string) ($_POST['celular'] ?? ''));
        $telefono     = Sanitizer::telefono($telefonoRaw);
        $celular      = Sanitizer::telefono($celularRaw);
        $email        = Sanitizer::email($_POST['email'] ?? '');
        $direccion    = Sanitizer::texto($_POST['direccion'] ?? '');
        $ciudad       = Sanitizer::texto($_POST['ciudad'] ?? '');
        $sitioWeb     = Sanitizer::url($_POST['sitio_web'] ?? '');
        $calificacion = Sanitizer::entero($_POST['calificacion_estrellas'] ?? 0);
        $notas        = Sanitizer::texto($_POST['notas'] ?? '');
        $activo       = filter_var($_POST['activo'] ?? null, FILTER_VALIDATE_INT);

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre del proveedor es obligatorio.';
        }
        if (!Validator::longitud($nombre, 2, 255)) {
            $errores[] = 'El nombre debe tener entre 2 y 255 caracteres.';
        }
        if (!preg_match('/^[0-9+() -]{7,20}$/', $telefonoRaw)) $errores[] = 'El teléfono es obligatorio y su formato no es válido.';
        if (!preg_match('/^[0-9+() -]{7,20}$/', $celularRaw)) $errores[] = 'El celular es obligatorio y su formato no es válido.';
        if (!Validator::longitud($direccion, 5, 255)) $errores[] = 'La dirección es obligatoria y debe tener entre 5 y 255 caracteres.';
        if (!empty($email) && !Validator::email($email)) {
            $errores[] = 'El formato del correo electrónico no es válido.';
        }
        if (!Validator::noVacio($sitioWeb) || !Validator::url($sitioWeb) || strlen($sitioWeb) > 255) {
            $errores[] = 'El formato de la URL del sitio web no es válido.';
        }
        if (!Validator::rangoNumerico($calificacion, 1, 5)) {
            $errores[] = 'La calificación debe estar entre 1 y 5 estrellas.';
        }
        if (!in_array($activo, [0, 1], true)) $errores[] = 'El estado del proveedor es obligatorio.';

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = $_POST;
            $redirect = 'proveedor/admin';
            if ($id) {
                $redirect .= '?editar=' . $id;
            }
            $this->redirect($redirect);
            return;
        }

        $model = $this->model('ProveedorModel');
        $datos = [
            'nombre'                => $nombre,
            'telefono'              => $telefono,
            'celular'               => $celular,
            'email'                 => $email,
            'direccion'             => $direccion,
            'ciudad'                => $ciudad,
            'sitio_web'             => $sitioWeb,
            'calificacion_estrellas'=> $calificacion,
            'notas'                => $notas,
            'activo'               => $activo,
        ];

        if ($id) {
            $model->actualizar($id, $datos);
            $_SESSION['exito'] = $this->lang['exito_actualizado'];
        } else {
            $model->crear($datos);
            $_SESSION['exito'] = $this->lang['exito_creado'];
        }

        $this->redirect('proveedor/admin');
    }

    public function eliminar(int $id): void
    {
        $this->verificarAdmin();
        $this->exigirPostConCsrf();
        $model = $this->model('ProveedorModel');

        if ($model->tieneInventario($id)) {
            $_SESSION['errores'] = ['No se puede eliminar el proveedor porque tiene entradas de inventario asociadas.'];
            $this->redirect('proveedor/admin');
            return;
        }

        $resultado = $model->eliminar($id);
        if ($resultado) {
            $_SESSION['exito'] = $this->lang['exito_eliminado'];
        } else {
            $_SESSION['errores'] = ['No se pudo eliminar el proveedor.'];
        }
        $this->redirect('proveedor/admin');
    }
}
