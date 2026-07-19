<?php $valores = array_merge($usuario ?? [], $old); ?>
<div class="container py-4" style="max-width: 720px">
    <h1 class="h3"><?= $usuario ? 'Editar usuario' : 'Registrar usuario' ?></h1>
    <?php foreach ($errores as $error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <form method="POST" action="<?= BASE_URL ?>usuario/<?= $usuario ? 'editar/' . (int) $usuario['id_usuario'] : 'crear' ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="mb-3"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" maxlength="100" required value="<?= htmlspecialchars($valores['nombre'] ?? '') ?>"></div>
        <div class="mb-3"><label class="form-label" for="email">Correo</label><input class="form-control" type="email" id="email" name="email" maxlength="150" required value="<?= htmlspecialchars($valores['email'] ?? '') ?>"></div>
        <?php if (!$usuario): ?><div class="mb-3"><label class="form-label" for="password">Contraseña inicial</label><input class="form-control" type="password" id="password" name="password" minlength="8" maxlength="12" required autocomplete="new-password"></div><?php endif; ?>
        <div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="rol">Rol</label><select class="form-select" id="rol" name="rol" required><option value="cliente" <?= ($valores['rol'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option><option value="admin" <?= ($valores['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option></select></div>
        <div class="col-md-6 mb-3"><label class="form-label" for="id_idioma">Idioma</label><select class="form-select" id="id_idioma" name="id_idioma" required><?php foreach ($idiomas as $idioma): ?><option value="<?= (int) $idioma['id_idioma'] ?>" <?= (int) ($valores['id_idioma'] ?? 0) === (int) $idioma['id_idioma'] ? 'selected' : '' ?>><?= htmlspecialchars($idioma['nombre']) ?></option><?php endforeach; ?></select></div></div>
        <button class="btn btn-primary" type="submit">Guardar</button>
        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>usuario/admin">Cancelar</a>
    </form>
</div>
