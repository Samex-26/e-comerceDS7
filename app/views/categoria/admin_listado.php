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
                <h2 class="fw-bold mb-0 fs-4"><?= htmlspecialchars($lang['admin_categorias']) ?></h2>
                <p class="mb-0 small opacity-75">Organiza los productos por categorías</p>
            </div>
            <a href="<?= BASE_URL ?>categoria/crear" class="btn d-flex align-items-center gap-2 fw-bold" style="background: #fd761a; color: white; padding: 0.6rem 1.5rem; border-radius: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem;">add</span>
                Nueva categoría
            </a>
        </div>

        <?php if (!empty($exito)): ?>
            <div class="mx-3 mt-3 px-4 py-3 rounded-3 d-flex align-items-center gap-2" style="background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46;">
                <span class="material-symbols-outlined">check_circle</span>
                <?= htmlspecialchars($exito) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errores)): ?>
            <div class="mx-3 mt-3 px-4 py-3 rounded-3" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
                <?php foreach ($errores as $e): ?><p class="mb-0 d-flex align-items-center gap-2"><span class="material-symbols-outlined">error</span><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead><tr class="admin-table">
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr></thead>
                    <tbody>
                        <?php if (empty($categorias)): ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">No hay categorías registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td class="fw-semibold" style="color: #1e293b;"><?= (int) $cat['id_categoria'] ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($cat['nombre']) ?></td>
                                    <td style="color: #64748b;"><?= htmlspecialchars($cat['descripcion']) ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>categoria/editar/<?= (int) $cat['id_categoria'] ?>" class="btn-edit d-inline-block" title="Editar">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <a href="<?= BASE_URL ?>categoria/eliminar/<?= (int) $cat['id_categoria'] ?>" class="btn-delete d-inline-block" title="Eliminar"
                                           onclick="return confirm('<?= htmlspecialchars($lang['confirmar_eliminar']) ?>')">
                                            <span class="material-symbols-outlined">delete</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-3 d-flex justify-content-between align-items-center" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <small class="text-muted">Mostrando <?= count($categorias) ?> categoría(s)</small>
                <a href="<?= BASE_URL ?>producto/admin" class="btn btn-sm d-flex align-items-center gap-1" style="border: 1px solid #e2e8f0; color: #1e293b;">
                    <span class="material-symbols-outlined" style="font-size: 1rem;">arrow_back</span>
                    Volver a productos
                </a>
            </div>
        </div>
    </div>
</div>
