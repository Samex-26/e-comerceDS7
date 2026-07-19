<?php
if (isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'])) {
    $_SESSION['idioma_codigo'] = $_GET['lang'];
    echo "<script>window.location.href='" . BASE_URL . "'</script>";
    exit;
}
$currentLang = IdiomaHelper::getCodigo();
?>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    .navbar { display: none !important; }
    body {
        background: #f7f9fb !important;
        font-family: 'Inter', sans-serif !important;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow-x: hidden;
        padding: 2rem 1rem !important;
    }
    .bg-circle {
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }
    .bg-circle-1 {
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(79,70,229,0.12) 0%, transparent 70%);
        top: -200px; right: -150px;
    }
    .bg-circle-2 {
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(253,118,26,0.10) 0%, transparent 70%);
        bottom: -150px; left: -100px;
    }
    .bg-circle-3 {
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(30,41,59,0.06) 0%, transparent 70%);
        bottom: 10%; right: 10%;
    }
    .lang-selector {
        position: fixed;
        top: 1.25rem;
        right: 1.5rem;
        z-index: 50;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #64748b;
        cursor: default;
    }
    .lang-selector .material-symbols-outlined {
        font-size: 1.25rem;
        color: #94a3b8;
    }
    .lang-selector .lang-link {
        color: #94a3b8;
        text-decoration: none;
        font-weight: 500;
        cursor: pointer;
        transition: color 0.15s;
    }
    .lang-selector .lang-link:hover,
    .lang-selector .lang-link.active {
        color: #fd761a;
    }
    .lang-selector .lang-sep {
        color: #cbd5e1;
    }
    .logo-wrapper {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 1.75rem;
    }
    .logo-icon-wrap {
        width: 4rem;
        height: 4rem;
        background: #1e293b;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
    }
    .logo-icon-wrap .material-symbols-outlined {
        font-size: 2rem;
        color: #ffffff;
        font-variation-settings: 'FILL' 1, 'wght' 300;
    }
    .logo-text {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.02em;
        margin: 0;
    }
    .login-card {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 2rem;
    }
    .login-card-title {
        font-size: 1.375rem;
        font-weight: 700;
        color: #1e293b;
        text-align: center;
        margin-bottom: 1.5rem;
        letter-spacing: -0.01em;
    }
    .error-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
        font-size: 0.875rem;
        color: #b91c1c;
    }
    .error-alert .material-symbols-outlined {
        font-size: 1.25rem;
        color: #ef4444;
        flex-shrink: 0;
        margin-top: 0.0625rem;
    }
    .error-alert p { margin: 0; }
    .error-alert p + p { margin-top: 0.25rem; }
    .success-alert {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
        font-size: 0.875rem;
        color: #15803d;
    }
    .success-alert .material-symbols-outlined {
        font-size: 1.25rem;
        color: #22c55e;
        flex-shrink: 0;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.375rem;
    }
    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrapper .input-icon {
        position: absolute;
        left: 0.875rem;
        font-size: 1.25rem;
        color: #94a3b8;
        pointer-events: none;
        font-variation-settings: 'FILL' 0, 'wght' 300;
    }
    .input-wrapper .input-field {
        width: 100%;
        height: 2.75rem;
        padding: 0 0.875rem 0 2.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.625rem;
        font-size: 0.9375rem;
        font-family: 'Inter', sans-serif;
        color: #1e293b;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .input-wrapper .input-field:focus {
        border-color: #fd761a;
        box-shadow: 0 0 0 3px rgba(253,118,26,0.12);
    }
    .input-wrapper .input-field::placeholder {
        color: #94a3b8;
    }
    .input-wrapper .input-field.has-right-btn {
        padding-right: 2.75rem;
    }
    .password-toggle-btn {
        position: absolute;
        right: 0.5rem;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        border-radius: 0.375rem;
        transition: color 0.15s, background 0.15s;
    }
    .password-toggle-btn:hover {
        color: #64748b;
        background: #f1f5f9;
    }
    .password-toggle-btn .material-symbols-outlined {
        font-size: 1.25rem;
        font-variation-settings: 'FILL' 0, 'wght' 300;
    }
    .form-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #475569;
        cursor: pointer;
        user-select: none;
    }
    .checkbox-label input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        accent-color: #fd761a;
        cursor: pointer;
    }
    .forgot-link {
        font-size: 0.875rem;
        color: #fd761a;
        text-decoration: none;
        font-weight: 500;
    }
    .forgot-link:hover {
        text-decoration: underline;
    }
    .btn-submit {
        width: 100%;
        height: 2.875rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: #fd761a;
        color: #ffffff;
        font-size: 1rem;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        border: none;
        border-radius: 0.625rem;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
    }
    .btn-submit:hover {
        background: #e4690f;
    }
    .btn-submit:active {
        transform: scale(0.98);
    }
    .btn-submit .material-symbols-outlined {
        font-size: 1.25rem;
        font-variation-settings: 'FILL' 0, 'wght' 400;
    }
    .register-link {
        position: relative;
        z-index: 1;
        text-align: center;
        margin-top: 1.25rem;
        font-size: 0.9375rem;
        color: #64748b;
    }
    .register-link a {
        color: #fd761a;
        text-decoration: none;
        font-weight: 600;
    }
    .register-link a:hover {
        text-decoration: underline;
    }
    .login-footer {
        position: relative;
        z-index: 1;
        text-align: center;
        margin-top: 2rem;
        font-size: 0.8125rem;
        color: #94a3b8;
    }
