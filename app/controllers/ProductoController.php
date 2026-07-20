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
        $sku          = strtoupper(Sanitizer::alias($_POST['sku'] ?? ''));
        $nombre       = Sanitizer::texto($_POST['nombre'] ?? '');
        $descripcion  = Sanitizer::html($_POST['descripcion'] ?? '');
        $precio       = Sanitizer::decimal($_POST['precio'] ?? 0);
        $precioOferta = Sanitizer::decimal($_POST['precio_oferta'] ?? 0);
        $costo        = Sanitizer::decimal($_POST['costo'] ?? 0);
        $cantidad     = filter_var($_POST['cantidad'] ?? '', FILTER_VALIDATE_INT);
        $idCategoria  = Sanitizer::entero($_POST['id_categoria'] ?? 0);
        $activo       = isset($_POST['activo']) ? 1 : 0;

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre del producto es obligatorio.';
        }
        if ($sku === '' || strlen($sku) > 80 || !preg_match('/^[A-Z0-9_-]+$/', $sku)) {
            $errores[] = 'El SKU es obligatorio y solo admite letras, números, guion y guion bajo (máximo 80).';
        }
        if (!Validator::numerico($precio) || $precio <= 0) {
            $errores[] = 'El precio debe ser un numero positivo.';
        }
        if (!Validator::enteroPositivo($idCategoria)) {
            $errores[] = 'Debe seleccionar una categoria valida.';
        }
        if ($precioOferta < 0 || $precioOferta > $precio) $errores[] = 'La oferta no puede superar el precio regular.';
        if ($costo < 0 || $costo > 99999999.99) $errores[] = 'El costo está fuera del rango permitido.';
        if ($precio > 99999999.99 || $precioOferta > 99999999.99) $errores[] = 'El precio excede el máximo permitido.';
        if ($cantidad === false || $cantidad < 0 || $cantidad > 2147483647) $errores[] = 'El stock debe ser un entero no negativo válido.';

        $imagen = null;
        $model = $this->model('Producto');
        if (!$this->model('Categoria')->buscarPorId($idCategoria)) $errores[] = 'La categoría seleccionada no existe.';
        if ($sku !== '' && $model->buscarPorSku($sku, $id ?: null)) $errores[] = 'El SKU ya está registrado.';

        if (!empty($_FILES['imagen']['name'])) {
            $imagen = $this->procesarImagen($_FILES['imagen']);
            if ($imagen === false) {
                $errores[] = $this->lang['error_imagen'];
            }
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
            'sku'           => $sku,
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

        try {
            if ($id) {
                $model->actualizar($id, $datos);
                $_SESSION['exito'] = $this->lang['exito_actualizado'];
            } else {
                $model->crear($datos);
                $_SESSION['exito'] = $this->lang['exito_creado'];
            }
        } catch (PDOException $e) {
            error_log('Error al guardar producto: ' . $e->getMessage());
            $_SESSION['errores'] = ['No se pudo guardar el producto. Verifique el SKU y los datos relacionados.'];
            $_SESSION['old'] = $_POST;
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

        $nombre       = Sanitizer::texto($_POST['nombre'] ?? '');
        $descripcion  = Sanitizer::html($_POST['descripcion'] ?? '');
        $precio       = Sanitizer::decimal($_POST['precio'] ?? 0);
        $precioOferta = Sanitizer::decimal($_POST['precio_oferta'] ?? 0);
        $costo        = Sanitizer::decimal($_POST['costo'] ?? 0);
        $cantidad     = Sanitizer::entero($_POST['cantidad'] ?? 0);
        $idCategoria  = Sanitizer::entero($_POST['id_categoria'] ?? 0);

        // Validar campos
        if ($sku === '' || strlen($sku) > 80 || !preg_match('/^[A-Z0-9_-]+$/', $sku)) {
            $errores[] = 'SKU inválido.';
        }
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
        if ($model->buscarPorSku($sku)) {
            $_SESSION['errores'] = ['El SKU ya está registrado.'];
            $_SESSION['old'] = $_POST;
            $this->redirect('producto/crear');
        }
        $model->crear([
            'sku'           => $sku,
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

        $sku          = strtoupper(Sanitizer::alias($_POST['sku'] ?? ''));
        $sku          = strtoupper(Sanitizer::alias($_POST['sku'] ?? ''));
        $nombre       = Sanitizer::texto($_POST['nombre'] ?? '');
        $descripcion  = Sanitizer::html($_POST['descripcion'] ?? '');
        $precio       = Sanitizer::decimal($_POST['precio'] ?? 0);
        $precioOferta = Sanitizer::decimal($_POST['precio_oferta'] ?? 0);
        $costo        = Sanitizer::decimal($_POST['costo'] ?? 0);
        $cantidad     = Sanitizer::entero($_POST['cantidad'] ?? 0);
        $idCategoria  = Sanitizer::entero($_POST['id_categoria'] ?? 0);

        if ($sku === '' || strlen($sku) > 80 || !preg_match('/^[A-Z0-9_-]+$/', $sku)) {
            $errores[] = 'SKU inválido.';
        }
        if ($this->model('Producto')->buscarPorSku($sku, $id)) {
            $errores[] = 'El SKU ya está registrado.';
        }
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
            'sku'           => $sku,
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
        $this->exigirPostConCsrf();
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

        $tiposPermitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        if ($archivo['size'] > $maxSize) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);
        if (!isset($tiposPermitidos[$mime]) || @getimagesize($archivo['tmp_name']) === false) return false;
        $directorio = BASE_PATH . '/../public/assets/img/productos';
        if ((!is_dir($directorio) && !mkdir($directorio, 0755, true)) || !is_writable($directorio)) {
            error_log('Directorio de productos no disponible: ' . $directorio);
            return false;
        }
        $nombreUnico = 'prod_' . bin2hex(random_bytes(16)) . '.' . $tiposPermitidos[$mime];
        $destino = $directorio . '/' . $nombreUnico;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            return false;
        }

        return 'assets/img/productos/' . $nombreUnico;
    }
}
