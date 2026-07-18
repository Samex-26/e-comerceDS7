<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    .form-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; }
    .form-card-header { background: #1e293b; color: white; padding: 1.25rem 1.5rem; }
    .form-label-custom { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 0.4rem; }
    .form-input-custom { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.7rem 1rem; width: 100%; transition: all 0.2s; }
    .form-input-custom:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15); outline: none; }
</style>

<div class="container-fluid py-4 px-4" style="background: #f7f9fb; min-height: calc(100vh - 56px);">
    <div class="form-card bg-white">
        <div class="form-card-header d-flex align-items-center gap-3">
            <span class="material-symbols-outlined"><?= isset($categoria) ? 'edit' : 'add' ?></span>
            <h2 class="fw-bold mb-0 fs-4"><?= htmlspecialchars(isset($categoria) ? $lang['editar_categoria'] : $lang['nueva_categoria']) ?></h2>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="mx-3 mt-3 px-4 py-3 rounded-3" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
                <?php foreach ($errores as $e): ?><p class="mb-0 d-flex align-items-center gap-2"><span class="material-symbols-outlined">error</span><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="p-4">
            <form method="POST" action="<?= BASE_URL ?>categoria/<?= isset($categoria) ? 'editar/' . (int) $categoria['id_categoria'] : 'crear' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="mb-4">
                    <label class="form-label-custom">Nombre de la Categoría <span class="text-danger">*</span></label>
                    <input type="text" class="form-input-custom" id="nombre" name="nombre"
                           value="<?= htmlspecialchars($categoria['nombre'] ?? $old['nombre'] ?? '') ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">Descripción</label>
                    <textarea class="form-input-custom" id="descripcion" name="descripcion" rows="3"><?=
                        htmlspecialchars($categoria['descripcion'] ?? $old['descripcion'] ?? '')
                    ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-3 pt-3 border-top" style="border-color: #e2e8f0;">
                    <a href="<?= BASE_URL ?>categoria/admin" class="btn px-5 py-2 fw-semibold" style="border: 1px solid #e2e8f0; color: #1e293b; border-radius: 0.5rem;">Cancelar</a>
                    <button type="submit" class="btn px-5 py-2 fw-bold d-flex align-items-center gap-2" style="background: #1e293b; color: white; border-radius: 0.5rem;">
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;">save</span>
                        <?= htmlspecialchars($lang['guardar']) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
