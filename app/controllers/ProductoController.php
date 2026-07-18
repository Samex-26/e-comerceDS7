<?php
// Controlador de productos: catálogo público y administración CRUD.

class ProductoController extends Controller
{
    // ---- Acciones públicas ----

    public function index(?int $idCategoria = null): void
    {
        $productoModel = $this->model('Producto');
        $categoriaModel = $this->model('Categoria');

        $categorias  = $categoriaModel->listarTodas();
        $productos   = $productoModel->listarActivos($idCategoria);
        $categoriaActiva = $idCategoria !== null ? $categoriaModel->buscarPorId($idCategoria) : null;

        $this->view('producto/catalogo', [
            'productos'       => $productos,
            'categorias'      => $categorias,
            'categoriaActiva' => $categoriaActiva,
            'idCategoria'     => $idCategoria,
        ]);
    }

    public function detalle(int $id): void
    {
        $model = $this->model('Producto');
        $producto = $model->buscarPorId($id);

        if (!$producto || !$producto['activo']) {
            $this->redirect('producto');
            return;
        }

        $this->view('producto/detalle', [
            'producto' => $producto,
        ]);
    }

    // ---- Acciones de administración ----

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
        $model = $this->model('Producto');
        $productos = $model->listarActivos();
        $this->view('producto/admin_listado', [
            'productos' => $productos,
            'errores'   => $_SESSION['errores'] ?? [],
            'exito'     => $_SESSION['exito'] ?? '',
        ]);
        unset($_SESSION['errores'], $_SESSION['exito']);
    }

    public function crear(): void
    {
        $this->verificarAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCrear();
        } else {
            $catModel = $this->model('Categoria');
            $this->view('producto/admin_form', [
                'categorias' => $catModel->listarTodas(),
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
        $descripcion  = Sanitizer::html($_POST['descripcion'] ?? '');
        $precio       = Sanitizer::decimal($_POST['precio'] ?? 0);
        $precioOferta = Sanitizer::decimal($_POST['precio_oferta'] ?? 0);
        $costo        = Sanitizer::decimal($_POST['costo'] ?? 0);
        $cantidad     = Sanitizer::entero($_POST['cantidad'] ?? 0);
        $idCategoria  = Sanitizer::entero($_POST['id_categoria'] ?? 0);

        // Validar campos
        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre del producto es obligatorio.';
        }
        if (!Validator::numerico($precio) || $precio <= 0) {
            $errores[] = 'El precio debe ser un número positivo.';
        }
        if (!Validator::enteroPositivo($idCategoria)) {
            $errores[] = 'Debe seleccionar una categoría válida.';
        }
        if ($precioOferta > 0 && !Validator::maximo($precioOferta, $precio)) {
            $errores[] = 'El precio de oferta no puede ser mayor al precio regular.';
        }
        if (!Validator::enteroPositivo($cantidad) && $cantidad !== 0) {
            $errores[] = 'La cantidad debe ser un número entero no negativo.';
        }

        // Procesar imagen
        $imagen = $this->procesarImagen($_FILES['imagen'] ?? []);
        if ($imagen === false) {
            $errores[] = $this->lang['error_imagen'];
        }

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = $_POST;
            $this->redirect('producto/crear');
            return;
        }

        $model = $this->model('Producto');
        $model->crear([
            'nombre'        => $nombre,
            'descripcion'   => $descripcion,
            'imagen'        => $imagen,
            'precio'        => $precio,
            'precio_oferta' => $precioOferta > 0 ? $precioOferta : null,
            'costo'         => $costo,
            'cantidad'      => $cantidad,
            'id_categoria'  => $idCategoria,
        ]);

        $_SESSION['exito'] = $this->lang['exito_creado'];
        $this->redirect('producto/admin');
    }

    public function editar(int $id): void
    {
        $this->verificarAdmin();
        $model = $this->model('Producto');
        $producto = $model->buscarPorId($id);

        if (!$producto) {
            $this->redirect('producto/admin');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarEditar($id);
        } else {
            $catModel = $this->model('Categoria');
            $this->view('producto/admin_form', [
                'producto'   => $producto,
                'categorias' => $catModel->listarTodas(),
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
        $descripcion  = Sanitizer::html($_POST['descripcion'] ?? '');
        $precio       = Sanitizer::decimal($_POST['precio'] ?? 0);
        $precioOferta = Sanitizer::decimal($_POST['precio_oferta'] ?? 0);
        $costo        = Sanitizer::decimal($_POST['costo'] ?? 0);
        $cantidad     = Sanitizer::entero($_POST['cantidad'] ?? 0);
        $idCategoria  = Sanitizer::entero($_POST['id_categoria'] ?? 0);

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre del producto es obligatorio.';
        }
        if (!Validator::numerico($precio) || $precio <= 0) {
            $errores[] = 'El precio debe ser un número positivo.';
        }
        if (!Validator::enteroPositivo($idCategoria)) {
            $errores[] = 'Debe seleccionar una categoría válida.';
        }
        if ($precioOferta > 0 && !Validator::maximo($precioOferta, $precio)) {
            $errores[] = 'El precio de oferta no puede ser mayor al precio regular.';
        }

        // Imagen opcional en edición
        $imagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $imagen = $this->procesarImagen($_FILES['imagen']);
            if ($imagen === false) {
                $errores[] = $this->lang['error_imagen'];
            }
        }

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $this->redirect('producto/editar/' . $id);
            return;
        }

        $datos = [
            'nombre'        => $nombre,
            'descripcion'   => $descripcion,
            'precio'        => $precio,
            'precio_oferta' => $precioOferta > 0 ? $precioOferta : null,
            'costo'         => $costo,
            'cantidad'      => $cantidad,
            'id_categoria'  => $idCategoria,
        ];
        if ($imagen) {
            $datos['imagen'] = $imagen;
        }

        $model = $this->model('Producto');
        $model->actualizar($id, $datos);
        $_SESSION['exito'] = $this->lang['exito_actualizado'];
        $this->redirect('producto/admin');
    }

    public function eliminar(int $id): void
    {
        $this->verificarAdmin();
        $model = $this->model('Producto');
        $model->eliminar($id);
        $_SESSION['exito'] = $this->lang['exito_eliminado'];
        $this->redirect('producto/admin');
    }

    /**
     * Procesa la subida de imagen. Retorna la ruta relativa o false si hay error.
     */
    private function procesarImagen(array $archivo): string|false
    {
        if (empty($archivo['name'])) {
            return '';
        }

        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            return false;
        }
        if ($archivo['size'] > $maxSize) {
            return false;
        }

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreUnico = uniqid('prod_', true) . '.' . $extension;
        $destino = BASE_PATH . '/../public/assets/img/productos/' . $nombreUnico;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            return false;
        }

        return 'assets/img/productos/' . $nombreUnico;
    }
}