</style>

<div class="bg-circle bg-circle-1"></div>
<div class="bg-circle bg-circle-2"></div>
<div class="bg-circle bg-circle-3"></div>

<div class="lang-selector">
    <span class="material-symbols-outlined">language</span>
    <span>
        Language:
        <a href="?lang=es" class="lang-link<?= $currentLang === 'es' ? ' active' : '' ?>">ES</a>
        <span class="lang-sep">/</span>
        <a href="?lang=en" class="lang-link<?= $currentLang === 'en' ? ' active' : '' ?>">EN</a>
    </span>
    <span class="material-symbols-outlined">expand_more</span>
</div>

<div class="logo-wrapper">
    <div class="logo-icon-wrap">
        <span class="material-symbols-outlined">shopping_bag</span>
    </div>
    <h1 class="logo-text">TiendaUTP</h1>
</div>

<div class="login-card">
    <h2 class="login-card-title"><?= htmlspecialchars($lang['inicio_sesion']) ?></h2>

    <?php if (!empty($exito)): ?>
        <div class="success-alert">
            <span class="material-symbols-outlined">check_circle</span>
            <span><?= htmlspecialchars($exito) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="error-alert">
            <span class="material-symbols-outlined">error_outline</span>
            <div>
                <?php foreach ($errores as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>auth/login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="form-group">
            <label for="email"><?= htmlspecialchars($lang['email']) ?></label>
            <div class="input-wrapper">
                <span class="material-symbols-outlined input-icon">mail</span>
                <input type="email" class="input-field" id="email" name="email"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       placeholder="ejemplo@utp.edu.co" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password"><?= htmlspecialchars($lang['contrasena']) ?></label>
            <div class="input-wrapper">
                <span class="material-symbols-outlined input-icon">lock</span>
                <input type="password" class="input-field has-right-btn" id="password" name="password" maxlength="12"
                       placeholder="Ingrese su contraseña" required>
                <button type="button" class="password-toggle-btn" onclick="togglePassword()" tabindex="-1" aria-label="Mostrar contraseña">
                    <span class="material-symbols-outlined" id="passwordIcon">visibility</span>
                </button>
            </div>
        </div>

        <div class="form-row">
            <label class="checkbox-label">
                <input type="checkbox" name="recordarme"> Recordarme
            </label>
            <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn-submit">
            Iniciar sesión
            <span class="material-symbols-outlined">arrow_forward</span>
        </button>
    </form>
</div>

<p class="register-link">
    ¿No tienes cuenta? <a href="<?= BASE_URL ?>auth/registro">Regístrate</a>
</p>

<footer class="login-footer">
    &copy; 2024 TiendaUTP. Universidad Tecnol&oacute;gica de Panam&aacute;.
</footer>

<script>
function togglePassword() {
    var pwd = document.getElementById('password');
    var icon = document.getElementById('passwordIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        pwd.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>
