<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    body { background-color: var(--bg-surface); }
    .badge-issued { background: #dbeafe; color: #1e40af; }
    .badge-success { background: #d1fae5; color: #065f46; }
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4" style="color: var(--primary);">
        <span class="material-symbols-outlined align-middle me-2">receipt_long</span>
        Mis Compras
    </h2>

    <?php if (empty($ventas)): ?>
        <div class="text-center py-5">
            <span class="material-symbols-outlined" style="font-size: 64px; color: #cbd5e1;">receipt_long</span>
            <h4 class="mt-3 text-muted">No has realizado compras aún</h4>
            <a href="<?= BASE_URL ?>producto" class="btn mt-3" style="background: var(--secondary); color: white; border-radius: 0.5rem; padding: 0.6rem 2rem;">
                Ir al catálogo
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle bg-white rounded-3 overflow-hidden" style="border-collapse: separate; border-spacing: 0 0.5rem;">
                <thead style="background: var(--primary); color: white;">
                    <tr>
                        <th class="p-3">Orden</th>
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Total</th>
                        <th class="p-3">Integridad</th>
                        <th class="p-3 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v): ?>
                        <tr class="bg-white shadow-sm rounded-3" style="border: 1px solid #e2e8f0;">
                            <td class="p-3">
                                <span class="fw-bold" style="color: var(--primary);">
                                    #UTP-<?= str_pad((int) $v['id_venta'], 5, '0', STR_PAD_LEFT) ?>
                                </span>
                            </td>
                            <td class="p-3"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                            <td class="p-3 fw-bold" style="color: var(--secondary);">$<?= number_format((float) $v['total'], 2, '.', '') ?></td>
                            <td class="p-3">
                                <?php if (!empty($v['firma_digital'])): ?>
                                    <span class="badge badge-issued rounded-pill px-3 py-2">
                                        <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">verified</span>
                                        Firmada
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?= BASE_URL ?>factura/generar/<?= (int) $v['id_venta'] ?>" class="btn btn-sm" style="border: 1px solid #e2e8f0; color: var(--primary);">
                                        <span class="material-symbols-outlined align-middle" style="font-size: 1.1rem;">picture_as_pdf</span>
                                        Factura
                                    </a>
                                    <a href="<?= BASE_URL ?>venta/verificarIntegridad/<?= (int) $v['id_venta'] ?>" class="btn btn-sm" style="border: 1px solid #e2e8f0; color: var(--primary);">
                                        <span class="material-symbols-outlined align-middle" style="font-size: 1.1rem;">visibility</span>
                                        Ver detalle
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
