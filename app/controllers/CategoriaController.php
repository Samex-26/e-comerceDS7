<?php
// Controlador de administración de categorías (solo admin).

class CategoriaController extends Controller
{

    public function admin(): void
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarGuardarCategoria();
            return;
        }

        $model = $this->model('Categoria');
        $categorias = $model->listarConConteo();

        $editarId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
        $categoriaEditar = $editarId ? $model->buscarPorId($editarId) : null;

        $this->view('categoria/admin_listado', [
            'categorias'      => $categorias,
            'categoriaEditar' => $categoriaEditar,
            'csrf_token'      => $this->generarTokenCsrf(),
            'errores'         => $_SESSION['errores'] ?? [],
            'exito'           => $_SESSION['exito'] ?? '',
            'old'             => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['exito'], $_SESSION['old']);
    }

    private function procesarGuardarCategoria(): void
    {
        $errores = [];
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        $id          = Sanitizer::entero($_POST['id'] ?? 0);
        $nombre      = Sanitizer::texto($_POST['nombre'] ?? '');
        $descripcion = Sanitizer::texto($_POST['descripcion'] ?? '');

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre de la categoria es obligatorio.';
        }
        if (!Validator::longitud($nombre, 2, 100)) {
            $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = ['nombre' => $nombre, 'descripcion' => $descripcion];
            $redirect = 'categoria/admin';
            if ($id) {
                $redirect .= '?editar=' . $id;
            }
            $this->redirect($redirect);
            return;
        }

        $model = $this->model('Categoria');
        $datos = ['nombre' => $nombre, 'descripcion' => $descripcion];

        if ($id) {
            $model->actualizar($id, $datos);
            $_SESSION['exito'] = $this->lang['exito_actualizado'];
        } else {
            $model->crear($datos);
            $_SESSION['exito'] = $this->lang['exito_creado'];
        }

        $this->redirect('categoria/admin');
    }

    public function crear(): void
    {
        $this->verificarAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCrear();
        } else {
            $this->view('categoria/admin_form', [
                'csrf_token' => $this->generarTokenCsrf(),
                'errores'    => $_SESSION['errores'] ?? [],
                'old'        => $_SESSION['old'] ?? [],
            ]);
            unset($_SESSION['errores'], $_SESSION['old']);
        }
    }

    private function procesarCrear(): void
    {
        $errores = [];
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        $nombre       = Sanitizer::texto($_POST['nombre'] ?? '');
        $descripcion  = Sanitizer::texto($_POST['descripcion'] ?? '');

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre de la categoría es obligatorio.';
        }
        if (!Validator::longitud($nombre, 2, 100)) {
            $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = ['nombre' => $nombre, 'descripcion' => $descripcion];
            $this->redirect('categoria/crear');
            return;
        }

        $model = $this->model('Categoria');
        $model->crear(['nombre' => $nombre, 'descripcion' => $descripcion]);
        $_SESSION['exito'] = $this->lang['exito_creado'];
        $this->redirect('categoria/admin');
    }

    public function editar(int $id): void
    {
        $this->verificarAdmin();
        $model = $this->model('Categoria');
        $categoria = $model->buscarPorId($id);

        if (!$categoria) {
            $this->redirect('categoria/admin');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarEditar($id);
        } else {
            $this->view('categoria/admin_form', [
                'categoria'  => $categoria,
                'csrf_token' => $this->generarTokenCsrf(),
                'errores'    => $_SESSION['errores'] ?? [],
            ]);
            unset($_SESSION['errores']);
        }
    }

    private function procesarEditar(int $id): void
    {
        $errores = [];
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        $nombre       = Sanitizer::texto($_POST['nombre'] ?? '');
        $descripcion  = Sanitizer::texto($_POST['descripcion'] ?? '');

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre de la categoría es obligatorio.';
        }
        if (!Validator::longitud($nombre, 2, 100)) {
            $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
        }

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $this->redirect('categoria/editar/' . $id);
            return;
        }

        $model = $this->model('Categoria');
        $model->actualizar($id, ['nombre' => $nombre, 'descripcion' => $descripcion]);
        $_SESSION['exito'] = $this->lang['exito_actualizado'];
        $this->redirect('categoria/admin');
    }

    public function eliminar(int $id): void
    {
        $this->verificarAdmin();
        $model = $this->model('Categoria');

        if ($model->tieneProductos($id)) {
            $_SESSION['errores'] = [$this->lang['error_categoria_productos']];
            $this->redirect('categoria/admin');
            return;
        }

        $model->eliminar($id);
        $_SESSION['exito'] = $this->lang['exito_eliminado'];
        $this->redirect('categoria/admin');
    }
}
