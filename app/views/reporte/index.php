<style>
    .section-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
        padding: 1.25rem;
        margin-bottom: 1.25rem;
    }
    .section-card h6 {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    .metric-block {
        padding: 1rem;
        border-radius: 0.625rem;
        text-align: center;
    }
    .metric-block .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    .metric-block .label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
    }
    .progress-bar-bg {
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
        background: #4f46e5;
        transition: width 0.3s ease;
    }
    .producto-thumb {
        width: 36px;
        height: 36px;
        border-radius: 0.5rem;
        object-fit: cover;
        border: 1px solid #e2e8f0;
    }
    .ranking-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .ranking-item:last-child {
        border-bottom: none;
    }
    .ranking-pos {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .ranking-pos.top {
        background: #1e293b;
        color: #fff;
    }
    .ranking-pos.mid {
        background: #e2e8f0;
        color: #64748b;
    }
    .ranking-pos.bot {
        background: #f1f5f9;
        color: #94a3b8;
    }
    .rank-name {
        flex: 1;
        font-size: 0.875rem;
        color: #1e293b;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rank-count {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        flex-shrink: 0;
    }
</style>

<div class="container py-4">

    <div class="d-flex align-items-center gap-2 mb-4">
        <span class="material-symbols-outlined" style="font-size: 1.5rem; color: #1e293b;">assessment</span>
        <h4 class="fw-bold mb-0" style="color: #1e293b;">Reportes</h4>
    </div>

    <!-- ── Selector de rango de fechas ── -->
    <div class="section-card">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size: 0.8rem; font-weight: 600; color: #64748b;">Desde</label>
                <input type="date" class="form-control form-control-sm" name="desde" value="<?= htmlspecialchars($desde) ?>"
                       style="border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.85rem;">
            </div>
            <div class="col-auto">
                <label class="form-label mb-1" style="font-size: 0.8rem; font-weight: 600; color: #64748b;">Hasta</label>
                <input type="date" class="form-control form-control-sm" name="hasta" value="<?= htmlspecialchars($hasta) ?>"
                       style="border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.85rem;">
            </div>
            <div class="col-auto d-flex gap-1">
                <button class="btn btn-sm px-3 fw-semibold text-white" style="background: #1e293b; border-radius: 0.375rem;" onclick="aplicarFechas()">
                    <span class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle;">search</span> Aplicar
                </button>
                <a href="<?= BASE_URL ?>reporte" class="btn btn-sm px-3 fw-semibold" style="border: 1px solid #e2e8f0; border-radius: 0.375rem; color: #64748b;">
                    Reiniciar
                </a>
            </div>
        </div>
    </div>

    <?php
    $totalVentas  = (float) ($resumen['suma_ventas'] ?? 0);
    $totalCostos  = (float) ($resumen['total_costos'] ?? 0);
    $ganancia     = (float) ($resumen['ganancia_neta'] ?? 0);
    $margen       = $totalVentas > 0 ? round($ganancia / $totalVentas * 100, 1) : 0;
    $totalVentasCount = (int) ($resumen['total_ventas'] ?? 0);
    ?>

    <!-- ── Ventas vs Costos ── -->
    <div class="section-card">
        <h6><span class="material-symbols-outlined" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.35rem;">monetization_on</span>Ventas vs Costos</h6>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="metric-block" style="background: #eef2ff;">
                    <div class="value" style="color: #4f46e5;">$<?= number_format($totalVentas, 2, '.', '') ?></div>
                    <div class="label">Total vendido</div>
                    <div style="font-size:0.75rem; color:#64748b;"><?= $totalVentasCount ?> ventas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-block" style="background: #fef2f2;">
                    <div class="value" style="color: #dc2626;">$<?= number_format($totalCostos, 2, '.', '') ?></div>
                    <div class="label">Total en costos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-block" style="background: #d1fae5;">
                    <div class="value" style="color: #059669;">$<?= number_format($ganancia, 2, '.', '') ?></div>
                    <div class="label">Ganancia neta</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-block" style="background: #fef3c7;">
                    <div class="value" style="color: #d97706;"><?= $margen ?>%</div>
                    <div class="label">Margen</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Top 5 productos más vendidos ── -->
    <div class="section-card">
        <h6><span class="material-symbols-outlined" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.35rem;">trending_up</span>Top 5 productos más vendidos</h6>
        <?php if (empty($topProductos)): ?>
        <p class="text-muted small mb-0">No hay ventas en el rango seleccionado.</p>
        <?php else:
            $maxCantidad = max(array_map(fn($r) => (int) $r['total_cantidad'], $topProductos));
        ?>
        <div class="d-flex flex-column gap-1">
            <?php foreach ($topProductos as $i => $p): ?>
            <?php $pct = $maxCantidad > 0 ? round(((int) $p['total_cantidad']) / $maxCantidad * 100) : 0; ?>
            <div class="ranking-item">
                <span class="ranking-pos <?= $i < 3 ? 'top' : 'mid' ?>"><?= $i + 1 ?></span>
                <?php if (!empty($p['imagen'])): ?>
                <img src="<?= BASE_URL . htmlspecialchars($p['imagen']) ?>" alt="" class="producto-thumb">
                <?php else: ?>
                <div class="producto-thumb d-flex align-items-center justify-content-center" style="background:#f1f5f9;">
                    <span class="material-symbols-outlined" style="font-size:1rem;color:#94a3b8;">inventory_2</span>
                </div>
                <?php endif; ?>
                <span class="rank-name"><?= htmlspecialchars($p['nombre']) ?></span>
                <span class="rank-count"><?= (int) $p['total_cantidad'] ?> vendidos</span>
                <span style="font-size:0.8rem;color:#94a3b8;flex-shrink:0;">$<?= number_format((float) $p['total_monto'], 2, '.', '') ?></span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── 10 más visitados / 10 menos visitados ── -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="section-card">
                <h6><span class="material-symbols-outlined" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.35rem;">visibility</span>10 más visitados</h6>
                <?php if (empty($topMas)): ?>
                <p class="text-muted small mb-0">Sin datos de visitas en el rango seleccionado.</p>
                <?php else: ?>
                <div class="d-flex flex-column">
                    <?php foreach ($topMas as $i => $p): ?>
                    <div class="ranking-item">
                        <span class="ranking-pos <?= $i < 3 ? 'top' : ($i < 6 ? 'mid' : 'bot') ?>"><?= $i + 1 ?></span>
                        <span class="rank-name"><?= htmlspecialchars($p['nombre']) ?></span>
                        <span class="rank-count"><?= (int) $p['visitas'] ?> visitas</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="section-card">
                <h6><span class="material-symbols-outlined" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.35rem;">visibility_off</span>10 menos visitados</h6>
                <?php if (empty($topMenos)): ?>
                <p class="text-muted small mb-0">Sin datos de visitas en el rango seleccionado.</p>
                <?php else: ?>
                <div class="d-flex flex-column">
                    <?php foreach ($topMenos as $i => $p): ?>
                    <div class="ranking-item">
                        <span class="ranking-pos bot"><?= $i + 1 ?></span>
                        <span class="rank-name"><?= htmlspecialchars($p['nombre']) ?></span>
                        <span class="rank-count"><?= (int) $p['visitas'] ?> visitas</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
function aplicarFechas() {
    var inputs = document.querySelectorAll('input[name="desde"], input[name="hasta"]');
    var params = [];
    inputs.forEach(function(inp) {
        if (inp.value) params.push(inp.name + '=' + encodeURIComponent(inp.value));
    });
    window.location.href = '<?= BASE_URL ?>reporte' + (params.length ? '?' + params.join('&') : '');
}
</script>