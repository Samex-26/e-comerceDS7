<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    .form-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; }
    .form-card-header { background: #1e293b; color: white; padding: 1.25rem 1.5rem; }
    .form-label-custom { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 0.4rem; }
    .form-input-custom { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.7rem 1rem; width: 100%; transition: all 0.2s; }
    .form-input-custom:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15); outline: none; }
    .section-title { font-weight: 600; color: #1e293b; font-size: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; }
</style>

<div class="container-fluid py-4 px-4" style="background: #f7f9fb; min-height: calc(100vh - 56px);">
    <div class="form-card bg-white">
        <div class="form-card-header d-flex align-items-center gap-3">
            <span class="material-symbols-outlined"><?= isset($producto) ? 'edit' : 'add' ?></span>
            <h2 class="fw-bold mb-0 fs-4"><?= htmlspecialchars(isset($producto) ? $lang['editar_producto'] : $lang['nuevo_producto']) ?></h2>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="mx-3 mt-3 px-4 py-3 rounded-3" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b;" id="error-banner">
                <?php foreach ($errores as $e): ?><p class="mb-0 d-flex align-items-center gap-2"><span class="material-symbols-outlined">error</span><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php if (!empty($errores)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var banner = document.getElementById('error-banner');
            if (banner) {
                banner.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
    <?php endif; ?>

        <div class="p-4">
            <form method="POST" action="<?= BASE_URL ?>producto/<?= isset($producto) ? 'editar/' . (int) $producto['id_producto'] : 'crear' ?>"
                  enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <p class="section-title d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.2rem;">info</span>
                    Información General
                </p>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-input-custom" id="nombre" name="nombre"
                               value="<?= htmlspecialchars($producto['nombre'] ?? $old['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-custom">Categoría <span class="text-danger">*</span></label>
                        <select class="form-input-custom" id="id_categoria" name="id_categoria" required>
                            <option value="">— Seleccionar —</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int) $cat['id_categoria'] ?>"
                                    <?= ((int) ($producto['id_categoria'] ?? $old['id_categoria'] ?? 0) === (int) $cat['id_categoria']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label-custom">Descripción</label>
                        <textarea class="form-input-custom" id="descripcion" name="descripcion" rows="4"><?=
                            htmlspecialchars($producto['descripcion'] ?? $old['descripcion'] ?? '')
                        ?></textarea>
                    </div>
                </div>

                <p class="section-title d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.2rem;">payments</span>
                    Precios e Inventario
                </p>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label-custom">Precio <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-input-custom" id="precio" name="precio"
                               value="<?= htmlspecialchars($producto['precio'] ?? $old['precio'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Precio de Oferta</label>
                        <input type="number" step="0.01" class="form-input-custom" id="precio_oferta" name="precio_oferta"
                               value="<?= htmlspecialchars($producto['precio_oferta'] ?? $old['precio_oferta'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-custom">Stock <span class="text-danger">*</span></label>
                        <input type="number" class="form-input-custom" id="cantidad" name="cantidad"
                               value="<?= htmlspecialchars($producto['cantidad'] ?? $old['cantidad'] ?? '') ?>" required>
                    </div>
                </div>

                <p class="section-title d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 1.2rem;">image</span>
                    Multimedia
                </p>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Imagen del Producto <?= !isset($producto) ? '<span class="text-danger">*</span>' : '' ?></label>
                        <input type="file" class="form-input-custom" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp" style="padding: 0.5rem;" <?= !isset($producto) ? 'required' : '' ?>>
                        <div class="mt-1" style="font-size: 0.75rem; color: #94a3b8;">Sube una imagen desde tu computadora. Formatos: JPG, PNG, WebP (máx. 2 MB).<?= !isset($producto) ? ' <strong>Campo obligatorio</strong>.' : '' ?></div>
                        <?php if (isset($producto) && !empty($producto['imagen'])): ?>
                            <div class="mt-2"><img src="<?= BASE_URL . htmlspecialchars($producto['imagen']) ?>" alt="" style="height: 80px; border-radius: 0.375rem; border: 1px solid #e2e8f0;"></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-custom">Costo</label>
                        <input type="number" step="0.01" class="form-input-custom" id="costo" name="costo"
                               value="<?= htmlspecialchars($producto['costo'] ?? $old['costo'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 pt-3 border-top" style="border-color: #e2e8f0;">
                    <a href="<?= BASE_URL ?>producto/admin" class="btn px-5 py-2 fw-semibold" style="border: 1px solid #e2e8f0; color: #1e293b; border-radius: 0.5rem;">Cancelar</a>
                    <button type="submit" class="btn px-5 py-2 fw-bold d-flex align-items-center gap-2" style="background: #1e293b; color: white; border-radius: 0.5rem;">
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;">save</span>
                        <?= htmlspecialchars($lang['guardar']) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
