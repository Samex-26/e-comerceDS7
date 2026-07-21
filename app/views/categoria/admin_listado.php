<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    .admin-table th { background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .admin-table td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid #e2e8f0; }
    .admin-table tr:hover { background: #f8fafc; }
    .admin-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; background: #fff; }
    .breadcrumb-link { color: #64748b; text-decoration: none; font-size: 0.85rem; }
    .breadcrumb-link:hover { color: #1e293b; }
    .breadcrumb-sep { color: #cbd5e1; margin: 0 0.5rem; }
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
    .cat-icon-option { display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 0.5rem; border: 2px solid #e2e8f0; cursor: pointer; transition: all 0.2s; }
    .cat-icon-option:hover { border-color: #cbd5e1; background: #f8fafc; }
    .cat-icon-option.selected { border-color: #4f46e5; background: #eef2ff; }
</style>

<?php
$showDrawer = !empty($categoriaEditar) || !empty($errores);
$iconos = ['category', 'shopping_basket', 'home', 'favorite', 'smartphone', 'styler', 'chair', 'fitness_center', 'book', 'computer', 'restaurant', 'pets'];
$iconoActual = $categoriaEditar['icono'] ?? $old['icono'] ?? 'category';
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
    <header style="background: white; border-bottom: 1px solid #e2e8f0; padding: 1.25rem 2rem; position: sticky; top: 0; z-index: 30;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <a href="<?= BASE_URL ?>dashboard/index" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-sep">chevron_right</span>
            <span style="color: #1e293b; font-size: 0.85rem; font-weight: 500;">Categorias</span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.25rem;">Gestion de Categorias</h2>
                <p class="mb-0" style="color: #64748b; font-size: 0.85rem;">Organiza los productos por categorias.</p>
            </div>
            <button onclick="toggleDrawer()" style="display: flex; align-items: center; gap: 8px; background: #fd761a; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem;">add</span>
                Nueva categoria
            </button>
        </div>
    </header>

    <main style="padding: 1.5rem 2rem;">
        <div class="admin-card">
            <div style="overflow-x: auto;">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr class="admin-table">
                            <th style="padding-left: 1.5rem;">Nombre</th>
                            <th>Descripcion</th>
                            <th style="width: 100px;">Productos</th>
                            <th class="text-end" style="padding-right: 1.5rem; width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categorias)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5" style="color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">category</span>
                                    No hay categorias registradas.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 40px; height: 40px; border-radius: 0.5rem; background: #eef2ff; display: flex; align-items: center; justify-content: center; color: #4f46e5;">
                                                <span class="material-symbols-outlined" style="font-size: 1.2rem;"><?= htmlspecialchars($cat['icono'] ?? 'category') ?></span>
                                            </div>
                                            <div>
                                                <p class="fw-semibold mb-0" style="color: #1e293b;"><?= htmlspecialchars($cat['nombre']) ?></p>
                                                <?php if (!empty($cat['descripcion'])): ?>
                                                    <small style="color: #94a3b8;"><?= htmlspecialchars(mb_substr($cat['descripcion'], 0, 60)) ?><?= mb_strlen($cat['descripcion']) > 60 ? '...' : '' ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: #64748b;"><?= htmlspecialchars(mb_substr($cat['descripcion'] ?? '', 0, 80)) ?><?= mb_strlen($cat['descripcion'] ?? '') > 80 ? '...' : '' ?></td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-1" style="background: #eef2ff; color: #4f46e5; font-weight: 600;">
                                            <?= (int) ($cat['total_productos'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="text-end" style="padding-right: 1.5rem;">
                                        <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                            <a href="<?= BASE_URL ?>categoria/admin?editar=<?= (int) $cat['id_categoria'] ?>"
                                               style="padding: 6px; color: #64748b; border-radius: 6px; text-decoration: none; display: inline-flex;"
                                               onmouseover="this.style.color='#fd761a';this.style.background='#fff7ed'"
                                               onmouseout="this.style.color='#64748b';this.style.background='transparent'">
                                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">edit</span>
                                            </a>
                                            <a href="<?= BASE_URL ?>categoria/eliminar/<?= (int) $cat['id_categoria'] ?>"
                                               style="padding: 6px; color: #64748b; border-radius: 6px; text-decoration: none; display: inline-flex;"
                                               onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'"
                                               onmouseout="this.style.color='#64748b';this.style.background='transparent'"
                                               onclick="return confirm('¿Eliminar esta categoria?')">
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
                <small style="color: #64748b;">Mostrando <?= count($categorias) ?> categoria(s)</small>
                <a href="<?= BASE_URL ?>producto/admin" class="btn btn-sm d-flex align-items-center gap-1" style="border: 1px solid #e2e8f0; color: #1e293b; border-radius: 0.375rem;">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">arrow_back</span>
                    Volver a productos
                </a>
            </div>
        </div>
    </main>
</div>

<div style="position: fixed; inset: 0; z-index: 50;" id="drawer" class="<?= $showDrawer ? '' : 'drawer-hidden' ?>">
    <div class="drawer-overlay" style="position: absolute; inset: 0; background: rgba(30,41,59,0.4); cursor: pointer;" onclick="toggleDrawer()"></div>
    <div class="drawer-content" style="position: absolute; right: 0; top: 0; height: 100vh; width: 100%; max-width: 512px; background: white; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.15rem;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem; vertical-align: middle; margin-right: 0.35rem;"><?= $categoriaEditar ? 'edit' : 'add' ?></span>
                <?= $categoriaEditar ? 'Editar Categoria' : 'Nueva Categoria' ?>
            </h3>
            <button onclick="toggleDrawer()" style="padding: 6px; border: none; background: transparent; cursor: pointer; border-radius: 9999px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='transparent'">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>categoria/admin" style="flex: 1; overflow-y: auto; padding: 1.5rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= $categoriaEditar ? (int) $categoriaEditar['id_categoria'] : '' ?>">

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Icono Visual</label>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="icon-selector">
                    <?php foreach ($iconos as $ico): ?>
                        <div class="cat-icon-option <?= $ico === $iconoActual ? 'selected' : '' ?>" data-icon="<?= $ico ?>" onclick="selectIcon(this)">
                            <span class="material-symbols-outlined" style="font-size: 1.3rem;"><?= $ico ?></span>
                        </div>
                    <?php endforeach; ?>
                    <input type="hidden" name="icono" id="icono_selected" value="<?= htmlspecialchars($iconoActual) ?>">
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Nombre de la Categoria <span style="color: #ef4444;">*</span></label>
                <input type="text" class="form-input-custom" name="nombre" required
                       value="<?= htmlspecialchars($categoriaEditar['nombre'] ?? $old['nombre'] ?? '') ?>">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Descripcion</label>
                <textarea class="form-input-custom" name="descripcion" rows="3"><?=
                    htmlspecialchars($categoriaEditar['descripcion'] ?? $old['descripcion'] ?? '')
                ?></textarea>
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
                    <?= $categoriaEditar ? 'Guardar Cambios' : 'Guardar Categoria' ?>
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

    function selectIcon(el) {
        document.querySelectorAll('.cat-icon-option').forEach(function(o) { o.classList.remove('selected'); });
        el.classList.add('selected');
        document.getElementById('icono_selected').value = el.dataset.icon;
    }

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
