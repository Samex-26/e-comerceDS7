<?php if (empty($_SESSION['cookie_consent'])): ?>
<div id="cookieBanner" style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999; background: #1e293b; color: white; padding: 1rem 0;">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <p class="mb-0 small" style="color: #e2e8f0;">
            Este sitio utiliza cookies para mejorar tu experiencia. Al continuar navegando, aceptas su uso.
        </p>
        <button id="btnAceptarCookies" class="btn px-4 py-2 fw-semibold flex-shrink-0" style="background: #fd761a; color: white; border: none;">
            Aceptar
        </button>
    </div>
</div>
<?php endif; ?>
