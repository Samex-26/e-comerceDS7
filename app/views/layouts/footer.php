<style>
    .site-footer { border-top: 1px solid #e2e8f0; background: #f2f4f6; }
</style>
<footer class="site-footer mt-5 py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" style="max-width: 1320px;">
        <div class="text-center text-md-start">
            <p class="fw-bold mb-1" style="color: #1e293b; font-size: 1.25rem;">TiendaUTP</p>
            <p class="small mb-0" style="color: #64748b;">© 2026 TiendaUTP. Universidad Tecnológica de Panamá.</p>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-4">
            <a href="<?= BASE_URL ?>contacto/index" class="small text-decoration-none" style="color: #64748b;">Contact Us</a>
            <a href="#" class="small text-decoration-none" style="color: #64748b;">Privacy Policy</a>
            <a href="#" class="small text-decoration-none" style="color: #64748b;">Shipping Info</a>
            <a href="#" class="small text-decoration-none" style="color: #64748b;">Terms of Service</a>
        </div>
    </div>
</footer>
<style>.cookies-banner,.cookie-banner{display:none!important}.consent-center{position:fixed;left:1rem;right:1rem;bottom:1rem;z-index:10000;background:#1e293b;color:#fff;padding:1rem;border-radius:.75rem}.consent-center[hidden]{display:none}</style>
<div id="consentCenter" class="consent-center" hidden>
    <p class="mb-2">Las cookies necesarias mantienen la sesión. Las analíticas son opcionales y solo se activan con su permiso.</p>
    <button type="button" class="btn btn-light btn-sm" data-consent="accepted">Aceptar analíticas</button>
    <button type="button" class="btn btn-outline-light btn-sm" data-consent="rejected">Rechazar</button>
</div>
<button type="button" id="revokeConsent" class="btn btn-sm btn-secondary" style="position:fixed;right:1rem;bottom:1rem;z-index:9999">Privacidad</button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
