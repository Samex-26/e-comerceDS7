<?php

class UsuarioController extends Controller
{

    public function admin(): void
    {
        $this->verificarAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarGuardarUsuario();
            return;
        }

        $model = $this->model('Usuario');
        $usuarios = $model->listarTodos();

        $editarId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
        $usuarioEditar = $editarId ? $model->buscarPorId($editarId) : null;

        $idiomaModel = $this->model('Idioma');

        $this->view('usuario/admin_listado', [
            'usuarios'      => $usuarios,
            'usuarioEditar' => $usuarioEditar,
            'idiomas'       => $idiomaModel->listarTodos(),
            'csrf_token'    => $this->generarTokenCsrf(),
            'errores'       => $_SESSION['errores'] ?? [],
            'exito'         => $_SESSION['exito'] ?? '',
            'old'           => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['exito'], $_SESSION['old']);
    }

    private function procesarGuardarUsuario(): void
    {
        $errores = [];
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->verificarTokenCsrf($token)) {
            $errores[] = $this->lang['error_csrf'];
        }

        $id        = Sanitizer::entero($_POST['id'] ?? 0);
        $nombre    = Sanitizer::nombrePropio(Sanitizer::texto($_POST['nombre'] ?? ''));
        $email     = Sanitizer::email($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $id_idioma = Sanitizer::entero($_POST['id_idioma'] ?? 0);
        $rol       = $_POST['rol'] ?? '';

        if (!Validator::noVacio($nombre)) {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (!Validator::email($email)) {
            $errores[] = 'Correo electrónico inválido.';
        }
        if (!in_array($rol, ['admin', 'cliente'], true)) {
            $errores[] = 'Rol seleccionado no válido.';
        }

        // Validar que el idioma exista
        $idiomaModel = $this->model('Idioma');
        $idioma = $idiomaModel->buscarPorId($id_idioma);
        if (!$idioma) {
            $errores[] = 'Idioma seleccionado no válido.';
        }

        if ($id) {
            // --- Editar usuario existente ---

            // Proteccion 1: no quitarse el propio rol de admin
            if ($id === (int) ($_SESSION['id_usuario'] ?? 0) && $rol !== 'admin') {
                $usuarioActual = $this->model('Usuario')->buscarPorId($id);
                if ($usuarioActual && $usuarioActual['rol'] === 'admin') {
                    $errores[] = 'No puedes quitarte tu propio rol de administrador.';
                }
            }

            // Proteccion adicional: no dejar el sistema sin admins
            if ($rol !== 'admin') {
                $model = $this->model('Usuario');
                $usuario = $model->buscarPorId($id);
                if ($usuario && $usuario['rol'] === 'admin' && $model->contarAdmins() <= 1) {
                    $errores[] = 'No puedes cambiar el rol del único administrador del sistema.';
                }
            }

            // Contraseña opcional en edicion
            $passwordHash = '';
            if (!empty($password)) {
                if (!Validator::longitud($password, 8, 100)) {
                    $errores[] = $this->lang['password_length_error'];
                } else {
                    $hasher = new PasswordHasherService();
                    $passwordHash = $hasher->procesar($password);
                }
            }

            // Verificar email duplicado (excluyendo al mismo usuario)
            $model = $this->model('Usuario');
            $existente = $model->buscarPorEmail($email);
            if ($existente && (int) $existente['id_usuario'] !== $id) {
                $errores[] = $this->lang['error_email_duplicado'];
            }

            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                $this->redirect('usuario/admin?editar=' . $id);
                return;
            }

            $datos = [
                'nombre'    => $nombre,
                'email'     => $email,
                'id_idioma' => $id_idioma,
                'rol'       => $rol,
            ];
            if (!empty($passwordHash)) {
                $datos['password_hash'] = $passwordHash;
            }

            $model->actualizar($id, $datos);
            $_SESSION['exito'] = $this->lang['exito_actualizado'];

        } else {
            // --- Crear nuevo usuario ---

            if (!Validator::longitud($password, 8, 100)) {
                $errores[] = $this->lang['password_length_error'];
            }

            // Verificar email duplicado
            $model = $this->model('Usuario');
            if ($model->buscarPorEmail($email) !== false) {
                $errores[] = $this->lang['error_email_duplicado'];
            }

            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                $_SESSION['old'] = ['nombre' => $nombre, 'email' => $email, 'id_idioma' => $id_idioma, 'rol' => $rol];
                $this->redirect('usuario/admin');
                return;
            }

            $hasher = new PasswordHasherService();
            $passwordHash = $hasher->procesar($password);

            $model->crear([
                'nombre'        => $nombre,
                'email'         => $email,
                'password_hash' => $passwordHash,
                'id_idioma'     => $id_idioma,
                'rol'           => $rol,
            ]);
            $_SESSION['exito'] = $this->lang['exito_creado'];
        }

        $this->redirect('usuario/admin');
    }

    public function eliminar(int $id): void
    {
        $this->verificarAdmin();
        $this->exigirPostConCsrf();

        // Proteccion 2: no eliminarse a si mismo
        if ($id === (int) ($_SESSION['id_usuario'] ?? 0)) {
            $_SESSION['errores'] = ['No puedes eliminar tu propia cuenta.'];
            $this->redirect('usuario/admin');
            return;
        }

        $model = $this->model('Usuario');
        $model->eliminar($id);
        $_SESSION['exito'] = $this->lang['exito_eliminado'];
        $this->redirect('usuario/admin');
    }
}
