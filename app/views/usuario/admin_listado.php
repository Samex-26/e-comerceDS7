<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Usuarios</h1>
        <a class="btn btn-primary" href="<?= BASE_URL ?>usuario/crear">Nuevo usuario</a>
    </div>
    <?php foreach ($errores as $error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Idioma</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars($usuario['rol']) ?></td>
                    <td><?= htmlspecialchars($usuario['idioma_nombre']) ?></td>
                    <td><?= $usuario['activo'] && !$usuario['bloqueado'] ? 'Activo' : ($usuario['bloqueado'] ? 'Bloqueado' : 'Inactivo') ?></td>
                    <td class="d-flex gap-1">
                        <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>usuario/ver/<?= (int) $usuario['id_usuario'] ?>">Ver</a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>usuario/editar/<?= (int) $usuario['id_usuario'] ?>">Editar</a>
                        <form method="POST" action="<?= BASE_URL ?>usuario/<?= $usuario['activo'] && !$usuario['bloqueado'] ? 'desactivar' : 'activar' ?>/<?= (int) $usuario['id_usuario'] ?>" onsubmit="return confirm('¿Confirma el cambio de estado?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button class="btn btn-sm btn-outline-warning" type="submit"><?= $usuario['activo'] && !$usuario['bloqueado'] ? 'Desactivar' : 'Activar' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
