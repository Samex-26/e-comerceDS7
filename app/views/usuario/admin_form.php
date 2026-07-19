<style>
    .form-label-custom { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 0.4rem; display: block; }
    .form-input-custom { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.7rem 1rem; width: 100%; transition: all 0.2s; box-sizing: border-box; font-family: inherit; font-size: inherit; }
    .form-input-custom:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79,70,229,0.15); outline: none; }
    .warning-role { background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.5rem; padding: 0.75rem 1rem; display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.85rem; color: #92400e; }
</style>

<?php $esEdicion = isset($usuario); ?>

<div style="min-height: 100vh; display: flex; flex-direction: column; background: #f7f9fb;">
    <header style="background: white; border-bottom: 1px solid #e2e8f0; padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.25rem;">
                <?= $esEdicion ? 'Editar Usuario' : 'Nuevo Usuario' ?>
            </h2>
            <p class="mb-0" style="color: #64748b; font-size: 0.85rem;">
                <?= $esEdicion ? 'Modifica los datos del usuario seleccionado.' : 'Registra un nuevo usuario en la plataforma.' ?>
            </p>
        </div>
        <a href="<?= BASE_URL ?>usuario/admin"
           style="display: flex; align-items: center; gap: 8px; padding: 10px 24px; border-radius: 8px; font-weight: 600; border: 1px solid #e2e8f0; color: #1e293b; text-decoration: none; background: white;"
           onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
            <span class="material-symbols-outlined" style="font-size: 1.2rem;">arrow_back</span>
            Volver
        </a>
    </header>

    <main style="padding: 1.5rem 2rem; max-width: 640px;">
        <?php if (!empty($errores)): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                <?php foreach ($errores as $e): ?>
                    <p style="margin: 0; font-size: 14px;"><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.5rem;">
            <form method="POST" action="<?= BASE_URL ?>usuario/<?= $esEdicion ? 'editar/' . (int) $usuario['id_usuario'] : 'crear' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label-custom">Nombre completo <span style="color: #ef4444;">*</span></label>
                    <input type="text" class="form-input-custom" name="nombre" required
                           value="<?= htmlspecialchars($usuario['nombre'] ?? $old['nombre'] ?? '') ?>">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label-custom">Correo electr&oacute;nico <span style="color: #ef4444;">*</span></label>
                    <input type="email" class="form-input-custom" name="email" required
                           value="<?= htmlspecialchars($usuario['email'] ?? $old['email'] ?? '') ?>">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label-custom">
                        Contrase&ntilde;a
                        <?php if (!$esEdicion): ?><span style="color: #ef4444;">*</span><?php endif; ?>
                    </label>
                    <input type="password" class="form-input-custom" name="password"
                           <?= $esEdicion ? '' : 'required' ?>
                           placeholder="<?= $esEdicion ? 'Dejar en blanco para mantener actual' : '' ?>">
                    <small style="color: #94a3b8; font-size: 0.75rem;">M&iacute;nimo 8 caracteres.</small>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label-custom">Idioma <span style="color: #ef4444;">*</span></label>
                    <select class="form-input-custom" name="id_idioma" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($idiomas as $idi): ?>
                            <option value="<?= (int) $idi['id_idioma'] ?>"
                                <?= ((int) ($usuario['id_idioma'] ?? $old['id_idioma'] ?? 0) === (int) $idi['id_idioma']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($idi['nombre']) ?> (<?= strtoupper(htmlspecialchars($idi['codigo'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label class="form-label-custom">Rol <span style="color: #ef4444;">*</span></label>
                    <select class="form-input-custom" name="rol" id="rolSelect" required onchange="document.getElementById('rolWarning').style.display = this.value === 'admin' ? '' : 'none'">
                        <option value="cliente" <?= (($usuario['rol'] ?? $old['rol'] ?? '') === 'cliente') ? 'selected' : '' ?>>Cliente</option>
                        <option value="admin" <?= (($usuario['rol'] ?? $old['rol'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>

                <div id="rolWarning" class="warning-role" style="<?= (($usuario['rol'] ?? $old['rol'] ?? '') === 'admin') ? '' : 'display: none;' ?>">
                    <span class="material-symbols-outlined" style="font-size: 1.2rem; color: #d97706; flex-shrink: 0;">warning</span>
                    <span>Este usuario tendr&aacute; acceso completo al panel de administraci&oacute;n.</span>
                </div>

                <div style="padding-top: 1rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                    <a href="<?= BASE_URL ?>usuario/admin"
                       style="padding: 0.7rem 1.5rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-weight: 600; color: #475569; background: white; text-decoration: none;"
                       onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        Cancelar
                    </a>
                    <button type="submit"
                            style="padding: 0.7rem 2rem; background: #1e293b; color: white; border-radius: 0.5rem; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;"
                            onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='#1e293b'">
                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">save</span>
                        <?= $esEdicion ? 'Guardar Cambios' : 'Crear Usuario' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
