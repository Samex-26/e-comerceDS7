<?php

class UsuarioController extends Controller
{
    public function admin(): void
    {
        $this->requerirAdmin();
        $usuarios = $this->model('Usuario')->listarTodos();
        $this->view('usuario/admin_listado', [
            'usuarios' => $usuarios,
            'errores' => $_SESSION['errores'] ?? [],
            'exito' => $_SESSION['exito'] ?? '',
        ]);
        unset($_SESSION['errores'], $_SESSION['exito']);
    }

    public function ver(int $id): void
    {
        $this->requerirAdmin();
        $usuario = $this->model('Usuario')->buscarPorId($id);
        if (!$usuario) {
            $this->redirect('usuario/admin');
        }
        unset($usuario['password_hash']);
        $this->view('usuario/detalle', [
            'usuario' => $usuario,
            'errores' => $_SESSION['errores'] ?? [],
            'exito' => $_SESSION['exito'] ?? '',
        ]);
        unset($_SESSION['errores'], $_SESSION['exito']);
    }

    public function enviarEnlacePassword(int $id): void
    {
        $this->requerirPost();
        $this->requerirAdmin();
        $this->requerirCsrf();
        $usuario = $this->model('Usuario')->buscarPorId($id);
        if (!$usuario) {
            $_SESSION['errores'] = ['Usuario no encontrado.'];
            $this->redirect('usuario/admin');
        }
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $hash = hash('sha256', $token);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        try {
            $this->model('PasswordResetToken')->crear((int) $usuario['id_usuario'], $hash, $ip, (int) $_SESSION['id_usuario']);
            $enlace = BASE_URL . 'auth/restablecer/' . rawurlencode($token);
            $medio = (new MailService())->enviarRestablecimiento($usuario, $enlace);
            $_SESSION['exito'] = $medio === 'SMTP' ? 'Enlace de cambio enviado por correo.' : 'SMTP no está configurado; el correo se guardó como archivo local de prueba.';
        } catch (Throwable $e) {
            error_log('No se pudo enviar el enlace de contraseña: ' . $e->getMessage());
            $_SESSION['errores'] = ['No se pudo enviar el enlace. Revise la configuración de correo e inténtelo nuevamente.'];
        } finally {
            $token = str_repeat("\0", strlen($token));
        }
        $this->redirect('usuario/ver/' . $id);
    }

    public function crear(): void
    {
        $this->requerirAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->guardar(null);
            return;
        }
        $this->mostrarFormulario();
    }

    public function editar(int $id): void
    {
        $this->requerirAdmin();
        $usuario = $this->model('Usuario')->buscarPorId($id);
        if (!$usuario) {
            $this->redirect('usuario/admin');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->guardar($id);
            return;
        }
        unset($usuario['password_hash']);
        $this->mostrarFormulario($usuario);
    }

    public function activar(int $id): void
    {
        $this->cambiarEstado($id, true);
    }

    public function desactivar(int $id): void
    {
        $this->cambiarEstado($id, false);
    }

    private function cambiarEstado(int $id, bool $activo): void
    {
        $this->requerirPost();
        $this->requerirAdmin();
        $this->requerirCsrf();
        $model = $this->model('Usuario');
        $usuario = $model->buscarPorId($id);
        if (!$usuario) {
            $_SESSION['errores'] = ['Usuario no encontrado.'];
            $this->redirect('usuario/admin');
        }
        if (!$activo && (int) $usuario['id_usuario'] === (int) $_SESSION['id_usuario']
            && $usuario['rol'] === 'admin' && $model->contarAdministradoresActivos() <= 1) {
            $_SESSION['errores'] = ['No puede desactivar al único administrador activo.'];
            $this->redirect('usuario/admin');
        }
        $model->cambiarEstado($id, $activo);
        $_SESSION['exito'] = $activo ? 'Usuario activado.' : 'Usuario desactivado.';
        $this->redirect('usuario/admin');
    }

    private function mostrarFormulario(?array $usuario = null): void
    {
        $this->view('usuario/form', [
            'usuario' => $usuario,
            'idiomas' => $this->model('Idioma')->listarTodos(),
            'errores' => $_SESSION['errores'] ?? [],
            'old' => $_SESSION['old'] ?? [],
        ]);
        unset($_SESSION['errores'], $_SESSION['old']);
    }

    private function guardar(?int $id): void
    {
        $this->requerirCsrf();
        $nombre = Sanitizer::nombrePropio(Sanitizer::texto($_POST['nombre'] ?? ''));
        $email = Sanitizer::email($_POST['email'] ?? '');
        $password = $id === null ? (string) ($_POST['password'] ?? '') : '';
        $rol = in_array($_POST['rol'] ?? '', ['admin', 'cliente'], true) ? $_POST['rol'] : '';
        $idIdioma = Sanitizer::entero($_POST['id_idioma'] ?? 0);
        $errores = [];

        if (!Validator::longitud($nombre, 2, 100)) $errores[] = 'El nombre debe tener entre 2 y 100 caracteres.';
        if (!Validator::email($email)) $errores[] = 'Correo electrónico inválido.';
        if (!$rol) $errores[] = 'Rol inválido.';
        if (!$this->model('Idioma')->buscarPorId($idIdioma)) $errores[] = 'Idioma inválido.';
        if (($id === null || $password !== '') && !Validator::longitud($password, 8, 12)) {
            $errores[] = 'La contraseña debe tener entre 8 y 12 caracteres.';
        }

        $model = $this->model('Usuario');
        $duplicado = $model->buscarPorEmail($email);
        if ($duplicado && (int) $duplicado['id_usuario'] !== (int) $id) $errores[] = 'El correo ya está registrado.';
        if ($id !== null) {
            $actual = $model->buscarPorId($id);
            if ($actual && $actual['rol'] === 'admin' && $rol !== 'admin'
                && (bool) $actual['activo'] && $model->contarAdministradoresActivos() <= 1) {
                $errores[] = 'No puede cambiar el rol del único administrador activo.';
            }
        }
        if ($errores) {
            $_SESSION['errores'] = $errores;
            $_SESSION['old'] = ['nombre' => $nombre, 'email' => $email, 'rol' => $rol, 'id_idioma' => $idIdioma];
            $this->redirect($id === null ? 'usuario/crear' : 'usuario/editar/' . $id);
        }

        $datos = ['nombre' => $nombre, 'email' => $email, 'rol' => $rol, 'id_idioma' => $idIdioma];
        if ($password !== '') $datos['password_hash'] = (new PasswordHasherService())->procesar($password);
        if ($id === null) {
            $datos['activo'] = 1;
            $model->crear($datos);
        } else {
            $model->actualizar($id, $datos);
        }
        $_SESSION['exito'] = $id === null ? 'Usuario registrado.' : 'Usuario actualizado.';
        $this->redirect('usuario/admin');
    }
}
