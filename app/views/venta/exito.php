<style>
    :root {
        --primary: #1e293b;
        --secondary: #fd761a;
        --bg-surface: #f7f9fb;
    }
    body { background-color: var(--bg-surface); }
    .success-card { border-radius: 0.75rem; overflow: hidden; border: 1px solid #e2e8f0; }
    .btn-secondary-custom { background-color: var(--secondary); color: white; border: none; }
    .btn-secondary-custom:hover { background-color: #e06500; }
    .btn-primary-custom { background-color: var(--primary); color: white; border: none; }
    .btn-primary-custom:hover { background-color: #0f172a; }
</style>

<div class="container py-5" style="max-width: 600px;">
    <div class="success-card bg-white">
        <div class="p-5 text-center" style="background: #10b981;">
            <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <span class="material-symbols-outlined text-white" style="color: #10b981 !important; font-size: 48px; font-variation-settings: 'FILL' 1;">check_circle</span>
            </div>
            <h3 class="fw-bold text-white mb-1">¡Compra confirmada!</h3>
            <p class="text-white-50 mb-0">Hemos enviado un correo con los detalles.</p>
        </div>

        <div class="p-5">
            <div class="p-4 rounded-3 text-center mb-4" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
                <p class="small text-muted text-uppercase fw-bold tracking-wider mb-1">Número de orden</p>
                <h2 class="fw-extrabold mb-0" style="color: var(--primary); letter-spacing: -0.02em;">
                    #UTP-<?= str_pad((int) $venta['id_venta'], 5, '0', STR_PAD_LEFT) ?>
                </h2>
            </div>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Total pagado</span>
                <span class="fw-bold fs-5" style="color: var(--primary);">$<?= number_format((float) $venta['total'], 2, '.', '') ?></span>
            </div>
            <div class="d-flex justify-content-between mb-4">
                <span class="text-muted">Fecha</span>
                <span><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></span>
            </div>

            <a href="<?= BASE_URL ?>factura/generar/<?= (int) $venta['id_venta'] ?>" class="btn btn-primary-custom w-100 py-3 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 mb-3">
                <span class="material-symbols-outlined">picture_as_pdf</span>
                Descargar factura (PDF)
            </a>
            <a href="<?= BASE_URL ?>producto" class="btn w-100 py-3 fw-semibold rounded-3 d-flex align-items-center justify-content-center gap-2" style="border: 1px solid #e2e8f0; color: var(--primary); background: white;">
                <span class="material-symbols-outlined">arrow_back</span>
                Volver al catálogo
            </a>

            <div class="d-flex justify-content-center gap-4 mt-4 small text-muted">
                <span class="d-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">schedule</span>
                    Listo para recojo en 24h
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">inventory_2</span>
                    Embalaje ecológico
                </span>
            </div>
        </div>
    </div>
</div>
