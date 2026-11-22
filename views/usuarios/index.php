<?php
defined('APP') or exit;
$js = ['usuarios.js'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="titulo mb-0">Usuarios</h1>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2"><input type="hidden" name="r" value="usuarios/index">
      <select name="rol" class="form-select" onchange="this.form.submit()" aria-label="Rol"><option value="0">Todos los roles</option>
        <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>" <?= $rol === (int) $r['id'] ? 'selected' : '' ?>><?= $r['nombre'] ?></option><?php endforeach; ?></select>
      <input type="search" name="q" class="form-control" placeholder="Buscar…" value="<?= e($q) ?>"></form>
    <button class="btn btn-dorado text-nowrap" data-bs-toggle="modal" data-bs-target="#modalUsuario">Nuevo</button>
  </div>
</div>

<div class="tarjeta p-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Nombre</th><th>Rol</th><th>Contacto</th><th>Activo</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($usuarios as $u): $yo = (int) $u['id'] === Auth::id(); ?>
        <tr>
          <td><?= e($u['nombre']) ?><?= $yo ? ' <small class="text-secondary">(tú)</small>' : '' ?><?= $u['empresa'] ? '<br><small class="text-secondary">' . e($u['empresa']) . '</small>' : '' ?></td>
          <td><span class="badge rol-<?= $u['id_rol'] ?>"><?= $u['rol'] ?></span></td>
          <td class="small"><?= e($u['correo']) ?><br><?= e($u['telefono']) ?></td>
          <td><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" data-activo="<?= $u['id'] ?>" <?= $u['activo'] ? 'checked' : '' ?> <?= $yo ? 'disabled' : '' ?> aria-label="Activo"></div></td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalUsuario"
                    data-datos='<?= e(json_encode(array_intersect_key($u, array_flip(['id', 'nombre', 'correo', 'id_rol', 'telefono', 'documento', 'empresa'])))) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
            <?php if (!$yo): ?>
              <form method="post" action="<?= url('usuarios/eliminar') ?>" class="d-inline" data-confirmar="¿Eliminar a <?= e($u['nombre']) ?>?">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('usuarios/guardar') ?>" novalidate data-nuevo="Nuevo usuario" data-editar="Editar usuario">
      <div class="modal-header"><h2 class="modal-title h5">Nuevo usuario</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body row g-2">
        <?= csrf_field() ?><input type="hidden" name="id">
        <div class="col-7"><label class="form-label small">Nombre</label><input class="form-control" name="nombre" required minlength="3"></div>
        <div class="col-5"><label class="form-label small">Rol</label><select class="form-select" name="id_rol" id="rolUsuario"><?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>"><?= $r['nombre'] ?></option><?php endforeach; ?></select></div>
        <div class="col-7"><label class="form-label small">Correo</label><input type="email" class="form-control" name="correo" required></div>
        <div class="col-5"><label class="form-label small">Teléfono</label><input class="form-control" name="telefono" pattern="\d{7,15}"></div>
        <div class="col-12 solo-rol" data-rol="4"><label class="form-label small">Cédula o pasaporte</label><input class="form-control" name="documento"></div>
        <div class="col-12 solo-rol" data-rol="5"><label class="form-label small">Empresa</label><input class="form-control" name="empresa"></div>
        <div class="col-12"><label class="form-label small">Contraseña <span class="text-secondary solo-editar">(vacío = no cambiar)</span></label><input type="password" class="form-control" name="contrasena" minlength="6" data-obligatorio-nuevo></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-dorado">Guardar</button></div>
    </form>
  </div>
</div>
