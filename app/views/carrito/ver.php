<style>
    :root {
        --primary: #1e293b;
        --secondary: #fd761a;
        --bg-surface: #f7f9fb;
        --surface: #ffffff;
    }
    body { background-color: var(--bg-surface); }
    .card-cart { border: 1px solid #e2e8f0; border-radius: 0.5rem; }
    .btn-primary-custom { background-color: var(--primary); color: white; border: none; }
    .btn-primary-custom:hover { background-color: #0f172a; }
    .btn-secondary-custom { background-color: var(--secondary); color: white; border: none; }
    .btn-secondary-custom:hover { background-color: #e06500; }
    .text-secondary-custom { color: var(--secondary); }
    .text-primary-custom { color: var(--primary); }
    .bg-surface-custom { background-color: var(--bg-surface); }
    .qty-input { width: 70px; text-align: center; border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 0.375rem; }
</style>

<div class="container py-5" style="max-width: 1100px;">
    <h2 class="fw-bold mb-4" style="color: var(--primary);">Carrito de Compras</h2>

    <?php if (!empty($exito)): ?>
        <div class="alert alert-success border-0 rounded-3 py-3 d-flex align-items-center gap-2">
            <span class="material-symbols-outlined">check_circle</span>
            <?= htmlspecialchars($exito) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger border-0 rounded-3 py-3">
            <?php foreach ($errores as $e): ?>
                <p class="mb-0 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">error</span>
                    <?= htmlspecialchars($e) ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($carrito)): ?>
        <div class="text-center py-5">
            <span class="material-symbols-outlined" style="font-size: 64px; color: #cbd5e1;">shopping_cart</span>
            <h4 class="mt-3 text-muted">Tu carrito está vacío</h4>
            <p class="text-muted">Explora nuestro catálogo y agrega productos.</p>
            <a href="<?= BASE_URL ?>producto" class="btn btn-secondary-custom rounded-3 px-5 py-3 fw-bold mt-2">
                <span class="material-symbols-outlined me-2">arrow_back</span>
                Ir al catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <?php foreach ($carrito as $idP => $item): ?>
                    <div class="card-cart bg-white p-4 mb-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 0.5rem; overflow: hidden; border: 1px solid #e2e8f0; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <?php if (!empty($item['imagen'])): ?>
                                    <img src="<?= BASE_URL . htmlspecialchars($item['imagen']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <span class="material-symbols-outlined" style="color: #94a3b8; font-size: 32px;">inventory_2</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="color: var(--primary);"><?= htmlspecialchars($item['nombre']) ?></h6>
                                <p class="mb-0" style="color: var(--secondary); font-weight: 600;">
                                    $<?= number_format($item['precio_unitario'], 2, '.', '') ?>
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>carrito/actualizar/<?= (int) $idP ?>" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="number" name="cantidad" value="<?= (int) $item['cantidad'] ?>" min="1" class="qty-input">
                            <button type="submit" class="btn btn-sm btn-outline-secondary border-0" title="Actualizar">
                                <span class="material-symbols-outlined" style="font-size: 20px;">refresh</span>
                            </button>
                        </form>

                        <div class="text-end" style="min-width: 90px;">
                            <p class="fw-bold mb-1" style="color: var(--primary);">
                                $<?= number_format($item['subtotal'] ?? ($item['cantidad'] * $item['precio_unitario']), 2, '.', '') ?>
                            </p>
                            <form method="POST" action="<?= BASE_URL ?>carrito/eliminar/<?= (int) $idP ?>"
                               class="text-danger text-decoration-none small"
                               onclick="return confirm('¿Eliminar este producto del carrito?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" class="btn btn-link text-danger text-decoration-none small p-0"><span class="material-symbols-outlined" style="font-size: 18px;">delete</span> Quitar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-lg-4">
                <div class="card-cart bg-white p-4">
                    <h5 class="fw-bold mb-3" style="color: var(--primary);">Resumen</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold">$<?= number_format($total, 2, '.', '') ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Envío</span>
                        <span style="color: var(--secondary); font-weight: 600;">Gratis</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold" style="color: var(--primary);">Total</span>
                        <span class="fw-bold fs-5" style="color: var(--primary);">$<?= number_format($total, 2, '.', '') ?></span>
                    </div>
                    <a href="<?= BASE_URL ?>venta/checkout" class="btn btn-secondary-custom w-100 py-3 fw-bold rounded-3">
                        <span class="material-symbols-outlined me-2">lock</span>
                        Proceder al pago
                    </a>
                    <a href="<?= BASE_URL ?>producto" class="btn w-100 mt-2 py-2 fw-semibold" style="border: 1px solid #e2e8f0; color: var(--primary);">
                        Seguir comprando
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
