<style>
    .breadcrumb-custom { background: transparent; padding: 0; margin-bottom: 1rem; }
    .breadcrumb-custom .breadcrumb-item + .breadcrumb-item::before { content: "›"; }
    .category-badge { background-color: #1e293b; color: white; padding: 0.25rem 1rem; border-radius: 50px; font-size: 0.85rem; display: inline-block; }
    .product-title { font-size: 32px; font-weight: 700; font-family: 'Inter', sans-serif; }
    .star-filled { color: #fd761a; }
    .star-empty { color: #d1d5db; }
    .price-block { background-color: #f2f4f6; border-radius: 12px; padding: 1.25rem; }
    .current-price { font-size: 1.75rem; font-weight: 700; color: #1e293b; }
    .current-price-sale { font-size: 1.75rem; font-weight: 700; color: #dc2626; }
    .old-price { font-size: 1rem; color: #9ca3af; text-decoration: line-through; }
    .discount-badge { background-color: #dc2626; color: white; font-size: 0.8rem; padding: 0.15rem 0.6rem; border-radius: 50px; }
    .stock-disponible { color: #16a34a; font-weight: 500; }
    .qty-selector { display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
    .qty-selector button { background: white; border: none; width: 40px; height: 40px; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #1e293b; user-select: none; }
    .qty-selector button:hover { background: #f3f4f6; }
    .qty-selector input { width: 60px; text-align: center; border: none; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; height: 40px; outline: none; font-size: 1rem; -moz-appearance: textfield; }
    .qty-selector input::-webkit-outer-spin-button,
    .qty-selector input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .btn-agregar { background-color: #fd761a; color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; font-size: 1rem; }
    .btn-agregar:hover { background-color: #e06500; color: white; }
    .main-image-container { border: 1px solid #e2e8f0; background: white; border-radius: 12px; overflow: hidden; }
    .main-image-container img { transition: transform 0.3s ease; width: 100%; height: 450px; object-fit: contain; }
    .main-image-container:hover img { transform: scale(1.05); }
    .thumb-img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: opacity 0.2s; }
    .thumb-img:hover { opacity: 0.7; }
    .thumb-active { border: 2px solid #1e293b !important; }
    .thumb-inactive { border: 1px solid #e2e8f0; }
    .warranty-item { display: flex; align-items: center; gap: 0.5rem; color: #4b5563; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .related-card .card-img-top { height: 200px; object-fit: cover; }
    .related-card .btn-icon { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: #fd761a; color: white; border: none; text-decoration: none; }
    .related-card .btn-icon:hover { background: #e06500; color: white; }
</style>

<div class="container mt-4">
    <a href="javascript:history.back()" class="btn px-0 mb-2 d-inline-flex align-items-center gap-1" style="color: var(--primary); font-weight: 600; border: none; background: none;">
        <span class="material-symbols-outlined">arrow_back</span>
        Volver
    </a>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-custom">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-decoration-none">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>producto" class="text-decoration-none"><?= htmlspecialchars($producto['categoria_nombre'] ?? '') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($producto['nombre']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-7">
            <div class="main-image-container mb-3">
                <?php if (!empty($producto['imagen'])): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($producto['imagen']) ?>"
                         alt="<?= htmlspecialchars($producto['nombre']) ?>"
                         id="mainImage">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-light" style="height: 450px;">
                        <span class="text-muted fs-4"><?= htmlspecialchars($lang['sin_imagen'] ?? 'Sin imagen') ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2">
                <?php $imgSrc = !empty($producto['imagen']) ? BASE_URL . htmlspecialchars($producto['imagen']) : ''; ?>
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <?php if (!empty($producto['imagen'])): ?>
                        <img src="<?= $imgSrc ?>"
                             class="thumb-img <?= $i === 0 ? 'thumb-active' : 'thumb-inactive' ?>"
                             alt="Vista <?= $i + 1 ?>"
                             onclick="cambiarImagen(this, '<?= $imgSrc ?>')">
                    <?php else: ?>
                        <div class="thumb-img thumb-inactive bg-light d-flex align-items-center justify-content-center text-muted" style="font-size:0.7rem">No img</div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <span class="category-badge mb-2"><?= htmlspecialchars($producto['categoria_nombre'] ?? '') ?></span>

            <h1 class="product-title mb-2"><?= htmlspecialchars($producto['nombre']) ?></h1>

            <div class="d-flex align-items-center gap-2 mb-3">
                <div>
                    <span class="material-symbols-outlined star-filled">star</span>
                    <span class="material-symbols-outlined star-filled">star</span>
                    <span class="material-symbols-outlined star-filled">star</span>
                    <span class="material-symbols-outlined star-filled">star</span>
                    <span class="material-symbols-outlined star-empty">star</span>
                </div>
                <span class="text-muted small">(0 reseñas)</span>
            </div>

            <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

            <?php $onSale = !empty($producto['precio_oferta']) && $producto['precio_oferta'] > 0; ?>
            <div class="price-block mb-3">
                <?php if ($onSale): ?>
                    <?php $discount = round((1 - $producto['precio_oferta'] / $producto['precio']) * 100); ?>
                    <span class="old-price">$<?= number_format($producto['precio'], 2, '.', '') ?></span>
                    <span class="discount-badge ms-2">-<?= $discount ?>%</span>
                    <div class="current-price-sale">$<?= number_format($producto['precio_oferta'], 2, '.', '') ?></div>
                <?php else: ?>
                    <div class="current-price">$<?= number_format($producto['precio'], 2, '.', '') ?></div>
                <?php endif; ?>
            </div>

            <?php if ((int) $producto['cantidad'] > 0): ?>
                <div class="d-flex align-items-center gap-2 mb-2 stock-disponible">
                    <span class="material-symbols-outlined" style="font-size:1.2rem">check_circle</span>
                    <span>Stock Disponible (<?= (int) $producto['cantidad'] ?> unidades)</span>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center gap-2 mb-2 text-danger">
                    <span class="material-symbols-outlined" style="font-size:1.2rem">cancel</span>
                    <span>Sin stock</span>
                </div>
            <?php endif; ?>

            <div class="d-flex align-items-center gap-2 mb-3 text-muted">
                <span class="material-symbols-outlined" style="font-size:1.2rem">local_shipping</span>
                <span>Entrega estimada: 2-3 días hábiles</span>
            </div>

            <?php if ((int) $producto['cantidad'] > 0): ?>
                <form method="POST" action="<?= BASE_URL ?>carrito/agregar/<?= (int) $producto['id_producto'] ?>">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <label class="mb-0 text-muted">Cantidad:</label>
                        <div class="qty-selector">
                            <button type="button" onclick="decrementarCantidad()">−</button>
                            <input type="number" name="cantidad" id="inputCantidad" value="1" min="1" max="<?= (int) $producto['cantidad'] ?>">
                            <button type="button" onclick="incrementarCantidad(<?= (int) $producto['cantidad'] ?>)">+</button>
                        </div>
                    </div>
                    <button type="submit" class="btn-agregar w-100 d-flex align-items-center justify-content-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:1.2rem">shopping_cart</span>
                        Agregar al carrito
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg w-100" disabled>Sin stock</button>
            <?php endif; ?>

            <hr>
            <div class="warranty-item">
                <span class="material-symbols-outlined" style="color:#4b5563;">verified_user</span>
                <span>Garantía oficial de 12 meses</span>
            </div>
            <div class="warranty-item">
                <span class="material-symbols-outlined" style="color:#4b5563;">verified_user</span>
                <span>Devolución gratis (30 días)</span>
            </div>
        </div>
    </div>

    <?php if (isset($productosRelacionados) && !empty($productosRelacionados)): ?>
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold mb-0">Productos Relacionados</h3>
                <p class="text-muted mb-0">Otros productos que podrían interesarte</p>
            </div>
            <a href="<?= BASE_URL ?>producto" class="btn btn-outline-dark btn-sm">Ver todos</a>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($productosRelacionados as $rel): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm related-card">
                        <?php if (!empty($rel['imagen'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($rel['imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($rel['nombre']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                                <span class="text-muted"><?= htmlspecialchars($lang['sin_imagen'] ?? 'Sin imagen') ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <span class="category-badge align-self-start mb-2" style="font-size:0.7rem"><?= htmlspecialchars($rel['categoria_nombre'] ?? '') ?></span>
                            <h6 class="card-title"><?= htmlspecialchars($rel['nombre']) ?></h6>
                            <?php if (!empty($rel['precio_oferta']) && $rel['precio_oferta'] > 0): ?>
                                <p class="card-text mb-2">
                                    <span class="text-decoration-line-through text-muted small">$<?= number_format($rel['precio'], 2, '.', '') ?></span>
                                    <span class="fw-bold" style="color:#1e293b;">$<?= number_format($rel['precio_oferta'], 2, '.', '') ?></span>
                                </p>
                            <?php else: ?>
                                <p class="card-text fw-bold mb-2" style="color:#1e293b;">$<?= number_format($rel['precio'], 2, '.', '') ?></p>
                            <?php endif; ?>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <a href="<?= BASE_URL ?>producto/detalle/<?= (int) $rel['id_producto'] ?>" class="btn btn-sm btn-outline-dark">Ver detalle</a>
                                <a href="<?= BASE_URL ?>carrito/agregar/<?= (int) $rel['id_producto'] ?>" class="btn-icon">
                                    <span class="material-symbols-outlined" style="font-size:1.1rem">shopping_cart</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function cambiarImagen(el, src) {
    document.querySelectorAll('.thumb-img').forEach(function(t) {
        t.classList.remove('thumb-active');
        t.classList.add('thumb-inactive');
    });
    el.classList.remove('thumb-inactive');
    el.classList.add('thumb-active');
    document.getElementById('mainImage').src = src;
}
function incrementarCantidad(max) {
    var input = document.getElementById('inputCantidad');
    var val = parseInt(input.value) || 1;
    if (val < max) input.value = val + 1;
}
function decrementarCantidad() {
    var input = document.getElementById('inputCantidad');
    var val = parseInt(input.value) || 1;
    if (val > 1) input.value = val - 1;
}
</script>

<script>
(function() {
    var startTime = Date.now();
    var visitaId = null;
    var pagina = window.location.pathname;
    var baseUrl = '<?= BASE_URL ?>';
    var idProducto = <?= (int) ($producto['id_producto'] ?? 0) ?>;

    function registrarVisita() {
        var params = 'pagina=' + encodeURIComponent(pagina);
        if (idProducto > 0) params += '&id_producto=' + idProducto;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', baseUrl + 'visita/registrar', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                try { visitaId = JSON.parse(xhr.responseText).id_visita; } catch(e) {}
            }
        };
        xhr.send(params);
    }

    function actualizarTiempo() {
        if (visitaId) {
            var elapsed = Math.floor((Date.now() - startTime) / 1000);
            var data = 'id_visita=' + visitaId + '&tiempo_segundos=' + elapsed;
            if (navigator.sendBeacon) {
                navigator.sendBeacon(baseUrl + 'visita/actualizarTiempo', data);
            }
        }
    }

    if (document.readyState === 'complete') {
        registrarVisita();
    } else {
        window.addEventListener('load', registrarVisita);
    }
    window.addEventListener('pagehide', actualizarTiempo);
    window.addEventListener('beforeunload', actualizarTiempo);
})();
</script>
