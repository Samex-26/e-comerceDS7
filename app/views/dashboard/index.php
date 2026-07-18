<style>
    .kpi-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
        padding: 1.25rem;
        transition: box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        box-shadow: 0 4px 20px rgba(30,41,59,0.05);
    }
    .kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }
    .kpi-label {
        font-size: 0.8rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
    }
    .variation-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
    }
    .variation-up { color: #059669; background: #d1fae5; }
    .variation-down { color: #dc2626; background: #fee2e2; }

    .chart-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #fff;
        padding: 1.25rem;
    }
    .chart-card h6 {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    .dashboard-table {
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #fff;
    }
    .dashboard-table table {
        margin-bottom: 0;
    }
    .dashboard-table th {
        background: #f8fafc;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
    }
    .dashboard-table td {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .estado-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 999px;
    }
    .estado-confirmada { background: #d1fae5; color: #059669; }
    .estado-pendiente  { background: #fef3c7; color: #d97706; }
</style>

<div class="container py-4">

    <div class="d-flex align-items-center gap-2 mb-4">
        <span class="material-symbols-outlined" style="font-size: 1.5rem; color: #1e293b;">dashboard</span>
        <h4 class="fw-bold mb-0" style="color: #1e293b;">Dashboard</h4>
    </div>

    <!-- ── KPI Cards ── -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: #eef2ff;">
                        <span class="material-symbols-outlined" style="color: #4f46e5;">shopping_cart</span>
                    </div>
                    <div>
                        <div class="kpi-value"><?= $kpiVentasConteo ?></div>
                        <div class="kpi-label">Ventas del mes</div>
                        <div class="mt-1" style="font-size: 0.8rem; color: #1e293b;">
                            $<?= number_format($kpiVentasSuma, 2, '.', '') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: #d1fae5;">
                        <span class="material-symbols-outlined" style="color: #059669;">payments</span>
                    </div>
                    <div>
                        <div class="kpi-value">$<?= number_format($kpiGanancia, 2, '.', '') ?></div>
                        <div class="kpi-label">Ganancia neta</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: #fef3c7;">
                        <span class="material-symbols-outlined" style="color: #d97706;">inventory_2</span>
                    </div>
                    <div>
                        <div class="kpi-value"><?= $kpiProductosVendidos ?></div>
                        <div class="kpi-label">Productos vendidos</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="kpi-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: #ede9fe;">
                        <span class="material-symbols-outlined" style="color: #7c3aed;">group</span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="kpi-value"><?= $kpiVisitantes ?></div>
                            <span class="variation-badge <?= $variacionVisitas >= 0 ? 'variation-up' : 'variation-down' ?>">
                                <?= ($variacionVisitas >= 0 ? '+' : '') . $variacionVisitas ?>%
                            </span>
                        </div>
                        <div class="kpi-label">Visitantes totales</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Charts ── -->
    <div class="row g-3 mb-4">
        <div class="col-md-7">
            <div class="chart-card">
                <h6><span class="material-symbols-outlined" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.35rem;">bar_chart</span>Ventas por mes</h6>
                <canvas id="chartVentasMes" height="200"></canvas>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-card">
                <h6><span class="material-symbols-outlined" style="font-size: 1.1rem; vertical-align: middle; margin-right: 0.35rem;">donut_small</span>Top categorías</h6>
                <canvas id="chartTopCategorias" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Últimas Ventas ── -->
    <div class="dashboard-table">
        <div class="px-3 py-3 border-bottom" style="background: #f8fafc;">
            <h6 class="fw-bold mb-0" style="color: #1e293b; font-size: 0.9rem;">
                <span class="material-symbols-outlined" style="font-size: 1.15rem; vertical-align: middle; margin-right: 0.35rem;">receipt_long</span>
                Últimas ventas
            </h6>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ultimasVentas)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No hay ventas registradas</td></tr>
                <?php else: ?>
                <?php foreach ($ultimasVentas as $v): ?>
                <tr>
                    <td><strong>#UTP-<?= str_pad((string) $v['id_venta'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= htmlspecialchars($v['cliente']) ?></td>
                    <td>$<?= number_format((float) $v['total'], 2, '.', '') ?></td>
                    <td><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                    <td><span class="estado-badge estado-<?= htmlspecialchars($v['estado']) ?>"><?= htmlspecialchars($v['estado']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var meses = <?= json_encode(array_map(fn($r) => $r['mes'], $ventasPorMes)) ?>;
    var ventasCount = <?= json_encode(array_map(fn($r) => (int) $r['total_ventas'], $ventasPorMes)) ?>;
    var ventasSuma  = <?= json_encode(array_map(fn($r) => (float) $r['suma_ventas'], $ventasPorMes)) ?>;

    new Chart(document.getElementById('chartVentasMes'), {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ventas ($)',
                data: ventasSuma,
                backgroundColor: '#4f46e5',
                borderRadius: 4,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(v) { return '$' + v.toFixed(0); } }
                }
            }
        }
    });

    var catLabels = <?= json_encode(array_map(fn($r) => $r['nombre'], $topCategorias)) ?>;
    var catData   = <?= json_encode(array_map(fn($r) => (int) $r['total_vendido'], $topCategorias)) ?>;

    new Chart(document.getElementById('chartTopCategorias'), {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: ['#4f46e5', '#fd761a', '#059669', '#d97706', '#7c3aed'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 12, font: { size: 11 } }
                }
            }
        }
    });
});
</script>