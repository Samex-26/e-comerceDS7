<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .modal-overlay { transition: opacity 0.3s ease; backdrop-filter: blur(4px); }
    .modal-content { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease; }
    .modal-hidden .modal-overlay { opacity: 0; pointer-events: none; }
    .modal-hidden .modal-content { opacity: 0; transform: scale(0.95) translateY(10px); pointer-events: none; }
    .toast-success { animation: slideIn 0.3s ease, fadeOut 0.3s ease 3.7s forwards; }
    .toast-error { animation: slideIn 0.3s ease, fadeOut 0.3s ease 4.7s forwards; }
    @keyframes slideIn { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-20px); } }
    .tr-hover:hover { background-color: #f8fafc; }
</style>

<?php
$editarId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
$editarEntrada = $editarId && isset($entrada) ? $entrada : null;
$showModal = $editarEntrada || !empty($errores);

$totalEntradas = 0;
$totalValor = 0;
$proveedoresUnicos = [];
foreach ($entradas as $e) {
    $totalEntradas += (int) $e['cantidad_ingresada'];
    $totalValor += (float) ($e['costo_unitario'] ?? 0) * (int) $e['cantidad_ingresada'];
    $proveedoresUnicos[$e['id_proveedor']] = true;
}
$proveedoresActivos = count($proveedoresUnicos);
?>

<?php if ($exito): ?>
<div style="position: fixed; top: 16px; right: 16px; z-index: 200; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px;" class="toast-success">
    <span class="material-symbols-outlined" style="color: #16a34a;">check_circle</span>
    <span style="font-weight: 500;"><?= htmlspecialchars($exito) ?></span>
</div>
<?php endif; ?>

<?php if (!empty($errores)): ?>
<div style="position: fixed; top: 16px; right: 16px; z-index: 200; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px; max-width: 448px;" class="toast-error">
    <span class="material-symbols-outlined" style="color: #dc2626;">error</span>
    <div>
        <?php foreach ($errores as $e): ?>
            <p style="font-size: 14px;"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div style="min-height: 100vh; display: flex; flex-direction: column;">
    <header style="height: 64px; background: white; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; padding: 0 32px; position: sticky; top: 0; z-index: 30;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;">Movimientos de Inventario</h2>
            <p style="font-size: 14px; color: #64748b; margin: 2px 0 0 0;">Gesti&oacute;n de entradas de stock y flujo de mercanc&iacute;a.</p>
        </div>
        <button onclick="toggleModal()" style="display: flex; align-items: center; gap: 8px; background: #1e293b; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='#1e293b'">
            <span class="material-symbols-outlined">add</span>
            Registrar entrada
        </button>
    </header>

    <main style="padding: 32px;">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px;">
            <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px;">
                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin: 0 0 4px 0;">Entradas (Total)</p>
                <p style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;"><?= number_format($totalEntradas, 0, '.', '') ?></p>
            </div>
            <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px;">
                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin: 0 0 4px 0;">Valor de Inventario</p>
                <p style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">$<?= number_format($totalValor, 2, '.', '') ?></p>
            </div>
            <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px;">
                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin: 0 0 4px 0;">Proveedores Activos</p>
                <p style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;"><?= $proveedoresActivos ?></p>
            </div>
            <div style="background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px;">
                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin: 0 0 4px 0;">Registros</p>
                <p style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;"><?= count($entradas) ?></p>
            </div>
        </div>

        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <div style="overflow-x: auto;">
                <table style="width: 100%; text-align: left; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Producto</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Proveedor</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Costo</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; text-align: center;">Cantidad</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Fecha</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Detalle</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entradas)): ?>
                            <tr>
                                <td colspan="7" style="padding: 48px 24px; text-align: center; color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 36px; margin-bottom: 8px; display: block;">inventory</span>
                                    No hay movimientos de inventario registrados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($entradas as $e): ?>
                            <tr class="tr-hover" style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 16px 24px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                            <span class="material-symbols-outlined">inventory_2</span>
                                        </div>
                                        <span style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($e['producto_nombre'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td style="padding: 16px 24px; color: #475569;"><?= htmlspecialchars($e['proveedor_nombre'] ?? '') ?></td>
                                <td style="padding: 16px 24px; color: #1e293b; font-weight: 500;">$<?= number_format((float) ($e['costo_unitario'] ?? 0), 2, '.', '') ?></td>
                                <td style="padding: 16px 24px; text-align: center;">
                                    <span style="background: #f1f5f9; padding: 4px 12px; border-radius: 9999px; font-weight: 700; font-size: 14px;"><?= (int) $e['cantidad_ingresada'] ?></span>
                                </td>
                                <td style="padding: 16px 24px; color: #475569;"><?= htmlspecialchars($e['fecha_entrada']) ?></td>
                                <td style="padding: 16px 24px; color: #64748b; font-style: italic; font-size: 14px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($e['detalle'] ?? '') ?></td>
                                <td style="padding: 16px 24px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                        <a href="<?= BASE_URL ?>inventario/admin?editar=<?= (int) $e['id_inventario'] ?>"
                                           style="padding: 8px; color: #64748b; border-radius: 8px; text-decoration: none; display: inline-flex;"
                                           onmouseover="this.style.color='#fd761a';this.style.background='#fff7ed'"
                                           onmouseout="this.style.color='#64748b';this.style.background='transparent'">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>inventario/eliminar/<?= (int) $e['id_inventario'] ?>" style="display:inline" onsubmit="return confirm('&iquest;Est&aacute; seguro de eliminar esta entrada?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <button type="submit" style="padding:8px;color:#64748b;border:0;background:transparent;display:inline-flex"><span class="material-symbols-outlined">delete</span></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px 24px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0;">
                <p style="font-size: 14px; color: #64748b; margin: 0;">Mostrando <?= count($entradas) ?> registro(s)</p>
            </div>
        </div>
    </main>
</div>

<div style="position: fixed; inset: 0; z-index: 100; <?= $showModal ? '' : 'pointer-events: none;' ?>" id="modalEntry" class="<?= $showModal ? '' : 'modal-hidden' ?>">
    <div class="modal-overlay" style="position: absolute; inset: 0; background: rgba(30,41,59,0.2); cursor: pointer;" onclick="toggleModal()"></div>
    <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <div class="modal-content" style="background: white; width: 100%; max-width: 576px; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid #e2e8f0; overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;"><?= $editarEntrada ? 'Editar Entrada de Stock' : 'Nueva Entrada de Stock' ?></h3>
                <button onclick="toggleModal()" style="border: none; background: transparent; color: #64748b; cursor: pointer; padding: 4px; display: flex;" onmouseover="this.style.color='#334155'" onmouseout="this.style.color='#64748b'">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form method="POST" action="<?= BASE_URL ?>inventario/admin" style="padding: 24px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" value="<?= $editarEntrada ? (int) $editarEntrada['id_inventario'] : '' ?>">

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 6px;">Producto <span style="color: #ef4444;">*</span></label>
                    <select name="id_producto" required
                            style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; background: white; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                            onfocus="this.style.borderColor='#1e293b';this.style.boxShadow='0 0 0 2px rgba(30,41,59,0.25)'"
                            onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'">
                        <option value="">Seleccionar producto</option>
                        <?php foreach ($productos as $prod): ?>
                            <option value="<?= (int) $prod['id_producto'] ?>"
                                <?= ((int) ($editarEntrada['id_producto'] ?? $old['id_producto'] ?? 0) === (int) $prod['id_producto']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prod['nombre']) ?> (Stock: <?= (int) $prod['cantidad'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 6px;">Proveedor <span style="color: #ef4444;">*</span></label>
                        <select name="id_proveedor" required
                                style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; background: white; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                                onfocus="this.style.borderColor='#1e293b';this.style.boxShadow='0 0 0 2px rgba(30,41,59,0.25)'"
                                onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'">
                            <option value="">Seleccionar proveedor</option>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?= (int) $prov['id_proveedor'] ?>"
                                    <?= ((int) ($editarEntrada['id_proveedor'] ?? $old['id_proveedor'] ?? 0) === (int) $prov['id_proveedor']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prov['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 6px;">Fecha <span style="color: #ef4444;">*</span></label>
                        <input type="date" name="fecha_entrada" required
                               style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                               onfocus="this.style.borderColor='#1e293b';this.style.boxShadow='0 0 0 2px rgba(30,41,59,0.25)'"
                               onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                               value="<?= htmlspecialchars($editarEntrada['fecha_entrada'] ?? $old['fecha_entrada'] ?? date('Y-m-d')) ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 6px;">Costo Unitario</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: 700;">$</span>
                            <input type="number" step="0.01" min="0" name="costo_unitario"
                                   style="width: 100%; padding: 12px 16px 12px 32px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                                   onfocus="this.style.borderColor='#1e293b';this.style.boxShadow='0 0 0 2px rgba(30,41,59,0.25)'"
                                   onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                                   placeholder="0.00"
                                   value="<?= htmlspecialchars($editarEntrada['costo_unitario'] ?? $old['costo_unitario'] ?? '') ?>">
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 6px;">Cantidad Ingresada <span style="color: #ef4444;">*</span></label>
                        <input type="number" min="1" name="cantidad_ingresada" required
                               style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                               onfocus="this.style.borderColor='#1e293b';this.style.boxShadow='0 0 0 2px rgba(30,41,59,0.25)'"
                               onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                               placeholder="0"
                               value="<?= htmlspecialchars($editarEntrada['cantidad_ingresada'] ?? $old['cantidad_ingresada'] ?? '') ?>">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 6px;">Detalle / Notas</label>
                    <textarea name="detalle" rows="3"
                              style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; resize: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                              onfocus="this.style.borderColor='#1e293b';this.style.boxShadow='0 0 0 2px rgba(30,41,59,0.25)'"
                              onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                              placeholder="A&ntilde;ade notas adicionales sobre esta entrada..."><?= htmlspecialchars($editarEntrada['detalle'] ?? $old['detalle'] ?? '') ?></textarea>
                </div>

                <div style="padding-top: 16px; display: flex; align-items: center; justify-content: flex-end; gap: 12px; border-top: 1px solid #e2e8f0;">
                    <button type="button" onclick="toggleModal()"
                            style="padding: 12px 24px; border: 1px solid #cbd5e1; border-radius: 8px; color: #475569; font-weight: 600; background: white; cursor: pointer;"
                            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        Cancelar
                    </button>
                    <button type="submit"
                            style="padding: 12px 24px; background: #fd761a; color: white; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                            onmouseover="this.style.background='#e06500'" onmouseout="this.style.background='#fd761a'">
                        <?= $editarEntrada ? 'Actualizar entrada' : 'Registrar entrada' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal() {
        const modal = document.getElementById('modalEntry');
        modal.classList.toggle('modal-hidden');
    }

    <?php if ($showModal): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalEntry').classList.remove('modal-hidden');
    });
    <?php endif; ?>

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('modalEntry');
            if (!modal.classList.contains('modal-hidden')) {
                toggleModal();
            }
        }
    });

    <?php if ($exito): ?>
    setTimeout(function() {
        var toast = document.querySelector('.toast-success');
        if (toast) toast.remove();
    }, 4000);
    <?php endif; ?>
    <?php if (!empty($errores)): ?>
    setTimeout(function() {
        var toast = document.querySelector('.toast-error');
        if (toast) toast.remove();
    }, 5000);
    <?php endif; ?>
</script>
