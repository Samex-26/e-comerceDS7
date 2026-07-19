<nav class="navbar navbar-expand-lg sticky-top" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
    <div class="container" style="max-width: 1320px;">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>" style="color: #1e293b; font-size: 1.25rem;"><?= htmlspecialchars(APP_NAME) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border-color: #e2e8f0;">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>" style="color: #45474c;"><?= htmlspecialchars($lang['inicio']) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>producto" style="color: #45474c;"><?= htmlspecialchars($lang['productos']) ?></a></li>
                <?php if (isset($_SESSION['id_usuario']) && ($_SESSION['rol'] ?? '') === 'cliente'): ?>
                <?php
                    $cartCount = 0;
                    if (isset($_SESSION['carrito'])) {
                        foreach ($_SESSION['carrito'] as $item) {
                            $cartCount += (int) ($item['cantidad'] ?? 0);
                        }
                    }
                ?>
                <li class="nav-item">
                    <a class="nav-link position-relative d-flex align-items-center" href="<?= BASE_URL ?>carrito/ver" style="color: #1e293b;">
                        <span class="material-symbols-outlined" style="font-size: 1.5rem;">shopping_cart</span>
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill d-flex align-items-center justify-content-center" style="background: #fd761a; color: white; font-size: 0.6rem; width: 18px; height: 18px; min-width: 18px;">
                                <?= $cartCount > 99 ? '99+' : $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <li class="nav-item">
                        <span class="nav-link" style="color: #64748b; font-size: 0.9rem;">
                            <?= htmlspecialchars(sprintf($lang['bienvenida_usuario'], $_SESSION['nombre'])) ?>
                        </span>
                    </li>
                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" href="#" role="button" data-bs-toggle="dropdown" style="color: #1e293b; font-weight: 600;">
                                <span class="material-symbols-outlined" style="font-size: 1.2rem;">dashboard</span>
                                Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 4px 20px rgba(30,41,59,0.05);">
                                <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>producto/admin"><span class="material-symbols-outlined" style="font-size: 1.2rem; color: #64748b;">inventory_2</span>Productos</a></li>
                                <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>categoria/admin"><span class="material-symbols-outlined" style="font-size: 1.2rem; color: #64748b;">category</span>Categorías</a></li>
                                <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>proveedor/admin"><span class="material-symbols-outlined" style="font-size: 1.2rem; color: #64748b;">local_shipping</span>Proveedores</a></li>
                                <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>inventario/admin"><span class="material-symbols-outlined" style="font-size: 1.2rem; color: #64748b;">warehouse</span>Inventario</a></li>
                                <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>usuario/admin"><span class="material-symbols-outlined" style="font-size: 1.2rem; color: #64748b;">group</span>Usuarios</a></li>
                                <li><a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= BASE_URL ?>venta/historial"><span class="material-symbols-outlined" style="font-size: 1.2rem; color: #64748b;">payments</span>Ventas</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <form method="POST" action="<?= BASE_URL ?>auth/logout" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="nav-link btn py-1 px-3 fw-semibold" style="border: 1px solid #e2e8f0; color: #1e293b; border-radius: 0.375rem;"><?= htmlspecialchars($lang['cerrar_sesion']) ?></button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>auth/login" style="color: #45474c;"><?= htmlspecialchars($lang['inicio_sesion']) ?></a></li>
                    <li class="nav-item"><a class="nav-link btn py-1 px-3 fw-bold" style="background: #fd761a; color: white; border-radius: 0.375rem;" href="<?= BASE_URL ?>auth/registro"><?= htmlspecialchars($lang['registro']) ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
