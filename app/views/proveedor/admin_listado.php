<style>
.provider-wrap{max-width:1180px;margin:auto}.provider-table{min-width:850px}.provider-table th{white-space:nowrap;font-size:.75rem;text-transform:uppercase;color:#64748b}.provider-table th:first-child{width:25%}.provider-table th:nth-child(2){width:22%}.provider-table th:nth-child(3){width:15%}.provider-table th:nth-child(4){width:14%}.provider-table th:nth-child(5){width:10%}.provider-table th:last-child{width:14%}.provider-table td{vertical-align:middle}.provider-actions{display:flex;gap:.5rem;justify-content:flex-end;white-space:nowrap}.provider-actions .btn{display:inline-flex;align-items:center;gap:.3rem}.provider-logo{width:42px;height:42px;border-radius:10px;background:#fff3e8;color:#d85f00;display:inline-flex;align-items:center;justify-content:center;font-weight:700}.status-pill{font-size:.75rem;padding:.25rem .55rem;border-radius:999px}.status-active{background:#dcfce7;color:#166534}.status-inactive{background:#f1f5f9;color:#475569}
</style>
<main class="container-fluid py-4 px-3 px-lg-4">
 <div class="provider-wrap">
  <?php if ($exito): ?><div class="alert alert-success" role="status"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
  <?php foreach ($errores as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
   <div><h1 class="h3 mb-1">Proveedores</h1><p class="text-secondary mb-0">Administre los contactos que suministran productos al inventario.</p></div>
   <a class="btn btn-primary d-inline-flex align-items-center gap-2" href="<?= BASE_URL ?>proveedor/crear"><span class="material-symbols-outlined" aria-hidden="true">add</span>Nuevo proveedor</a>
  </div>
  <div class="card shadow-sm border-0"><div class="table-responsive"><table class="table table-hover provider-table mb-0">
   <thead class="table-light"><tr><th>Proveedor</th><th>Contacto</th><th>Ciudad</th><th>Sitio web</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
   <tbody>
   <?php if (!$proveedores): ?><tr><td colspan="6" class="text-center text-secondary py-5">No hay proveedores registrados.</td></tr><?php endif; ?>
   <?php foreach ($proveedores as $p): ?><tr>
    <td><div class="d-flex align-items-center gap-2"><span class="provider-logo"><?= htmlspecialchars(mb_strtoupper(mb_substr($p['nombre'],0,2))) ?></span><strong><?= htmlspecialchars($p['nombre']) ?></strong></div></td>
    <td><div><?= htmlspecialchars($p['telefono'] ?: ($p['celular'] ?: '—')) ?></div><small class="text-secondary"><?= htmlspecialchars($p['email'] ?: 'Sin correo') ?></small></td>
    <td><?= htmlspecialchars($p['ciudad'] ?: '—') ?></td>
    <td><?php if ($p['sitio_web']): ?><a href="<?= htmlspecialchars($p['sitio_web']) ?>" target="_blank" rel="noopener noreferrer">Visitar sitio</a><?php else: ?>—<?php endif; ?></td>
    <td><span class="status-pill <?= $p['activo'] ? 'status-active' : 'status-inactive' ?>"><?= $p['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
    <td><div class="provider-actions"><a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>proveedor/editar/<?= (int)$p['id_proveedor'] ?>" title="Editar <?= htmlspecialchars($p['nombre']) ?>"><span class="material-symbols-outlined" aria-hidden="true">edit</span>Editar</a><button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteProviderModal" data-id="<?= (int)$p['id_proveedor'] ?>" data-name="<?= htmlspecialchars($p['nombre']) ?>"><span class="material-symbols-outlined" aria-hidden="true">delete</span>Eliminar</button></div></td>
   </tr><?php endforeach; ?>
   </tbody>
  </table></div></div>
 </div>
</main>
<div class="modal fade" id="deleteProviderModal" tabindex="-1" aria-labelledby="deleteProviderTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="deleteProviderTitle">Eliminar proveedor</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body">¿Desea eliminar a <strong id="deleteProviderName"></strong>? Esta acción no estará disponible si tiene inventario asociado.</div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><form id="deleteProviderForm" method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"><button class="btn btn-danger" type="submit">Eliminar proveedor</button></form></div></div></div></div>
<script>document.getElementById('deleteProviderModal').addEventListener('show.bs.modal',function(e){const b=e.relatedTarget;document.getElementById('deleteProviderName').textContent=b.dataset.name;document.getElementById('deleteProviderForm').action='<?= BASE_URL ?>proveedor/eliminar/'+b.dataset.id;});</script>
