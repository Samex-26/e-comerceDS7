<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    body { background-color: var(--bg-surface); }
    .integrity-valid { background: #d1fae5; border-left: 4px solid #10b981; }
    .integrity-invalid { background: #fee2e2; border-left: 4px solid #ef4444; }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-3" style="color: var(--primary);">
        <span class="material-symbols-outlined align-middle me-2">receipt</span>
        Detalle de Venta
    </h2>

    <div class="card mb-4" style="border: 1px solid #e2e8f0; border-radius: 0.75rem;">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted text-uppercase fw-semibold">Orden</small>
                    <p class="fw-bold fs-5 mb-0" style="color: var(--primary);">
                        #UTP-<?= str_pad((int) $venta['id_venta'], 5, '0', STR_PAD_LEFT) ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted text-uppercase fw-semibold">Fecha</small>
                    <p class="fw-bold mb-0"><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted text-uppercase fw-semibold">Total</small>
                    <p class="fw-bold fs-5 mb-0" style="color: var(--secondary);">
                        $<?= number_format((float) $venta['total'], 2, '.', '') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3" style="color: var(--primary);">Productos</h5>
    <div class="table-responsive mb-4">
        <table class="table align-middle bg-white rounded-3 overflow-hidden" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background: var(--primary); color: white;">
                <tr>
                    <th class="p-3">Producto</th>
                    <th class="p-3 text-center">Cantidad</th>
                    <th class="p-3 text-end">Precio</th>
                    <th class="p-3 text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($venta['detalle'])): ?>
                    <?php foreach ($venta['detalle'] as $det): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td class="p-3 fw-semibold"><?= htmlspecialchars($det['nombre_producto'] ?? $det['id_producto']) ?></td>
                            <td class="p-3 text-center"><?= (int) $det['cantidad'] ?></td>
                            <td class="p-3 text-end">$<?= number_format((float) $det['precio_unitario'], 2, '.', '') ?></td>
                            <td class="p-3 text-end fw-bold">$<?= number_format((float) $det['subtotal'], 2, '.', '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="p-3 text-muted text-center">No hay detalle disponible.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h5 class="fw-bold mb-3" style="color: var(--primary);">
        <span class="material-symbols-outlined align-middle me-1">verified</span>
        Verificación de Integridad
    </h5>

    <div class="p-4 rounded-3 mb-3 <?= $firmaValida ? 'integrity-valid' : 'integrity-invalid' ?>">
        <div class="d-flex align-items-center gap-3">
            <span class="material-symbols-outlined" style="font-size: 2rem; color: <?= $firmaValida ? '#10b981' : '#ef4444' ?>;">
                <?= $firmaValida ? 'verified' : 'cancel' ?>
            </span>
            <div>
                <h6 class="fw-bold mb-1">
                    <?= $firmaValida ? 'Firma digital válida' : '¡Alerta! La firma digital NO coincide' ?>
                </h6>
                <p class="mb-0 small text-muted">
                    <?php if ($firmaValida): ?>
                        Los datos de esta venta no han sido alterados desde su emisión.
                    <?php else: ?>
                        Los datos de esta venta podrían haber sido modificados. Verifique con el administrador.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (isset($firmaRegenerada)): ?>
        <details class="mb-3">
            <summary class="text-muted small" style="cursor: pointer;">Ver detalles técnicos</summary>
            <div class="mt-2 p-3 rounded-3" style="background: #f8fafc; font-family: monospace; font-size: 0.85rem;">
                <p class="mb-1"><strong>Hash almacenado:</strong><br><code style="word-break: break-all;"><?= htmlspecialchars($venta['hash_datos'] ?? '—') ?></code></p>
                <p class="mb-1"><strong>Hash recalculado:</strong><br><code style="word-break: break-all;"><?= htmlspecialchars($hashActual ?? '—') ?></code></p>
                <p class="mb-0"><strong>Firma almacenada:</strong><br><code style="word-break: break-all;"><?= htmlspecialchars($venta['firma_digital'] ?? '—') ?></code></p>
            </div>
        </details>
    <?php endif; ?>

    <div class="d-flex gap-2 mt-2">
        <a href="<?= BASE_URL ?>factura/generar/<?= (int) $venta['id_venta'] ?>" class="btn" style="border: 1px solid #e2e8f0; color: var(--primary);">
            <span class="material-symbols-outlined align-middle">picture_as_pdf</span>
            Descargar factura
        </a>
        <a href="<?= BASE_URL ?>venta/historial" class="btn" style="border: 1px solid #e2e8f0; color: var(--primary);">
            <span class="material-symbols-outlined align-middle">arrow_back</span>
            Volver al historial
        </a>
    </div>
</div>
