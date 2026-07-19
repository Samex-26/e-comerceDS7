<?php
if (isset($_GET['lang'])) {
    $_SESSION['idioma_codigo'] = $_GET['lang'] === 'en' ? 'en' : 'es';
}
$langCode = IdiomaHelper::getCodigo();
?>
<div class="min-vh-100 d-flex flex-column" style="background: #f7f9fb; font-family: 'Inter', sans-serif;">
    <div class="text-end py-2 px-4">
        <span class="material-symbols-outlined align-middle me-1" style="font-size: 1.1rem; color: #1e293b;">globe</span>
        <a href="?lang=es" class="text-decoration-none <?= $langCode === 'es' ? 'fw-bold' : '' ?>" style="color: #1e293b; font-size: 0.875rem;">ES</a>
        <span class="mx-1" style="color: #e2e8f0;">|</span>
        <a href="?lang=en" class="text-decoration-none <?= $langCode === 'en' ? 'fw-bold' : '' ?>" style="color: #1e293b; font-size: 0.875rem;">EN</a>
    </div>

    <div class="text-center mt-4">
        <span class="material-symbols-outlined" style="font-size: 3rem; color: #1e293b;">shopping_bag</span>
        <h1 class="h4 mt-2" style="color: #1e293b; font-weight: 600;">TiendaUTP</h1>
    </div>

    <div class="container d-flex justify-content-center mt-3 mb-4">
        <div class="card" style="max-width: 440px; width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: none;">
            <div class="card-body p-4">
                <h2 class="h5 mb-4" style="color: #1e293b; font-weight: 600;">Crear cuenta</h2>

                <?php if (!empty($errores)): ?>
                    <div class="alert p-3 mb-4" style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; color: #dc2626; font-size: 0.875rem;">
                        <strong><?= htmlspecialchars($lang['error_campos']) ?></strong>
                        <?php foreach ($errores as $e): ?>
                            <p class="mb-0 mt-1"><?= htmlspecialchars($e) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>auth/registro">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="mb-3">
                        <label for="nombre" class="form-label" style="color: #1e293b; font-size: 0.875rem; font-weight: 500;"><?= htmlspecialchars($lang['nombre']) ?></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: transparent; border: 1px solid #e2e8f0; border-right: none;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem; color: #94a3b8;">person</span>
                            </span>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   value="<?= htmlspecialchars($old['nombre'] ?? '') ?>" required
                                   style="border: 1px solid #e2e8f0; border-left: none; padding-left: 0; font-size: 0.875rem;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label" style="color: #1e293b; font-size: 0.875rem; font-weight: 500;"><?= htmlspecialchars($lang['email']) ?></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: transparent; border: 1px solid #e2e8f0; border-right: none;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem; color: #94a3b8;">mail</span>
                            </span>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required
                                   style="border: 1px solid #e2e8f0; border-left: none; padding-left: 0; font-size: 0.875rem;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label" style="color: #1e293b; font-size: 0.875rem; font-weight: 500;"><?= htmlspecialchars($lang['contrasena']) ?></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: transparent; border: 1px solid #e2e8f0; border-right: none;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem; color: #94a3b8;">lock</span>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8" maxlength="12"
                                   style="border: 1px solid #e2e8f0; border-left: none; padding-left: 0; font-size: 0.875rem;">
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                                    style="border: 1px solid #e2e8f0; border-left: none; background: transparent; color: #94a3b8;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem;">visibility</span>
                            </button>
                        </div>
                        <div class="form-text" style="color: #64748b; font-size: 0.75rem;"><?= htmlspecialchars($lang['password_length_error']) ?></div>
                    </div>

                    <div class="mb-4">
                        <label for="id_idioma" class="form-label" style="color: #1e293b; font-size: 0.875rem; font-weight: 500;"><?= htmlspecialchars($lang['idioma']) ?></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background: transparent; border: 1px solid #e2e8f0; border-right: none;">
                                <span class="material-symbols-outlined" style="font-size: 1.25rem; color: #94a3b8;">globe</span>
                            </span>
                            <select class="form-select" id="id_idioma" name="id_idioma" required
                                    style="border: 1px solid #e2e8f0; border-left: none; padding-left: 0; font-size: 0.875rem;">
                                <option value="">-- <?= htmlspecialchars($lang['idioma']) ?> --</option>
                                <?php foreach ($idiomas as $idi): ?>
                                    <option value="<?= (int) $idi['id_idioma'] ?>"
                                        <?= (isset($old['id_idioma']) && (int) $old['id_idioma'] === (int) $idi['id_idioma']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($idi['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 py-2 d-flex align-items-center justify-content-center gap-2"
                            style="background: #fd761a; color: #fff; border: none; border-radius: 8px; font-weight: 500; font-size: 0.9rem;">
                        Crear cuenta
                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">arrow_forward</span>
                    </button>
                </form>

                <p class="mt-4 text-center mb-0" style="font-size: 0.875rem; color: #64748b;">
                    ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>auth/login" style="color: #fd761a; text-decoration: none; font-weight: 500;">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-auto" style="color: #64748b; font-size: 0.8rem; border-top: 1px solid #e2e8f0;">
        &copy; 2024 TiendaUTP. Universidad Tecnológica de Panamá.
    </footer>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = this.querySelector('.material-symbols-outlined');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        pwd.type = 'password';
        icon.textContent = 'visibility';
    }
});
</script>
