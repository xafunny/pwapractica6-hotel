<?php
// Buscar habitaciones libres y reservar (cliente o recepción)
defined('APP') or exit;
$js = ['reservas.js'];
$esCliente = Auth::es(Auth::CLIENTE);
$n = $error ? 0 : noches($entrada, $salida);
$clienteElegido = (int) get('cliente');
?>
<div class="d-flex justify-content-between align-items-center mb-2">
  <h1 class="titulo mb-0"><?= $esCliente ? 'Reservar habitación' : 'Nueva reserva' ?></h1>
  <?php if (!$esCliente): ?><button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente"><i class="bi bi-person-plus"></i> Cliente nuevo</button><?php endif; ?>
</div>

<form class="tarjeta row g-2 align-items-end mb-4" action="index.php">
  <input type="hidden" name="r" value="reservas/buscar">
  <div class="col-6 col-md-3"><label class="form-label small">Entrada</label><input type="date" name="entrada" class="form-control" value="<?= e($entrada) ?>" min="<?= date('Y-m-d') ?>" required></div>
  <div class="col-6 col-md-3"><label class="form-label small">Salida</label><input type="date" name="salida" class="form-control" value="<?= e($salida) ?>" required></div>
  <div class="col-4 col-md-2"><label class="form-label small">Personas</label><input type="number" name="huespedes" class="form-control" value="<?= $huespedes ?>" min="1" max="10"></div>
  <div class="col-8 col-md-2"><label class="form-label small">Tipo</label>
    <select name="tipo" class="form-select"><option value="">Todas</option>
      <?php foreach (Habitacion::TIPOS as $t): ?><option <?= $tipo === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-md-2"><button class="btn btn-dorado w-100">Buscar</button></div>
</form>

<?php if ($error): ?>
  <div class="alert alert-warning"><?= e($error) ?></div>
<?php else: ?>
  <p class="text-secondary"><?= count($habitaciones) ?> habitación(es) libres del <?= fecha_corta($entrada) ?> al <?= fecha_corta($salida) ?> · <?= $n ?> noche(s)</p>
  <div class="row g-3">
    <?php foreach ($habitaciones as $h): ?>
      <div class="col-md-6 col-xl-4">
        <div class="habitacion">
          <div class="habitacion-foto tipo-<?= strtolower($h['tipo']) ?>"><span><?= $h['tipo'] ?></span></div>
          <div class="p-3">
            <div class="d-flex justify-content-between">
              <strong>Habitación <?= $h['numero'] ?></strong>
              <span class="text-secondary small"><i class="bi bi-people"></i> hasta <?= $h['capacidad'] ?></span>
            </div>
            <p class="small text-secondary my-2"><?= e($h['descripcion']) ?></p>
            <div class="d-flex justify-content-between align-items-end">
              <span><span class="precio"><?= dinero($h['precio']) ?></span> <small class="text-secondary">/ noche</small><br>
                <small>Total: <strong><?= dinero($h['precio'] * $n) ?></strong></small></span>
              <button class="btn btn-dorado" data-bs-toggle="modal" data-bs-target="#modalReserva"
                      data-habitacion='<?= e(json_encode(['id' => $h['id'], 'numero' => $h['numero'], 'tipo' => $h['tipo'], 'total' => $h['precio'] * $n])) ?>'>Reservar</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$habitaciones): ?>
      <div class="col-12"><div class="tarjeta text-center text-secondary">No hay habitaciones libres con esos datos. Prueba otras fechas o menos personas.</div></div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- confirmar la reserva -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('reservas/crear') ?>" novalidate>
      <div class="modal-header"><h2 class="modal-title h5">Confirmar reserva</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="id_habitacion" id="rHabitacion">
        <input type="hidden" name="entrada" value="<?= e($entrada) ?>">
        <input type="hidden" name="salida" value="<?= e($salida) ?>">
        <input type="hidden" name="huespedes" value="<?= $huespedes ?>">
        <p class="mb-1"><strong id="rTitulo"></strong></p>
        <p class="small text-secondary"><?= fecha_corta($entrada) ?> → <?= fecha_corta($salida) ?> · <?= $n ?> noche(s) · <?= $huespedes ?> persona(s)</p>

        <?php if (!$esCliente): ?>
          <label class="form-label small">Cliente</label>
          <div class="input-group mb-1">
            <select name="id_cliente" class="form-select" required>
              <option value="">Elegir cliente…</option>
              <?php foreach ($clientes as $c): ?><option value="<?= $c['id'] ?>" <?= $clienteElegido === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?> · <?= e($c['documento']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <small class="text-secondary">¿No aparece? Usa el botón «Cliente nuevo» de arriba.</small>
        <?php endif; ?>

        <label class="form-label small mt-3">Notas (opcional)</label>
        <input class="form-control" name="notas" maxlength="255" placeholder="Hora de llegada, pedidos especiales…">
        <div class="d-flex justify-content-between align-items-center border-top mt-3 pt-3">
          <span>Total a pagar</span><span class="precio fs-4" id="rTotal"></span>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Volver</button><button class="btn btn-dorado">Confirmar</button></div>
    </form>
  </div>
</div>

<?php if (!$esCliente): ?>
<!-- registrar cliente sin perder la búsqueda -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <form class="modal-content needs-validation" method="post" action="<?= url('clientes/guardar') ?>" novalidate>
      <div class="modal-header"><h2 class="modal-title h6">Cliente nuevo</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body">
        <?= csrf_field() ?>
        <input type="hidden" name="volver" value="buscar">
        <input type="hidden" name="entrada" value="<?= e($entrada) ?>"><input type="hidden" name="salida" value="<?= e($salida) ?>"><input type="hidden" name="huespedes" value="<?= $huespedes ?>">
        <input class="form-control mb-2" name="nombre" placeholder="Nombre completo" required minlength="3" aria-label="Nombre">
        <input class="form-control mb-2" name="documento" placeholder="Cédula o pasaporte" required pattern="[A-Za-z0-9]{5,20}" aria-label="Documento">
        <input class="form-control mb-2" name="telefono" placeholder="Teléfono" required pattern="\d{7,15}" aria-label="Teléfono">
        <input type="email" class="form-control" name="correo" placeholder="Correo" required aria-label="Correo">
      </div>
      <div class="modal-footer"><button class="btn btn-dorado w-100">Guardar</button></div>
    </form>
  </div>
</div>
<?php endif; ?>
