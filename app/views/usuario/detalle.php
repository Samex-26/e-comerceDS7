<div class="container py-4" style="max-width: 720px">
    <?php foreach ($errores ?? [] as $error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <?php if (!empty($exito)): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
    <h1 class="h3">Detalle de usuario</h1>
    <dl class="row">
        <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8"><?= htmlspecialchars($usuario['nombre']) ?></dd>
        <dt class="col-sm-4">Correo</dt><dd class="col-sm-8"><?= htmlspecialchars($usuario['email']) ?></dd>
        <dt class="col-sm-4">Rol</dt><dd class="col-sm-8"><?= htmlspecialchars($usuario['rol']) ?></dd>
        <dt class="col-sm-4">Estado</dt><dd class="col-sm-8"><?= $usuario['activo'] ? ($usuario['bloqueado'] ? 'Bloqueado' : 'Activo') : 'Inactivo' ?></dd>
        <dt class="col-sm-4">Creado</dt><dd class="col-sm-8"><?= htmlspecialchars($usuario['created_at']) ?></dd>
    </dl>
    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>usuario/admin">Volver</a>
    <form class="d-inline" method="POST" action="<?= BASE_URL ?>usuario/enviarEnlacePassword/<?= (int) $usuario['id_usuario'] ?>" onsubmit="return confirm('¿Desea enviar a este usuario un enlace para establecer una nueva contraseña?')">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <button class="btn btn-primary" type="submit">Enviar enlace para cambiar contraseña</button>
    </form>
</div>
