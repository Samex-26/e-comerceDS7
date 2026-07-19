<div class="container py-5" style="max-width: 520px">
    <h1 class="h3 mb-4">Establecer nueva contraseña</h1>
    <?php foreach ($errores ?? [] as $error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <?php if (!empty($tokenValido)): ?>
    <form method="POST" action="<?= BASE_URL ?>auth/restablecer/<?= rawurlencode($token) ?>" onsubmit="return confirm('¿Desea guardar esta nueva contraseña?')">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="mb-3"><label class="form-label" for="password">Nueva contraseña</label><div class="input-group"><input class="form-control password-field" id="password" type="password" name="password" minlength="8" maxlength="12" required autocomplete="new-password"><button class="btn btn-outline-secondary toggle-password" type="button">Mostrar</button></div></div>
        <div class="mb-3"><label class="form-label" for="password_confirmacion">Confirmar contraseña</label><div class="input-group"><input class="form-control password-field" id="password_confirmacion" type="password" name="password_confirmacion" minlength="8" maxlength="12" required autocomplete="new-password"><button class="btn btn-outline-secondary toggle-password" type="button">Mostrar</button></div></div>
        <button class="btn btn-primary" type="submit">Guardar contraseña</button>
    </form>
    <?php else: ?><a class="btn btn-outline-primary" href="<?= BASE_URL ?>auth/login">Volver al inicio de sesión</a><?php endif; ?>
</div>
<script>document.querySelectorAll('.toggle-password').forEach(function(b){b.addEventListener('click',function(){const i=b.parentElement.querySelector('input');i.type=i.type==='password'?'text':'password';b.textContent=i.type==='password'?'Mostrar':'Ocultar';});});</script>
