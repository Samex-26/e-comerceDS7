<style>
    :root { --primary: #1e293b; --secondary: #fd761a; --bg-surface: #f7f9fb; }
    .admin-table th { background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; }
    .admin-table td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid #e2e8f0; }
    .admin-table tr:hover { background: #f8fafc; }
    .admin-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; overflow: hidden; background: #fff; }
    .role-badge { font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 999px; letter-spacing: 0.03em; display: inline-block; }
    .role-admin { background: #1e293b; color: #fff; }
    .role-cliente { background: #f1f5f9; color: #64748b; }
    .avatar-initials { width: 36px; height: 36px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0; }
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
    .warning-role { background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.5rem; padding: 0.75rem 1rem; display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.85rem; color: #92400e; }
</style>

<?php
function iniciales(string $nombre): string {
    $parts = explode(' ', trim($nombre));
    $initials = '';
    for ($i = 0; $i < min(2, count($parts)); $i++) {
        $initials .= mb_strtoupper(mb_substr($parts[$i], 0, 1));
    }
    return $initials ?: '?';
}

$coloresAvatar = ['#4f46e5', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0891b2'];
$showDrawer = !empty($usuarioEditar) || !empty($errores);
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
            <h2 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.25rem;">Gesti&oacute;n de Usuarios</h2>
            <p class="mb-0" style="color: #64748b; font-size: 0.85rem;">Administra los accesos y perfiles de la plataforma.</p>
        </div>
        <button onclick="toggleDrawer()" style="display: flex; align-items: center; gap: 8px; background: #fd761a; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
            <span class="material-symbols-outlined" style="font-size: 1.2rem;">person_add</span>
            Nuevo usuario
        </button>
    </header>

    <main style="padding: 1.5rem 2rem;">
        <div class="admin-card">
            <div style="overflow-x: auto;">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr class="admin-table">
                            <th style="padding-left: 1.5rem;">Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Idioma</th>
                            <th>Fecha Registro</th>
                            <th class="text-end" style="padding-right: 1.5rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5" style="color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;">group</span>
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $idx = 0; ?>
                            <?php foreach ($usuarios as $u): ?>
                                <?php $color = $coloresAvatar[$idx % count($coloresAvatar)]; $idx++; ?>
                                <tr>
                                    <td style="padding-left: 1.5rem;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="avatar-initials" style="background: <?= $color ?>;">
                                                <?= iniciales($u['nombre']) ?>
                                            </div>
                                            <div>
                                                <p class="fw-semibold mb-0" style="color: #1e293b;"><?= htmlspecialchars($u['nombre']) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="color: #64748b;"><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="role-badge <?= ($u['rol'] ?? '') === 'admin' ? 'role-admin' : 'role-cliente' ?>">
                                            <?= strtoupper(htmlspecialchars($u['rol'] ?? 'cliente')) ?>
                                        </span>
                                    </td>
                                    <td style="color: #64748b;"><?= htmlspecialchars($u['idioma_nombre'] ?? '') ?></td>
                                    <td style="color: #64748b;">
                                        <?= date('d M Y', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td class="text-end" style="padding-right: 1.5rem;">
                                        <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                            <a href="<?= BASE_URL ?>usuario/admin?editar=<?= (int) $u['id_usuario'] ?>"
                                               style="padding: 6px; color: #64748b; border-radius: 6px; text-decoration: none; display: inline-flex;"
                                               onmouseover="this.style.color='#fd761a';this.style.background='#fff7ed'"
                                               onmouseout="this.style.color='#64748b';this.style.background='transparent'">
                                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">edit</span>
                                            </a>
                                            <?php if ((int) $u['id_usuario'] !== (int) ($_SESSION['id_usuario'] ?? 0)): ?>
                                            <a href="<?= BASE_URL ?>usuario/eliminar/<?= (int) $u['id_usuario'] ?>"
                                               style="padding: 6px; color: #64748b; border-radius: 6px; text-decoration: none; display: inline-flex;"
                                               onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'"
                                               onmouseout="this.style.color='#64748b';this.style.background='transparent'"
                                               onclick="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">delete</span>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding: 0.75rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <small style="color: #64748b;">Mostrando <?= count($usuarios) ?> usuario(s)</small>
            </div>
        </div>
    </main>
</div>

<div style="position: fixed; inset: 0; z-index: 50;" id="drawer" class="<?= $showDrawer ? '' : 'drawer-hidden' ?>">
    <div class="drawer-overlay" style="position: absolute; inset: 0; background: rgba(30,41,59,0.4); cursor: pointer;" onclick="toggleDrawer()"></div>
    <div class="drawer-content" style="position: absolute; right: 0; top: 0; height: 100vh; width: 100%; max-width: 560px; background: white; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 class="fw-bold mb-0" style="color: #1e293b; font-size: 1.15rem;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem; vertical-align: middle; margin-right: 0.35rem;"><?= $usuarioEditar ? 'edit' : 'person_add' ?></span>
                <?= $usuarioEditar ? 'Editar Usuario' : 'Nuevo Usuario' ?>
            </h3>
            <button onclick="toggleDrawer()" style="padding: 6px; border: none; background: transparent; cursor: pointer; border-radius: 9999px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='transparent'">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>usuario/admin" style="flex: 1; overflow-y: auto; padding: 1.5rem;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= $usuarioEditar ? (int) $usuarioEditar['id_usuario'] : '' ?>">

            <p style="font-weight: 600; color: #1e293b; font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">info</span>
                Informaci&oacute;n General
            </p>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Nombre completo <span style="color: #ef4444;">*</span></label>
                <input type="text" class="form-input-custom" name="nombre" required
                       value="<?= htmlspecialchars($usuarioEditar['nombre'] ?? $old['nombre'] ?? '') ?>">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Correo electr&oacute;nico <span style="color: #ef4444;">*</span></label>
                <input type="email" class="form-input-custom" name="email" required
                       value="<?= htmlspecialchars($usuarioEditar['email'] ?? $old['email'] ?? '') ?>">
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">
                    Contrase&ntilde;a
                    <?php if (!$usuarioEditar): ?><span style="color: #ef4444;">*</span><?php endif; ?>
                </label>
                <div style="position: relative;">
                    <input type="password" class="form-input-custom" name="password" id="passwordField"
                           style="padding-right: 2.5rem;"
                           <?= $usuarioEditar ? '' : 'required' ?>
                           placeholder="<?= $usuarioEditar ? 'Dejar en blanco para mantener actual' : '' ?>">
                    <button type="button" onclick="togglePassword()"
                            style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 4px; color: #64748b; display: flex; align-items: center;">
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;" id="passwordIcon">visibility</span>
                    </button>
                </div>
                <small style="color: #94a3b8; font-size: 0.75rem;">M&iacute;nimo 8 caracteres, incluye n&uacute;meros y s&iacute;mbolos.</small>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label class="form-label-custom">Idioma <span style="color: #ef4444;">*</span></label>
                <select class="form-input-custom" name="id_idioma" required>
                    <option value="">— Seleccionar —</option>
                    <?php foreach ($idiomas as $idi): ?>
                        <option value="<?= (int) $idi['id_idioma'] ?>"
                            <?= ((int) ($usuarioEditar['id_idioma'] ?? $old['id_idioma'] ?? 0) === (int) $idi['id_idioma']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($idi['nombre']) ?> (<?= strtoupper(htmlspecialchars($idi['codigo'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <p style="font-weight: 600; color: #1e293b; font-size: 0.9rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span class="material-symbols-outlined" style="font-size: 1.1rem;">manage_accounts</span>
                Rol y Permisos
            </p>

            <div style="margin-bottom: 1rem;">
                <label class="form-label-custom">Rol <span style="color: #ef4444;">*</span></label>
                <select class="form-input-custom" name="rol" id="rolSelect" required onchange="toggleRolWarning()">
                    <option value="cliente" <?= (($usuarioEditar['rol'] ?? $old['rol'] ?? '') === 'cliente') ? 'selected' : '' ?>>Cliente</option>
                    <option value="admin" <?= (($usuarioEditar['rol'] ?? $old['rol'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <div id="rolWarning" class="warning-role" style="<?= (($usuarioEditar['rol'] ?? $old['rol'] ?? '') === 'admin') ? '' : 'display: none;' ?>">
                <span class="material-symbols-outlined" style="font-size: 1.2rem; color: #d97706; flex-shrink: 0;">warning</span>
                <span>Este usuario tendr&aacute; acceso completo al panel de administraci&oacute;n.</span>
            </div>

            <div style="padding-top: 1rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" onclick="toggleDrawer()"
                        style="padding: 0.7rem 1.5rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-weight: 600; color: #475569; background: white; cursor: pointer;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding: 0.7rem 2rem; background: #1e293b; color: white; border-radius: 0.5rem; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;"
                        onmouseover="this.style.background='#0f172a'" onmouseout="this.style.background='#1e293b'">
                    <span class="material-symbols-outlined" style="font-size: 1.1rem;">save</span>
                    <?= $usuarioEditar ? 'Guardar Cambios' : 'Crear Usuario' ?>
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

    function togglePassword() {
        const field = document.getElementById('passwordField');
        const icon = document.getElementById('passwordIcon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            field.type = 'password';
            icon.textContent = 'visibility';
        }
    }

    function toggleRolWarning() {
        const select = document.getElementById('rolSelect');
        const warning = document.getElementById('rolWarning');
        warning.style.display = select.value === 'admin' ? '' : 'none';
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
