<?php
defined('APP') or exit;
$js = ['habitaciones.js'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="titulo mb-0">Habitaciones</h1>
  <button class="btn btn-dorado" data-bs-toggle="modal" data-bs-target="#modalHab"><i class="bi bi-plus"></i> Agregar</button>
</div>

<div class="tarjeta p-0">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>N.º</th><th>Tipo</th><th>Capacidad</th><th class="text-end">Precio / noche</th><th>Hoy</th><th>En servicio</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($habitaciones as $h): ?>
        <tr>
          <td class="fw-semibold"><?= $h['numero'] ?></td>
          <td><?= $h['tipo'] ?><br><small class="text-secondary"><?= e($h['descripcion']) ?></small></td>
          <td><?= $h['capacidad'] ?> pers.</td>
          <td class="text-end"><?= dinero($h['precio']) ?></td>
          <td data-situacion><?= etiqueta($h['situacion']) ?><?= $h['huesped'] ? '<br><small class="text-secondary">' . e($h['huesped']) . ' hasta ' . fecha_corta($h['fecha_salida']) . '</small>' : '' ?></td>
          <td><div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" data-mantenimiento="<?= $h['id'] ?>" <?= $h['estado'] === 'Disponible' ? 'checked' : '' ?> aria-label="En servicio"></div></td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalHab"
                    data-datos='<?= e(json_encode(array_intersect_key($h, array_flip(['id', 'numero', 'tipo', 'capacidad', 'precio', 'descripcion'])))) ?>' aria-label="Editar"><i class="bi bi-pencil"></i></button>
            <form method="post" action="<?= url('habitaciones/eliminar') ?>" class="d-inline" data-confirmar="¿Eliminar la habitación <?= $h['numero'] ?>?">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= $h['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" aria-label="Eliminar"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalHab" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('habitaciones/guardar') ?>" novalidate data-nuevo="Nueva habitación" data-editar="Editar habitación">
      <div class="modal-header"><h2 class="modal-title h5">Nueva habitación</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body row g-2">
        <?= csrf_field() ?><input type="hidden" name="id">
        <div class="col-4"><label class="form-label small">Número</label><input type="number" class="form-control" name="numero" required min="1"></div>
        <div class="col-8"><label class="form-label small">Tipo</label><select class="form-select" name="tipo"><?php foreach (Habitacion::TIPOS as $t): ?><option><?= $t ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label small">Capacidad</label><input type="number" class="form-control" name="capacidad" required min="1" max="10" value="2"></div>
        <div class="col-6"><label class="form-label small">Precio por noche</label><input type="number" class="form-control" name="precio" required min="1" step="0.01"></div>
        <div class="col-12"><label class="form-label small">Descripción</label><input class="form-control" name="descripcion" maxlength="255"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-dorado">Guardar</button></div>
    </form>
  </div>
</div>
