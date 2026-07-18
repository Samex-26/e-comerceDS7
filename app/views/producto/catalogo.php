<style>
    .card-product {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        transition: box-shadow 0.2s ease;
    }
    .card-product:hover {
        box-shadow: 0px 4px 20px rgba(30,41,59,0.05);
    }
    .card-product .img-wrap {
        aspect-ratio: 1 / 1;
        overflow: hidden;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    .card-product .img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .card-product .img-wrap:hover img {
        transform: scale(1.1);
    }
    .badge-discount {
        background-color: #fd761a;
        font-size: 0.75rem;
    }
    .price-primary {
        color: #18008f;
        font-size: 1.5rem;
        font-weight: 600;
    }
    .btn-primary-orange {
        background-color: #fd761a;
        color: white;
        letter-spacing: 0.05em;
        padding: 0.625rem 0;
    }
    .btn-primary-orange:hover {
        background-color: #e06500;
        color: white;
    }
    .pill {
        padding: 0.5rem 1.25rem;
        border-radius: 9999px;
        font-weight: 600;
        white-space: nowrap;
        text-decoration: none;
        transition: background-color 0.15s;
    }
    .pill-active {
        background-color: #1e293b;
        color: white;
    }
    .pill-inactive {
        background-color: #e6e8ea;
        color: #45474c;
    }
    .pill-inactive:hover {
        background-color: #d1d5db;
        color: #45474c;
    }
    .cookies-banner {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        border-top: 1px solid #e2e8f0;
        z-index: 1050;
        padding: 1rem 0;
        display: none;
    }
</style>

<div class="container my-4">

    <div class="mb-4 overflow-auto">
        <div class="d-flex gap-2" style="min-width: max-content;">
            <a href="<?= BASE_URL ?>producto"
               class="pill <?= $idCategoria === null ? 'pill-active' : 'pill-inactive' ?>">
                Todos
            </a>
            <?php foreach ($categorias as $cat): ?>
                <a href="<?= BASE_URL ?>producto/index/<?= (int) $cat['id_categoria'] ?>"
                   class="pill <?= $idCategoria !== null && (int) $idCategoria === (int) $cat['id_categoria'] ? 'pill-active' : 'pill-inactive' ?>">
                    <?= htmlspecialchars($cat['nombre']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($productos)): ?>
        <div class="text-center py-5">
            <span class="material-symbols-outlined" style="font-size: 4rem; color: #cbd5e1;">inventory_2</span>
            <h5 class="mt-3 text-secondary">No hay productos en esta categoría</h5>
            <a href="<?= BASE_URL ?>producto" class="btn btn-outline-secondary mt-3">
                Volver a Todos los productos
            </a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($productos as $p): ?>
                <div class="col">
                    <div class="card card-product h-100 border-0">
                        <div class="img-wrap position-relative">
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="<?= BASE_URL . htmlspecialchars($p['imagen']) ?>"
                                     alt="<?= htmlspecialchars($p['nombre']) ?>">
                            <?php else: ?>
                                <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                                    <span class="material-symbols-outlined text-muted" style="font-size: 3rem;">image</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($p['precio_oferta']) && $p['precio_oferta'] > 0): ?>
                                <span class="badge badge-discount position-absolute top-0 end-0 m-2">
                                    -<?= round((1 - $p['precio_oferta'] / $p['precio']) * 100) ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="fw-semibold mb-2" style="font-size: 1.25rem;">
                                <?= htmlspecialchars($p['nombre']) ?>
                            </h6>
                            <?php if (!empty($p['precio_oferta']) && $p['precio_oferta'] > 0): ?>
                                <p class="mb-2">
                                    <span class="price-primary">$<?= number_format($p['precio_oferta'], 2, '.', '') ?></span>
                                    <span class="text-decoration-line-through text-muted ms-2" style="font-size: 0.9rem;">
                                        $<?= number_format($p['precio'], 2, '.', '') ?>
                                    </span>
                                </p>
                            <?php else: ?>
                                <p class="price-primary mb-2">$<?= number_format($p['precio'], 2, '.', '') ?></p>
                            <?php endif; ?>
                            <div class="mt-auto">
                                <a href="<?= BASE_URL ?>carrito/agregar/<?= (int) $p['id_producto'] ?>"
                                   class="btn btn-primary-orange w-100 text-uppercase fw-semibold d-flex align-items-center justify-content-center gap-2">
                                    <span class="material-symbols-outlined" style="font-size: 1.25rem;">shopping_cart</span>
                                    <?= htmlspecialchars($lang['agregar_carrito']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="cookies-banner" id="cookieBanner">
    <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <p class="mb-0 text-muted small">
            Utilizamos cookies propias y de terceros para mejorar tu experiencia en nuestro sitio web.
            <a href="#" class="text-decoration-underline text-muted">Más información</a>
        </p>
        <button onclick="document.getElementById('cookieBanner').style.display='none'; localStorage.setItem('cookieConsent','true');"
                class="btn text-white px-4 py-2 flex-shrink-0" style="background-color: #1e293b;">
            Aceptar
        </button>
    </div>
</div>

<script>
    (function() {
        if (!localStorage.getItem('cookieConsent')) {
            document.getElementById('cookieBanner').style.display = '';
        }
    })();
</script>
