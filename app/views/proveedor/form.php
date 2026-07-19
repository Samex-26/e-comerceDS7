<?php
$editando = !empty($proveedor);
$accion = $editando ? BASE_URL . 'proveedor/actualizar/' . (int)$proveedor['id_proveedor'] : BASE_URL . 'proveedor/guardar';
$error = static fn(string $campo): string => $erroresCampos[$campo] ?? '';
$clase = static fn(string $campo): string => isset($erroresCampos[$campo]) ? ' is-invalid' : '';
?>
<style>
.provider-form-page{background:#f7f9fb;min-height:calc(100vh - 120px)}.provider-form-wrap{max-width:1000px;margin:auto}.provider-card{border:0;border-radius:16px;box-shadow:0 8px 28px rgba(15,23,42,.08)}.required-mark{color:#b42318}.form-label{font-weight:600;color:#334155}.form-control:focus,.form-check-input:focus{border-color:#fd761a;box-shadow:0 0 0 .25rem rgba(253,118,26,.2)}.rating-options{display:flex;gap:.35rem;flex-wrap:wrap}.rating-option input{position:absolute;opacity:0}.rating-option label{width:44px;height:42px;border:1px solid #cbd5e1;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-weight:700;color:#64748b}.rating-option input:checked+label{background:#fff3e8;border-color:#fd761a;color:#c94f00}.rating-option input:focus-visible+label{outline:3px solid rgba(253,118,26,.3);outline-offset:2px}@media(max-width:575.98px){.provider-card .card-body{padding:1.25rem!important}.form-actions .btn{width:100%}}
</style>
<main class="provider-form-page py-4 px-3">
 <div class="provider-form-wrap">
  <nav aria-label="Migas de navegación"><ol class="breadcrumb mb-3"><li class="breadcrumb-item">Admin</li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>proveedor/admin">Proveedores</a></li><li class="breadcrumb-item active" aria-current="page"><?= $editando ? 'Editar proveedor' : 'Nuevo proveedor' ?></li></ol></nav>
  <header class="mb-4"><h1 class="h2 mb-2"><?= $editando ? 'Editar proveedor' : 'Registrar proveedor' ?></h1><p class="text-secondary mb-0"><?= $editando ? 'Actualice la información y confirme los cambios antes de guardar.' : 'Complete la información para incorporar un nuevo proveedor al inventario.' ?></p></header>
  <?php foreach ($errores as $mensaje): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($mensaje) ?></div><?php endforeach; ?>
  <div class="card provider-card"><div class="card-body p-4 p-lg-5">
   <form method="POST" action="<?= $accion ?>" <?= $editando ? 'onsubmit="return confirm(\'¿Desea guardar los cambios realizados en este proveedor?\')"' : '' ?> novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <div class="row g-4">
     <div class="col-12"><label class="form-label" for="nombre">Nombre del proveedor <span class="required-mark" aria-label="obligatorio">*</span></label><input class="form-control<?= $clase('nombre') ?>" id="nombre" name="nombre" maxlength="255" required autocomplete="organization" value="<?= htmlspecialchars($valores['nombre'] ?? '') ?>" aria-describedby="nombre-error"><?php if ($error('nombre')): ?><div class="invalid-feedback" id="nombre-error"><?= htmlspecialchars($error('nombre')) ?></div><?php endif; ?></div>
     <div class="col-12 col-md-6"><label class="form-label" for="telefono">Teléfono</label><input class="form-control" type="tel" id="telefono" name="telefono" maxlength="50" autocomplete="tel" placeholder="+507 300-0000" value="<?= htmlspecialchars($valores['telefono'] ?? '') ?>"></div>
     <div class="col-12 col-md-6"><label class="form-label" for="celular">Celular</label><input class="form-control" type="tel" id="celular" name="celular" maxlength="50" autocomplete="tel" placeholder="+507 6000-0000" value="<?= htmlspecialchars($valores['celular'] ?? '') ?>"></div>
     <div class="col-12 col-md-6"><label class="form-label" for="email">Correo electrónico</label><input class="form-control<?= $clase('email') ?>" type="email" id="email" name="email" maxlength="255" autocomplete="email" placeholder="contacto@proveedor.com" value="<?= htmlspecialchars($valores['email'] ?? '') ?>" aria-describedby="email-error"><?php if ($error('email')): ?><div class="invalid-feedback" id="email-error"><?= htmlspecialchars($error('email')) ?></div><?php endif; ?></div>
     <div class="col-12 col-md-6"><label class="form-label" for="sitio_web">Sitio web</label><input class="form-control<?= $clase('sitio_web') ?>" type="text" inputmode="url" id="sitio_web" name="sitio_web" maxlength="255" autocomplete="url" placeholder="proveedor.com" value="<?= htmlspecialchars($valores['sitio_web'] ?? '') ?>" aria-describedby="sitio-error sitio-help"><div class="form-text" id="sitio-help">Si omite el protocolo, se agregará https:// automáticamente.</div><?php if ($error('sitio_web')): ?><div class="invalid-feedback" id="sitio-error"><?= htmlspecialchars($error('sitio_web')) ?></div><?php endif; ?></div>
     <div class="col-12"><label class="form-label" for="direccion">Dirección</label><input class="form-control" id="direccion" name="direccion" autocomplete="street-address" placeholder="Calle y número del establecimiento" value="<?= htmlspecialchars($valores['direccion'] ?? '') ?>"></div>
     <div class="col-12"><label class="form-label" for="ciudad">Ciudad</label><input class="form-control" id="ciudad" name="ciudad" maxlength="100" autocomplete="address-level2" placeholder="Ciudad de Panamá" value="<?= htmlspecialchars($valores['ciudad'] ?? '') ?>"></div>
     <div class="col-12"><label class="form-label" for="notas">Notas</label><textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Información adicional del proveedor"><?= htmlspecialchars($valores['notas'] ?? '') ?></textarea></div>
     <?php if ($editando): ?><div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?= !empty($valores['activo'])?'checked':'' ?>><label class="form-check-label" for="activo">Proveedor activo</label></div></div><?php endif; ?>
    </div>
    <div class="form-actions d-flex flex-column-reverse flex-sm-row justify-content-end gap-2 mt-5 pt-4 border-top"><a class="btn btn-outline-secondary px-4" href="<?= BASE_URL ?>proveedor/admin">Cancelar</a><button class="btn btn-primary px-4" type="submit"><?= $editando ? 'Actualizar proveedor' : 'Guardar proveedor' ?></button></div>
   </form>
  </div></div>
 </div>
</main>
