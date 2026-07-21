<?php if ($exito): ?>
<div style="position: fixed; top: 80px; right: 16px; z-index: 1050; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px;" class="toast-success">
    <span class="material-symbols-outlined" style="color: #16a34a;">check_circle</span>
    <span style="font-weight: 500;"><?= htmlspecialchars($exito) ?></span>
</div>
<?php endif; ?>

<?php if (!empty($errores)): ?>
<div style="position: fixed; top: 80px; right: 16px; z-index: 1050; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); display: flex; align-items: flex-start; gap: 12px; max-width: 448px;" class="toast-error">
    <span class="material-symbols-outlined" style="color: #dc2626; flex-shrink: 0;">error</span>
    <div style="flex: 1;">
        <?php foreach ($errores as $e): ?>
            <p style="font-size: 14px; margin: 0;"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    </div>
    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #991b1b; font-size: 1.3rem; cursor: pointer; line-height: 1; flex-shrink: 0; padding: 0;">&times;</button>
</div>
<?php endif; ?>

<script>
<?php if ($exito): ?>
setTimeout(function() {
    var toast = document.querySelector('.toast-success');
    if (toast) toast.remove();
}, 4000);
<?php endif; ?>
</script>
