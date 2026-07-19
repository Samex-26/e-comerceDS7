<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    .admin-table th { background: #1e293b; color: white; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem; }
    .admin-table td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid #e2e8f0; }
    .admin-table tr:hover { background: #f8fafc; }
    .btn-edit { color: #64748b; padding: 0.4rem; border-radius: 0.5rem; transition: all 0.2s; }
    .btn-edit:hover { color: #fd761a; background: #fff7ed; }
    .btn-delete { color: #64748b; padding: 0.4rem; border-radius: 0.5rem; transition: all 0.2s; }
    .btn-delete:hover { color: #dc2626; background: #fef2f2; }
    .admin-header { background: #1e293b; color: white; padding: 1.25rem 1.5rem; border-radius: 0.75rem 0.75rem 0 0; }
    .admin-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; }
</style>

<div class="container-fluid py-4 px-4" style="background: #f7f9fb; min-height: calc(100vh - 56px);">
    <div class="admin-card bg-white">
        <div class="admin-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0 fs-4"><?= htmlspecialchars($lang['admin_productos']) ?></h2>
                <p class="mb-0 small opacity-75">Administra tu catálogo de productos</p>
            </div>
            <a href="<?= BASE_URL ?>producto/crear" class="btn d-flex align-items-center gap-2 fw-bold" style="background: #fd761a; color: white; padding: 0.6rem 1.5rem; border-radius: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem;">add</span>
                Nuevo producto
            </a>
        </div>

        <?php if (!empty($exito)): ?>
            <div class="mx-3 mt-3 px-4 py-3 rounded-3 d-flex align-items-center gap-2" style="background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem;">check_circle</span>
                <?= htmlspecialchars($exito) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errores)): ?>
            <div class="mx-3 mt-3 px-4 py-3 rounded-3" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
                <?php foreach ($errores as $e): ?><p class="mb-0 d-flex align-items-center gap-2"><span class="material-symbols-outlined" style="font-size: 1.2rem;">error</span><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead><tr class="admin-table">
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th class="text-end">Acciones</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No hay productos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach ($productos as $p): ?>
                                <tr>
                                    <td class="fw-semibold" style="color: #1e293b;"><?= (int) $p['id_producto'] ?></td>
                                    <td>
                                        <?php if (!empty($p['imagen'])): ?>
                                            <img src="<?= BASE_URL . htmlspecialchars($p['imagen']) ?>" alt="" style="height: 40px; width: 40px; object-fit: cover; border-radius: 0.375rem; border: 1px solid #e2e8f0;">
                                        <?php else: ?>
                                            <div style="height: 40px; width: 40px; border-radius: 0.375rem; background: #f1f5f9; display: flex; align-items: center; justify-content: center;"><span class="material-symbols-outlined" style="color: #94a3b8; font-size: 1.2rem;">inventory_2</span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td style="color: #64748b;"><?= htmlspecialchars($p['categoria_nombre'] ?? '') ?></td>
                                    <td class="fw-bold" style="color: #1e293b;">$<?= number_format($p['precio'], 2, '.', '') ?></td>
                                    <td><span class="badge rounded-pill px-3 py-1" style="background: <?= (int) $p['cantidad'] > 0 ? '#d1fae5; color: #065f46' : '#fee2e2; color: #991b1b' ?>;"><?= (int) $p['cantidad'] ?></span></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>producto/editar/<?= (int) $p['id_producto'] ?>" class="btn-edit d-inline-block" title="Editar">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>producto/eliminar/<?= (int) $p['id_producto'] ?>" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars($lang['confirmar_eliminar']) ?>')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <button type="submit" class="btn btn-link btn-delete p-1" title="Eliminar"><span class="material-symbols-outlined">delete</span></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3 bg-light border-top border-#e2e8f0 d-flex justify-content-between align-items-center" style="background: #f8fafc;">
                <small class="text-muted">Mostrando <?= count($productos) ?> producto(s)</small>
                <a href="<?= BASE_URL ?>categoria/admin" class="btn btn-sm d-flex align-items-center gap-1" style="border: 1px solid #e2e8f0; color: #1e293b;">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">category</span>
                    Gestionar categorías
                </a>
            </div>
        </div>
    </div>
</div>
