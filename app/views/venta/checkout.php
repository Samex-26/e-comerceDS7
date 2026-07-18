<style>
    :root {
        --primary: #1e293b;
        --secondary: #fd761a;
        --bg-surface: #f7f9fb;
        --surface: #ffffff;
    }
    body { background-color: var(--bg-surface); }
    .card-checkout { border: 1px solid #e2e8f0; border-radius: 0.75rem; }
    .btn-secondary-custom { background-color: var(--secondary); color: white; border: none; }
    .btn-secondary-custom:hover { background-color: #e06500; }
    .text-secondary-custom { color: var(--secondary); }
    .text-primary-custom { color: var(--primary); }
</style>

<div class="container py-5" style="max-width: 1200px;">
    <h2 class="fw-bold mb-4" style="color: var(--primary);">Checkout</h2>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger border-0 rounded-3 py-3">
            <?php foreach ($errores as $e): ?>
                <p class="mb-0"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>venta/checkout">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="row g-4">
            <!-- Left Column: Buyer Info -->
            <div class="col-lg-7">
                <div class="card-checkout bg-white p-4 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="material-symbols-outlined" style="color: var(--primary);">person_check</span>
                        <h5 class="fw-bold mb-0" style="color: var(--primary);">Datos del Comprador</h5>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-semibold">Nombre Completo</label>
                            <div class="p-3 rounded-3" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                                <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted text-uppercase fw-semibold">Correo</label>
                            <div class="p-3 rounded-3" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                                <?= htmlspecialchars($_SESSION['email'] ?? $_SESSION['usuario_email'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-checkout bg-white p-4 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="material-symbols-outlined" style="color: var(--primary);">payments</span>
                        <h5 class="fw-bold mb-0" style="color: var(--primary);">Método de Pago</h5>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="border: 1px solid var(--primary); background: #f0f4ff;">
                        <div class="d-flex align-items-center gap-3">
                            <span class="material-symbols-outlined" style="color: var(--primary);">account_balance_wallet</span>
                            <div>
                                <p class="fw-bold mb-0 small">Tarjeta de Crédito / Débito</p>
                                <p class="mb-0 text-muted small">Pago contraentrega</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined" style="color: var(--primary);">check_circle</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary-custom w-100 py-3 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2">
                    <span class="material-symbols-outlined">lock</span>
                    Confirmar compra
                </button>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="col-lg-5">
                <div class="card-checkout bg-white overflow-hidden">
                    <div class="p-3" style="background: var(--primary);">
                        <h5 class="fw-bold mb-0 text-white">Resumen de la orden</h5>
                    </div>
                    <div class="p-4">
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex gap-3 mb-3 pb-3 <?= $item !== end($items) ? 'border-bottom' : '' ?>" style="border-color: #e2e8f0 !important;">
                                <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 0.375rem; overflow: hidden; border: 1px solid #e2e8f0; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                    <?php if (!empty($item['imagen'])): ?>
                                        <img src="<?= BASE_URL . htmlspecialchars($item['imagen']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <span class="material-symbols-outlined" style="color: #94a3b8;">inventory_2</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1" style="color: var(--primary);"><?= htmlspecialchars($item['nombre']) ?></h6>
                                    <p class="small text-muted mb-1">Cantidad: <?= (int) $item['cantidad'] ?></p>
                                    <p class="fw-bold mb-0" style="color: var(--secondary);">
                                        $<?= number_format($item['precio_unitario'], 2, '.', '') ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="p-4 pt-0">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Subtotal</span>
                            <span class="fw-semibold">$<?= number_format($total, 2, '.', '') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Envío</span>
                            <span style="color: var(--secondary); font-weight: 600;">Gratis</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold" style="color: var(--primary);">Total</span>
                            <span class="fw-bold fs-5" style="color: var(--primary);">$<?= number_format($total, 2, '.', '') ?></span>
                        </div>
                    </div>
                    <div class="p-3 text-center bg-light border-top">
                        <small class="text-muted d-flex align-items-center justify-content-center gap-1">
                            <span class="material-symbols-outlined" style="font-size: 14px;">verified_user</span>
                            Transacción segura
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
