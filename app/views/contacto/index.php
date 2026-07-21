<style>
    .contact-header { border-bottom: 1px solid #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 2rem; }
    .contact-card { border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #fff; padding: 1.5rem; height: 100%; transition: box-shadow 0.2s ease; }
    .contact-card:hover { box-shadow: 0 4px 20px rgba(30,41,59,0.05); }
    .contact-icon { width: 44px; height: 44px; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .form-input-custom { border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 0.7rem 1rem; width: 100%; transition: all 0.2s; box-sizing: border-box; font-family: inherit; font-size: inherit; }
    .form-input-custom:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79,70,229,0.15); outline: none; }
    .form-label-custom { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 0.4rem; display: block; }
</style>

<div class="container py-5" style="max-width: 1320px;">
    <div class="contact-header text-center">
        <h1 class="fw-bold" style="color: #1e293b; font-size: 2rem;">Contacto</h1>
        <p class="mb-0" style="color: #64748b; font-size: 1rem;">Estamos para ayudarte. Completa el formulario o escribenos directamente.</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="contact-card d-flex flex-column align-items-center text-center">
                <div class="contact-icon mb-3" style="background: #eef2ff;">
                    <span class="material-symbols-outlined" style="color: #4f46e5;">location_on</span>
                </div>
                <h6 class="fw-bold mb-1" style="color: #1e293b;">Direccion</h6>
                <p class="small mb-0" style="color: #64748b;">Westland Mall, Local 26<br>Autopista Arraiján-La Chorrera<br>Arraiján, Panamá</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="contact-card d-flex flex-column align-items-center text-center">
                <div class="contact-icon mb-3" style="background: #d1fae5;">
                    <span class="material-symbols-outlined" style="color: #059669;">mail</span>
                </div>
                <h6 class="fw-bold mb-1" style="color: #1e293b;">Correo Electronico</h6>
                <p class="small mb-0" style="color: #64748b;">contacto@e-comerceds7.com<br>ds7@utp.edu.pa</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="contact-card d-flex flex-column align-items-center text-center">
                <div class="contact-icon mb-3" style="background: #fef3c7;">
                    <span class="material-symbols-outlined" style="color: #d97706;">group</span>
                </div>
                <h6 class="fw-bold mb-1" style="color: #1e293b;">Equipo</h6>
                <p class="small mb-0" style="color: #64748b;">Estudiantes de Desarrollo de Software VII<br>Grupo de proyecto - e-comerceDS7</p>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="contact-card">
                <h5 class="fw-bold mb-3" style="color: #1e293b;">Envia tu mensaje</h5>

                <?php if (!empty($exito)): ?>
                    <div class="px-4 py-3 rounded-3 d-flex align-items-center gap-2 mb-4" style="background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46;">
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;">check_circle</span>
                        <?= htmlspecialchars($exito) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errores)): ?>
                    <div class="px-4 py-3 rounded-3 mb-4" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
                        <?php foreach ($errores as $e): ?>
                            <p class="mb-0 d-flex align-items-center gap-2"><span class="material-symbols-outlined" style="font-size: 1.2rem;">error</span><?= htmlspecialchars($e) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>contacto/index">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-input-custom" name="nombre" value="<?= htmlspecialchars($old['nombre'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-input-custom" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Asunto</label>
                        <input type="text" class="form-input-custom" name="asunto" value="<?= htmlspecialchars($old['asunto'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label-custom">Mensaje <span class="text-danger">*</span></label>
                        <textarea class="form-input-custom" name="mensaje" rows="5" required><?= htmlspecialchars($old['mensaje'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn fw-bold d-flex align-items-center gap-2" style="background: #1e293b; color: white; border-radius: 0.5rem; padding: 0.7rem 2rem;">
                        <span class="material-symbols-outlined" style="font-size: 1.2rem;">send</span>
                        Enviar mensaje
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
