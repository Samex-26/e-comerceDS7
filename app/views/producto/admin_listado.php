<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    .admin-table th { background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .admin-table td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid #e2e8f0; }
    .admin-table tr:hover { background: #f8fafc; }
    .admin-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; background: #fff; }
    .kpi-mini-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #fff; padding: 1rem; transition: box-shadow 0.2s ease; }
    .kpi-mini-card:hover { box-shadow: 0 4px 20px rgba(30,41,59,0.05); }
    .kpi-mini-icon { width: 40px; height: 40px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .kpi-mini-value { font-size: 1.3rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
    .kpi-mini-label { font-size: 0.75rem; color: #64748b; }
    .status-badge { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 999px; }
    .status-activo { background: #d1fae5; color: #059669; }
    .status-inactivo { background: #f1f5f9; color: #64748b; }
    .drawer-overlay { transition: opacity 0.3s ease; backdrop-filter: blur(4px); }
    .drawer-content { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .drawer-hidden { pointer-events: none; }
    .drawer-hidden .drawer-overlay { opacity: 0; pointer-events: none; }
    .drawer-hidden .drawer-content { transform: translateX(100%); }
    .toast-success { animation: slideIn 0.3s ease, fadeOut 0.3s ease 3.7s forwards; }
    .toast-error { animation: slideIn 0.3s ease, fadeOut 0.3s ease 4.7s forwards; }
    @keyframes slideIn { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-20px); } }
    .form-label-custom { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 0.4rem; display: block; }
    .form-input-custom { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.7rem 1rem; width: 100%; transition: all 0.2s; box-sizing: border-box; font-family: inherit; font-size: inherit; }
    .form-input-custom:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79,70,229,0.15); outline: none; }
</style>

<?php
$showDrawer = !empty($productoEditar) || !empty($errores);
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

<div style="min-height: 100vh; display: flex; flex-direction: column; background: #f7f9fb;">
    <header style="background: white; border-bottom: 1px solid #e2e8f0; padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 30;">
        <div>
            <h2 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.25rem;">Gestion de Productos</h2>
            <p class="mb-0" style="color: #64748b; font-size: 0.85rem;">Administra el inventario y catalogo de tu tienda.</p>
        </div>
        <button onclick="toggleDrawer()" style="display: flex; align-items: center; gap: 8px; background: #fd761a; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
            <span class="material-symbols-outlined" style="font-size: 1.2rem;">add</span>
            Nuevo producto
        </button>
    </header>

    <main style="padding: 1.5rem 2rem;">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="kpi-mini-card d-flex align-items-center gap-3">
                    <div class="kpi-mini-icon" style="background: #eef2ff;">
                        <span class="material-symbols-outlined" style="color: #4f46e5;">inventory_2</span>
                    </div>
                    <div>
                        <div class="kpi-mini-value"><?= $totalProductos ?></div>
                        <div class="kpi-mini-label">Total Productos</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-mini-card d-flex align-items-center gap-3">
                    <div class="kpi-mini-icon" style="background: #fef3c7;">
                        <span class="material-symbols-outlined" style="color: #d97706;">inventory</span>
                    </div>
                    <div>
                        <div class="kpi-mini-value"><?= $stockBajo ?></div>
                        <div class="kpi-mini-label">Stock Bajo</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-mini-card d-flex align-items-center gap-3">
                    <div class="kpi-mini-icon" style="background: #d1fae5;">
                        <span class="material-symbols-outlined" style="color: #059669;">category</span>
                    </div>
                    <div>
                        <div class="kpi-mini-value"><?= count($categorias) ?></div>
                        <div class="kpi-mini-label">Categorias</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="kpi-mini-card d-flex align-items-center gap-3">
                    <div class="kpi-mini-icon" style="background: #ede9fe;">
                        <span class="material-symbols-outlined" style="color: #7c3aed;">payments</span>
                    </div>
                    <div>
                        <div class="kpi-mini-value"><?= count($productos) ?></div>
                        <div class="kpi-mini-label">Registrados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div style="overflow-x: auto;">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr class="admin-table">
                            <th style="padding-left: 1.5rem;">Producto</th>
                            <th>Categoria</th>
                            <th>Precio Base</th>
                            <th>Precio Oferta</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th class="text-end" style="padding-right: 1.5rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5" style="color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">inventory_2</span>
                                    No hay productos registrados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <?php if (!empty($p['imagen'])): ?>
                                                <img src="<?= BASE_URL . htmlspecialchars($p['imagen']) ?>" alt="" style="height: 44px; width: 44px; object-fit: cover; border-radius: 0.5rem; border: 1px solid #e2e8f0;">
                                            <?php else: ?>
                                                <div style="height: 44px; width: 44px; border-radius: 0.5rem; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                                                    <span class="material-symbols-outlined" style="color: #94a3b8; font-size: 1.2rem;">inventory_2</span>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="fw-semibold mb-0" style="color: #1e293b;"><?= htmlspecialchars($p['nombre']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: #64748b;"><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></td>
                                    <td class="fw-bold" style="color: #1e293b;">$<?= number_format((float) $p['precio'], 2, '.', '') ?></td>
                                    <td>
                                        <?php if (!empty($p['precio_oferta'])): ?>
                                            <span class="fw-bold" style="color: #059669;">$<?= number_format((float) $p['precio_oferta'], 2, '.', '') ?></span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-1" style="background: <?= (int) $p['cantidad'] > 5 ? '#d1fae5; color: #065f46' : ((int) $p['cantidad'] > 0 ? '#fef3c7; color: #92400e' : '#fee2e2; color: #991b1b') ?>;">
                                            <?= (int) $p['cantidad'] ?> unid.
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= ((int) ($p['activo'] ?? 1)) === 1 ? 'status-activo' : 'status-inactivo' ?>">
                                            <?= ((int) ($p['activo'] ?? 1)) === 1 ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-end" style="padding-right: 1.5rem;">
                                        <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                            <a href="<?= BASE_URL ?>producto/admin?editar=<?= (int) $p['id_producto'] ?>"
                                               style="padding: 6px; color: #64748b; border-radius: 6px; text-decoration: none; display: inline-flex;"
                                               onmouseover="this.style.color='#fd761a';this.style.background='#fff7ed'"
                                               onmouseout="this.style.color='#64748b';this.style.background='transparent'">
                                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">edit</span>
                                            </a>
                                            <a href="<?= BASE_URL ?>producto/eliminar/<?= (int) $p['id_producto'] ?>"
                                               style="padding: 6px; color: #64748b; border-radius: 6px; text-decoration: none; display: inline-flex;"
                                               onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'"
                                               onmouseout="this.style.color='#64748b';this.style.background='transparent'"
                                               onclick="return confirm('¿Eliminar este producto?')">
                                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding: 0.75rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <small style="color: #64748b;">Mostrando <?= count($productos) ?> producto(s)</small>
                <a href="<?= BASE_URL ?>categoria/admin" class="btn btn-sm d-flex align-items-center gap-1" style="border: 1px solid #e2e8f0; color: #1e293b; border-radius: 0.375rem;">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">category</span>
                    Gestionar categorias
                </a>
            </div>
        </div>
    </main>
</div>

<div style="position: fixed; inset: 0; z-index: 50;" id="drawer" class="<?= $showDrawer ? '' : 'drawer-hidden' ?>">
    <div class="drawer-overlay" style="position: absolute; inset: 0; background: rgba(30,41,59,0.4); cursor: pointer;" onclick="toggleDrawer()"></div>
    <div class="drawer-content" style="position: absolute; right: 0; top: 0; height: 100vh; width: 100%; max-width: 560px; background: white; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.15rem;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem; vertical-align: middle; margin-right: 0.35rem;"><?= $productoEditar ? 'edit' : 'add' ?></span>
                <?= $productoEditar ? 'Editar Producto' : 'Nuevo Producto' ?>
            </h3>
            <button onclick="toggleDrawer()" style="padding: 6px; border: none; background: transparent; cursor: pointer; border-radius: 9999px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='transparent'">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>producto/admin" enctype="multipart/form-data" style="flex: 1; overflow-y: auto; padding: 1.5rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= $productoEditar ? (int) $productoEditar['id_producto'] : '' ?>">

            <p style="font-weight: 600; color: #1e293b; font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">info</span>
                Informacion General
            </p>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Nombre del Producto <span style="color: #ef4444;">*</span></label>
                <input type="text" class="form-input-custom" name="nombre" required
                       value="<?= htmlspecialchars($productoEditar['nombre'] ?? $old['nombre'] ?? '') ?>">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Descripcion</label>
                <textarea class="form-input-custom" name="descripcion" rows="3"><?=
                    htmlspecialchars($productoEditar['descripcion'] ?? $old['descripcion'] ?? '')
                ?></textarea>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Categoria <span style="color: #ef4444;">*</span></label>
                <select class="form-input-custom" name="id_categoria" required>
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['id_categoria'] ?>"
                            <?= ((int) ($productoEditar['id_categoria'] ?? $old['id_categoria'] ?? 0) === (int) $cat['id_categoria']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                <label style="font-size: 0.85rem; color: #1e293b; font-weight: 500;">Producto activo</label>
                <label style="position: relative; display: inline-block; width: 44px; height: 24px;">
                    <input type="checkbox" name="activo" value="1" style="opacity: 0; width: 0; height: 0;"
                        <?= (!isset($productoEditar['activo']) || (int) $productoEditar['activo'] === 1) ? 'checked' : '' ?>>
                    <span style="position: absolute; inset: 0; background: #cbd5e1; border-radius: 24px; transition: 0.3s; cursor: pointer;" onmouseover="this.style.boxShadow='0 0 0 3px rgba(79,70,229,0.15)'" onmouseout="this.style.boxShadow='none'"></span>
                    <span style="position: absolute; left: 2px; bottom: 2px; width: 20px; height: 20px; background: white; border-radius: 50%; transition: 0.3s;"></span>
                </label>
            </div>

            <p style="font-weight: 600; color: #1e293b; font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; margin-top: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">payments</span>
                Precios e Inventario
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label class="form-label-custom">Precio Base <span style="color: #ef4444;">*</span></label>
                    <input type="number" step="0.01" class="form-input-custom" name="precio" required
                           value="<?= htmlspecialchars($productoEditar['precio'] ?? $old['precio'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label-custom">Precio Oferta</label>
                    <input type="number" step="0.01" class="form-input-custom" name="precio_oferta"
                           value="<?= htmlspecialchars($productoEditar['precio_oferta'] ?? $old['precio_oferta'] ?? '') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                <div>
                    <label class="form-label-custom">Costo</label>
                    <input type="number" step="0.01" class="form-input-custom" name="costo"
                           value="<?= htmlspecialchars($productoEditar['costo'] ?? $old['costo'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label-custom">Cantidad en Stock <span style="color: #ef4444;">*</span></label>
                    <input type="number" class="form-input-custom" name="cantidad" required
                           value="<?= htmlspecialchars($productoEditar['cantidad'] ?? $old['cantidad'] ?? '') ?>">
                </div>
            </div>

            <p style="font-weight: 600; color: #1e293b; font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; margin-top: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">image</span>
                Multimedia
            </p>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Imagen del Producto</label>
                <input type="file" class="form-input-custom" name="imagen" accept="image/jpeg,image/png,image/webp" style="padding: 0.5rem;">
                <small style="color: #94a3b8; font-size: 0.75rem;">PNG, JPG, WebP hasta 2 MB.</small>
                <?php if ($productoEditar && !empty($productoEditar['imagen'])): ?>
                    <div style="margin-top: 0.5rem;">
                        <img src="<?= BASE_URL . htmlspecialchars($productoEditar['imagen']) ?>" alt="" style="height: 64px; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
                    </div>
                <?php endif; ?>
            </div>

            <div style="padding-top: 1rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="toggleDrawer()"
                        style="padding: 0.7rem 1.5rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-weight: 600; color: #475569; background: white; cursor: pointer;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding: 0.7rem 2rem; background: #1e293b; color: white; border-radius: 0.5rem; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;"
                        onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='#1e293b'">
                    <span class="material-symbols-outlined" style="font-size: 1.1rem;">save</span>
                    <?= $productoEditar ? 'Guardar Cambios' : 'Guardar' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDrawer() {
        const drawer = document.getElementById('drawer');
        drawer.classList.toggle('drawer-hidden');
        document.body.style.overflow = drawer.classList.contains('drawer-hidden') ? '' : 'hidden';
    }

    document.querySelectorAll('input[name="activo"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var track = this.nextElementSibling;
            var thumb = track.nextElementSibling;
            if (this.checked) {
                track.style.background = '#4f46e5';
                thumb.style.transform = 'translateX(20px)';
            } else {
                track.style.background = '#cbd5e1';
                thumb.style.transform = 'translateX(0)';
            }
        });
        if (checkbox.checked) {
            var track = checkbox.nextElementSibling;
            var thumb = track.nextElementSibling;
            track.style.background = '#4f46e5';
            thumb.style.transform = 'translateX(20px)';
        }
    });

    <?php if ($showDrawer): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('drawer').classList.remove('drawer-hidden');
        document.body.style.overflow = 'hidden';
    });
    <?php endif; ?>

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var drawer = document.getElementById('drawer');
            if (!drawer.classList.contains('drawer-hidden')) {
                toggleDrawer();
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
