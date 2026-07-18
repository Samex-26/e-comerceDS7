<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
    .star-filled { font-variation-settings: 'FILL' 1; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .drawer-overlay { transition: opacity 0.3s ease; backdrop-filter: blur(4px); }
    .drawer-content { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .drawer-hidden .drawer-overlay { opacity: 0; pointer-events: none; }
    .drawer-hidden .drawer-content { transform: translateX(100%); }
    .toast-success { animation: slideIn 0.3s ease, fadeOut 0.3s ease 3.7s forwards; }
    .toast-error { animation: slideIn 0.3s ease, fadeOut 0.3s ease 4.7s forwards; }
    @keyframes slideIn { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes fadeOut { to { opacity: 0; transform: translateY(-20px); } }
    .tr-hover:hover { background-color: #f8fafc; }
    .actions-group { opacity: 0; transition: opacity 0.15s; }
    .tr-hover:hover .actions-group { opacity: 1; }
    .btn-primary { background: #fd761a; color: white; font-weight: 700; border: none; cursor: pointer; }
    .btn-primary:hover { background: #e06500; }
</style>

<?php
$editarId = isset($_GET['editar']) ? (int) $_GET['editar'] : 0;
$editarProveedor = $editarId && isset($proveedor) ? $proveedor : null;
$showDrawer = $editarProveedor || !empty($errores);
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
    <header style="height: 64px; background: white; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; padding: 0 32px; position: sticky; top: 0; z-index: 30;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;">Gesti&oacute;n de Proveedores</h2>
            <p style="font-size: 14px; color: #64748b; margin: 2px 0 0 0;">Administra tus contactos de suministros y sus calificaciones.</p>
        </div>
        <button onclick="toggleDrawer()" style="display: flex; align-items: center; gap: 8px; background: #fd761a; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <span class="material-symbols-outlined">add</span>
            Nuevo proveedor
        </button>
    </header>

    <main style="padding: 32px;">
        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; text-align: left; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Nombre</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Tel&eacute;fono</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Celular</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Direcci&oacute;n</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Sitio web</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Calificaci&oacute;n</th>
                            <th style="padding: 16px 24px; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($proveedores)): ?>
                            <tr>
                                <td colspan="7" style="padding: 48px 24px; text-align: center; color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 36px; margin-bottom: 8px; display: block;">local_shipping</span>
                                    No hay proveedores registrados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($proveedores as $p): ?>
                            <tr class="tr-hover" style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 20px 24px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; border-radius: 8px; background: #dbeafe; display: flex; align-items: center; justify-content: center; color: #1e293b; font-weight: 700; font-size: 14px;">
                                            <?= strtoupper(substr($p['nombre'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <p style="font-weight: 600; color: #1e293b; margin: 0;"><?= htmlspecialchars($p['nombre']) ?></p>
                                            <?php if (!empty($p['email'])): ?>
                                                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;"><?= htmlspecialchars($p['email']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 20px 24px; color: #475569;"><?= htmlspecialchars($p['telefono'] ?: '&mdash;') ?></td>
                                <td style="padding: 20px 24px; color: #475569;"><?= htmlspecialchars($p['celular'] ?: '&mdash;') ?></td>
                                <td style="padding: 20px 24px; color: #475569; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($p['direccion'] ?: '&mdash;') ?></td>
                                <td style="padding: 20px 24px;">
                                    <?php if (!empty($p['sitio_web'])): ?>
                                        <a href="<?= htmlspecialchars($p['sitio_web']) ?>" target="_blank" style="color: #fd761a; font-weight: 500; display: flex; align-items: center; gap: 4px; font-size: 14px; text-decoration: none;">
                                            <?= htmlspecialchars(preg_replace('#^https?://#', '', $p['sitio_web'])) ?>
                                            <span class="material-symbols-outlined" style="font-size: 14px;">open_in_new</span>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #94a3b8;">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 20px 24px;">
                                    <div style="display: flex; gap: 2px;">
                                        <?php $stars = (int) ($p['calificacion_estrellas'] ?? 0); ?>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="material-symbols-outlined" style="font-size: 18px; <?= $i <= $stars ? 'color: #fd761a; font-variation-settings: \'FILL\' 1;' : 'color: #cbd5e1;' ?>">star</span>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td style="padding: 20px 24px; text-align: right;">
                                    <div class="actions-group" style="display: flex; justify-content: flex-end; gap: 4px;">
                                        <a href="<?= BASE_URL ?>proveedor/admin?editar=<?= (int) $p['id_proveedor'] ?>"
                                           style="padding: 8px; color: #64748b; border-radius: 8px; text-decoration: none; display: inline-flex;"
                                           onmouseover="this.style.color='#fd761a';this.style.background='#fff7ed'"
                                           onmouseout="this.style.color='#64748b';this.style.background='transparent'">
                                            <span class="material-symbols-outlined">edit</span>
                                        </a>
                                        <a href="<?= BASE_URL ?>proveedor/eliminar/<?= (int) $p['id_proveedor'] ?>"
                                           style="padding: 8px; color: #64748b; border-radius: 8px; text-decoration: none; display: inline-flex;"
                                           onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'"
                                           onmouseout="this.style.color='#64748b';this.style.background='transparent'"
                                           onclick="return confirm('&iquest;Est&aacute; seguro de eliminar este proveedor?')">
                                            <span class="material-symbols-outlined">delete</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px 24px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0;">
                <p style="font-size: 14px; color: #64748b; margin: 0;">Mostrando <?= count($proveedores) ?> proveedor(es)</p>
            </div>
        </div>
    </main>
</div>

<div style="position: fixed; inset: 0; z-index: 50; <?= $showDrawer ? '' : 'pointer-events: none;' ?>" id="drawer" class="<?= $showDrawer ? '' : 'drawer-hidden' ?>">
    <div class="drawer-overlay" style="position: absolute; inset: 0; background: rgba(30,41,59,0.4); cursor: pointer;" onclick="toggleDrawer()"></div>
    <div class="drawer-content" style="position: absolute; right: 0; top: 0; height: 100vh; width: 100%; max-width: 512px; background: white; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column;">
        <div style="padding: 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;"><?= $editarProveedor ? 'Editar Proveedor' : 'Nuevo Proveedor' ?></h3>
            <button onclick="toggleDrawer()" style="padding: 8px; border: none; background: transparent; cursor: pointer; border-radius: 9999px; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='transparent'">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>proveedor/admin" style="flex: 1; overflow-y: auto; padding: 32px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= $editarProveedor ? (int) $editarProveedor['id_proveedor'] : '' ?>">

            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Nombre del Proveedor <span style="color: #ef4444;">*</span></label>
                <input type="text" name="nombre" required
                       style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                       onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                       onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                       placeholder="Ej. TechCorp S.A."
                       value="<?= htmlspecialchars($editarProveedor['nombre'] ?? $old['nombre'] ?? '') ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Tel&eacute;fono</label>
                    <input type="text" name="telefono"
                           style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                           onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                           onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                           placeholder="(01) 000-0000"
                           value="<?= htmlspecialchars($editarProveedor['telefono'] ?? $old['telefono'] ?? '') ?>">
                </div>
                <div>
                    <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Celular</label>
                    <input type="text" name="celular"
                           style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                           onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                           onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                           placeholder="999 999 999"
                           value="<?= htmlspecialchars($editarProveedor['celular'] ?? $old['celular'] ?? '') ?>">
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Email</label>
                <input type="email" name="email"
                       style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                       onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                       onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                       placeholder="contacto@ejemplo.pe"
                       value="<?= htmlspecialchars($editarProveedor['email'] ?? $old['email'] ?? '') ?>">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Direcci&oacute;n</label>
                <input type="text" name="direccion"
                       style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                       onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                       onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                       placeholder="Calle, N&uacute;mero, Distrito"
                       value="<?= htmlspecialchars($editarProveedor['direccion'] ?? $old['direccion'] ?? '') ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Ciudad</label>
                    <input type="text" name="ciudad"
                           style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                           onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                           onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                           placeholder="Lima"
                           value="<?= htmlspecialchars($editarProveedor['ciudad'] ?? $old['ciudad'] ?? '') ?>">
                </div>
                <div>
                    <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Sitio Web</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;">https://</span>
                        <input type="text" name="sitio_web"
                               style="width: 100%; padding: 12px 16px 12px 80px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                               onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                               onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                               placeholder="www.ejemplo.pe"
                               value="<?= htmlspecialchars(preg_replace('#^https?://#', '', $editarProveedor['sitio_web'] ?? $old['sitio_web'] ?? '')) ?>">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 12px;">Calificaci&oacute;n</label>
                <div style="display: flex; align-items: center; gap: 8px;" id="star-selector">
                    <?php $rating = (int) ($editarProveedor['calificacion_estrellas'] ?? $old['calificacion_estrellas'] ?? 0); ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" onclick="setRating(<?= $i ?>)" style="padding: 4px; border: none; background: transparent; cursor: pointer;">
                            <span class="material-symbols-outlined" style="font-size: 36px; <?= $i <= $rating ? 'color: #fd761a; font-variation-settings: \'FILL\' 1;' : 'color: #cbd5e1;' ?>">star</span>
                        </button>
                    <?php endfor; ?>
                    <input type="hidden" name="calificacion_estrellas" id="calificacion_estrellas" value="<?= $rating ?>">
                </div>
                <p style="font-size: 12px; color: #94a3b8; font-style: italic; margin: 4px 0 0 0;">Califica la confiabilidad y calidad del proveedor.</p>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; display: block; margin-bottom: 8px;">Notas</label>
                <textarea name="notas" rows="3"
                          style="width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; resize: none; box-sizing: border-box; font-family: inherit; font-size: inherit;"
                          onfocus="this.style.borderColor='#fd761a';this.style.boxShadow='0 0 0 2px rgba(253,118,26,0.25)'"
                          onblur="this.style.borderColor='#cbd5e1';this.style.boxShadow='none'"
                          placeholder="Notas adicionales..."><?= htmlspecialchars($editarProveedor['notas'] ?? $old['notas'] ?? '') ?></textarea>
            </div>

            <div style="padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 16px;">
                <button type="button" onclick="toggleDrawer()"
                        style="padding: 12px 24px; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 600; color: #475569; background: white; cursor: pointer;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding: 12px 32px; background: #fd761a; color: white; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);"
                        onmouseover="this.style.background='#e06500'" onmouseout="this.style.background='#fd761a'">
                    <?= $editarProveedor ? 'Actualizar' : 'Guardar cambios' ?>
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

    function setRating(rating) {
        document.getElementById('calificacion_estrellas').value = rating;
        const stars = document.querySelectorAll('#star-selector .material-symbols-outlined');
        stars.forEach((star, idx) => {
            if (idx < rating) {
                star.style.color = '#fd761a';
                star.style.fontVariationSettings = "'FILL' 1";
            } else {
                star.style.color = '#cbd5e1';
                star.style.fontVariationSettings = "'FILL' 0";
            }
        });
    }

    <?php if ($showDrawer): ?>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('drawer').classList.remove('drawer-hidden');
        document.body.style.overflow = 'hidden';
    });
    <?php endif; ?>

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const drawer = document.getElementById('drawer');
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
