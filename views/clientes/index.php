<?php defined('APP') or exit; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="titulo mb-0">Clientes</h1>
  <div class="d-flex gap-2">
    <form class="d-flex"><input type="hidden" name="r" value="clientes/index"><input type="search" name="q" class="form-control" placeholder="Nombre, cédula o teléfono" value="<?= e($q) ?>"></form>
    <button class="btn btn-dorado text-nowrap" data-bs-toggle="modal" data-bs-target="#modalCliente">Nuevo</button>
  </div>
</div>

<div class="tarjeta p-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Nombre</th><th>Documento</th><th>Contacto</th><th class="text-center">Reservas</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($clientes as $c): ?>
        <tr>
          <td><a href="<?= url('clientes/ver', ['id' => $c['id']]) ?>"><?= e($c['nombre']) ?></a></td>
          <td><?= e($c['documento']) ?></td>
          <td class="small"><?= e($c['correo']) ?><br><?= e($c['telefono']) ?></td>
          <td class="text-center"><?= $c['reservas'] ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-dark" href="<?= url('reservas/buscar', ['cliente' => $c['id']]) ?>">Reservar</a>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalCliente"
                    data-datos='<?= e(json_encode(array_intersect_key($c, array_flip(['id', 'nombre', 'correo', 'telefono', 'documento'])))) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$clientes): ?><tr><td colspan="5" class="text-center text-secondary py-4">Sin resultados.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('clientes/guardar') ?>" novalidate data-nuevo="Nuevo cliente" data-editar="Editar cliente">
      <div class="modal-header"><h2 class="modal-title h5">Nuevo cliente</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body row g-2">
        <?= csrf_field() ?><input type="hidden" name="id">
        <div class="col-12"><label class="form-label small">Nombre completo</label><input class="form-control" name="nombre" required minlength="3"></div>
        <div class="col-6"><label class="form-label small">Cédula o pasaporte</label><input class="form-control" name="documento" required pattern="[A-Za-z0-9]{5,20}"></div>
        <div class="col-6"><label class="form-label small">Teléfono</label><input class="form-control" name="telefono" required pattern="\d{7,15}"></div>
        <div class="col-12"><label class="form-label small">Correo</label><input type="email" class="form-control" name="correo" required></div>
        <p class="small text-secondary mb-0 solo-nuevo">La clave inicial del cliente será su número de documento.</p>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-dorado">Guardar</button></div>
    </form>
  </div>
</div>
