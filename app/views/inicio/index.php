<style>
    .hero-section {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #fff;
        padding: 5rem 0;
        position: relative;
        overflow: hidden;
    }
    .hero-section::after {
        content: '';
        position: absolute;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: rgba(253, 118, 26, 0.08);
        top: -100px;
        right: -100px;
    }
    .hero-section::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(79, 70, 229, 0.06);
        bottom: -80px;
        left: -80px;
    }
    .hero-title {
        font-size: 2.6rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
    }
    .hero-subtitle {
        font-size: 1.1rem;
        color: #cbd5e1;
        max-width: 540px;
        line-height: 1.6;
    }
    .hero-btn {
        background: #fd761a;
        color: #fff;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 700;
        font-size: 1rem;
        transition: background 0.2s;
    }
    .hero-btn:hover {
        background: #e06500;
        color: #fff;
    }
    .hero-stat {
        font-size: 0.85rem;
        color: #94a3b8;
    }
    .hero-stat strong {
        color: #fff;
        font-size: 1.1rem;
    }
    .features-section {
        padding: 4.5rem 0;
        background: #f7f9fb;
    }
    .feature-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.75rem;
        height: 100%;
        transition: box-shadow 0.2s ease;
    }
    .feature-card:hover {
        box-shadow: 0 4px 20px rgba(30,41,59,0.05);
    }
    .feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    .feature-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.4rem;
    }
    .feature-desc {
        font-size: 0.875rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 0;
    }
    .cta-section {
        padding: 4rem 0;
        background: #fff;
        text-align: center;
    }
    .cta-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
    }
    .cta-desc {
        color: #64748b;
        max-width: 500px;
        margin: 0 auto 1.5rem;
    }
</style>

<!-- ── Hero ── -->
<section class="hero-section">
    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="hero-title"><?= htmlspecialchars($lang['bienvenido_tiendautp'] ?? 'Bienvenido a TiendaUTP') ?></h1>
                <p class="hero-subtitle mt-3">
                    <?= htmlspecialchars($lang['landing_descripcion'] ?? 'Explora nuestra plataforma de comercio electrónico con compras seguras, múltiples categorías y facturación digital respaldada por firma criptográfica.') ?>
                </p>
                <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
                    <a href="<?= BASE_URL ?>producto" class="hero-btn d-inline-flex align-items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;">storefront</span>
                        <?= htmlspecialchars($lang['explorar_catalogo'] ?? 'Explorar catálogo') ?>
                    </a>
                    <?php if (!isset($_SESSION['id_usuario'])): ?>
                    <a href="<?= BASE_URL ?>auth/registro" class="btn d-inline-flex align-items-center gap-2 px-4 py-2 fw-semibold"
                       style="border: 1px solid #475569; color: #cbd5e1; border-radius: 0.5rem;">
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;">person_add</span>
                        <?= htmlspecialchars($lang['registro']) ?>
                    </a>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-4 mt-4">
                    <div class="hero-stat">
                        <strong><?= $totalProductos ?></strong><br><?= htmlspecialchars($lang['productos'] ?? 'Productos') ?>
                    </div>
                    <div class="hero-stat">
                        <strong><?= $totalCategorias ?></strong><br><?= htmlspecialchars($lang['categorias'] ?? 'Categorías') ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center">
                <span class="material-symbols-outlined" style="font-size: 10rem; color: rgba(255,255,255,0.04);">shopping_bag</span>
            </div>
        </div>
    </div>
</section>

