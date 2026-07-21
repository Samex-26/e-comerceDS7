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

        $productosRelacionados = $model->buscarRelacionados(
            (int) $producto['id_categoria'],
            $id
        );

        $this->view('producto/detalle', [
            'producto'             => $producto,
            'productosRelacionados' => $productosRelacionados,
        ]);
    }

    // ---- Acciones de administración ----

    public function admin(): void
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarGuardarProducto();
            return;
        }

        $model = $this->model('Producto');
        $productos = $model->listarTodosAdmin();

        $totalProductos = $model->contarActivos();
        $stockBajo = $model->contarStockBajo(10);
        $catModel = $this->model('Categoria');
        $categorias = $catModel->listarTodas();

        $editarId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
        $productoEditar = $editarId ? $model->buscarPorId($editarId) : null;

        $this->view('producto/admin_listado', [
            'productos'      => $productos,
            'productoEditar' => $productoEditar,
            'categorias'     => $categorias,
            'totalProductos' => $totalProductos,
            'stockBajo'      => $stockBajo,
            'csrf_token'     => $this->generarTokenCsrf(),
            'errores'        => $_SESSION['errores'] ?? [],
            'exito'          => $_SESSION['exito'] ?? '',
            'old'            => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['exito'], $_SESSION['old']);
    }

    private function procesarGuardarProducto(): void
    {
        $errores = [];
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        $id           = Sanitizer::entero($_POST['id'] ?? 0);
        $nombre       = Sanitizer::nombrePropio(Sanitizer::texto($_POST['nombre'] ?? ''));
        $descripcion  = Sanitizer::capitalizar(Sanitizer::html($_POST['descripcion'] ?? ''));
        $precio       = Sanitizer::decimal($_POST['precio'] ?? 0);
        $precioOferta = Sanitizer::decimal($_POST['precio_oferta'] ?? 0);
        $costo        = Sanitizer::decimal($_POST['costo'] ?? 0);
        $cantidad     = Sanitizer::entero($_POST['cantidad'] ?? 0);
        $idCategoria  = Sanitizer::entero($_POST['id_categoria'] ?? 0);
        $activo       = isset($_POST['activo']) ? 1 : 0;

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre del producto es obligatorio.';
        }
        if (!Validator::numerico($precio) || $precio <= 0) {
            $errores[] = 'El precio debe ser un numero positivo.';
        }
        if (!Validator::enteroPositivo($idCategoria)) {
            $errores[] = 'Debe seleccionar una categoria valida.';
        }
        if ($precioOferta > 0 && $precioOferta >= $precio) {
            $errores[] = 'El precio de oferta debe ser menor al precio base.';
        }
        if ($precioOferta < 0) {
            $errores[] = 'El precio de oferta no puede ser negativo.';
        }
        if (!Validator::numerico($costo) || $costo < 0) {
            $errores[] = 'El costo no puede ser negativo.';
        }
        if ($cantidad < 0) {
            $errores[] = 'La cantidad no puede ser negativa.';
        }

        $imagen = null;
        $model = $this->model('Producto');

        if (!empty($_FILES['imagen']['name'])) {
            $imagen = $this->procesarImagen($_FILES['imagen']);
            if ($imagen === false) {
                $errores[] = $this->lang['error_imagen'];
            }
        } elseif ($id === 0) {
            $errores[] = 'La imagen del producto es obligatoria. Debe subir un archivo JPG, PNG o WebP.';
        }

        if (!empty($errores)) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = $_POST;
            $redirect = 'producto/admin';
            if ($id) {
                $redirect .= '?editar=' . $id;
            }
            $this->redirect($redirect);
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
            'activo'        => $activo,
        ];
        if ($imagen) {
            $datos['imagen'] = $imagen;
        }

        if ($id) {
            $model->actualizar($id, $datos);
            $_SESSION['exito'] = $this->lang['exito_actualizado'];
        } else {
            $model->crear($datos);
            $_SESSION['exito'] = $this->lang['exito_creado'];
        }

        $this->redirect('producto/admin');
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

        $nombre       = Sanitizer::nombrePropio(Sanitizer::texto($_POST['nombre'] ?? ''));
        $descripcion  = Sanitizer::capitalizar(Sanitizer::html($_POST['descripcion'] ?? ''));
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
        if ($precioOferta > 0 && $precioOferta >= $precio) {
            $errores[] = 'El precio de oferta debe ser menor al precio base.';
        }
        if ($precioOferta < 0) {
            $errores[] = 'El precio de oferta no puede ser negativo.';
        }
        if (!Validator::numerico($costo) || $costo < 0) {
            $errores[] = 'El costo no puede ser negativo.';
        }
        if (!Validator::enteroPositivo($cantidad) && $cantidad !== 0) {
            $errores[] = 'La cantidad debe ser un número entero no negativo.';
        }
        if ($cantidad < 0) {
            $errores[] = 'La cantidad no puede ser negativa.';
        }

        // Procesar imagen — obligatoria al crear
        $imagen = $this->procesarImagen($_FILES['imagen'] ?? []);
        if ($imagen === false || empty($imagen)) {
            $errores[] = $imagen === false ? $this->lang['error_imagen'] : 'La imagen del producto es obligatoria. Debe subir un archivo JPG, PNG o WebP.';
        }

        error_log('DEBUG errores: ' . print_r($errores, true));
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
            unset($_SESSION['errores'], $_SESSION['old']);
        }
    }

    private function procesarEditar(int $id): void
    {
        $errores = [];
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        $nombre       = Sanitizer::nombrePropio(Sanitizer::texto($_POST['nombre'] ?? ''));
        $descripcion  = Sanitizer::capitalizar(Sanitizer::html($_POST['descripcion'] ?? ''));
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
        if ($precioOferta > 0 && $precioOferta >= $precio) {
            $errores[] = 'El precio de oferta debe ser menor al precio base.';
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