<!-- ── Características ── -->
<section class="features-section">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color: #1e293b;"><?= htmlspecialchars($lang['landing_caracteristicas'] ?? '¿Por qué elegir TiendaUTP?') ?></h2>
        </div>
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #eef2ff;">
                        <span class="material-symbols-outlined" style="color: #4f46e5;">verified</span>
                    </div>
                    <h5 class="feature-title"><?= htmlspecialchars($lang['landing_feat1_tit'] ?? 'Compra segura') ?></h5>
                    <p class="feature-desc"><?= htmlspecialchars($lang['landing_feat1_desc'] ?? 'Cada venta se protege con firma digital y hash de integridad, garantizando que tus datos no sean alterados.') ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #d1fae5;">
                        <span class="material-symbols-outlined" style="color: #059669;">category</span>
                    </div>
                    <h5 class="feature-title"><?= htmlspecialchars($lang['landing_feat2_tit'] ?? 'Múltiples categorías') ?></h5>
                    <p class="feature-desc"><?= htmlspecialchars($lang['landing_feat2_desc'] ?? 'Navega por una amplia variedad de productos organizados en categorías para encontrar lo que buscas rápidamente.') ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #fef3c7;">
                        <span class="material-symbols-outlined" style="color: #d97706;">description</span>
                    </div>
                    <h5 class="feature-title"><?= htmlspecialchars($lang['landing_feat3_tit'] ?? 'Factura al instante') ?></h5>
                    <p class="feature-desc"><?= htmlspecialchars($lang['landing_feat3_desc'] ?? 'Recibe tu factura digital al momento de confirmar tu compra, con respaldo de autenticidad verificable.') ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #ede9fe;">
                        <span class="material-symbols-outlined" style="color: #7c3aed;">receipt_long</span>
                    </div>
                    <h5 class="feature-title"><?= htmlspecialchars($lang['landing_feat4_tit'] ?? 'Compra respaldada') ?></h5>
                    <p class="feature-desc"><?= htmlspecialchars($lang['landing_feat4_desc'] ?? 'Cada compra queda registrada con verificación de integridad, dándole trazabilidad real a tu pedido.') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA ── -->
<section class="cta-section">
    <div class="container">
        <h3 class="cta-title"><?= htmlspecialchars($lang['landing_cta_tit'] ?? '¿Listo para empezar?') ?></h3>
        <p class="cta-desc"><?= htmlspecialchars($lang['landing_cta_desc'] ?? 'Descubre todos los productos que tenemos para ti. Tu próxima compra está a un clic de distancia.') ?></p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>producto" class="hero-btn d-inline-flex align-items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 1.2rem;">arrow_forward</span>
                <?= htmlspecialchars($lang['explorar_catalogo'] ?? 'Explorar catálogo') ?>
            </a>
            <?php if (!isset($_SESSION['id_usuario'])): ?>
            <a href="<?= BASE_URL ?>auth/registro" class="btn d-inline-flex align-items-center gap-2 px-4 py-2 fw-semibold"
               style="border: 1px solid #e2e8f0; color: #1e293b; border-radius: 0.5rem;">
                <?= htmlspecialchars($lang['crear_cuenta'] ?? 'Crear cuenta') ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function() {
    var startTime = Date.now();
    var visitaId = null, visitaToken = null;
    var pagina = window.location.pathname;
    var baseUrl = '<?= BASE_URL ?>';

    function registrarVisita() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', baseUrl + 'visita/registrar', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try { var r = JSON.parse(xhr.responseText); visitaId = r.id_visita; visitaToken = r.token; } catch(e) {}
            }
        };
        xhr.send('pagina=' + encodeURIComponent(pagina));
    }

    function actualizarTiempo() {
        if (visitaId) {
            var elapsed = Math.floor((Date.now() - startTime) / 1000);
            var data = 'id_visita=' + visitaId + '&tiempo_segundos=' + elapsed + '&token=' + encodeURIComponent(visitaToken || '');
            if (navigator.sendBeacon) {
                navigator.sendBeacon(baseUrl + 'visita/actualizarTiempo', data);
            }
        }
    }

    if (document.readyState === 'complete') { registrarVisita(); }
    else { window.addEventListener('load', registrarVisita); }
    window.addEventListener('pagehide', actualizarTiempo);
    window.addEventListener('beforeunload', actualizarTiempo);
})();
</script>
