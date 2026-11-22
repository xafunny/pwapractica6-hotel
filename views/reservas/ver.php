<?php
defined('APP') or exit;
$js = ['reservas.js'];
$esCliente = Auth::es(Auth::CLIENTE);
$atiende = Auth::es(Auth::ADMIN, Auth::RECEPCIONISTA);
$hoy = date('Y-m-d');
?>
<a href="<?= url('reservas/index') ?>" class="small"><i class="bi bi-arrow-left"></i> Volver</a>
<div class="d-flex justify-content-between align-items-center mt-2 mb-3">
  <h1 class="titulo mb-0">Reserva #<?= $r['id'] ?></h1>
  <span data-etiqueta><?= etiqueta($r['estado']) ?></span>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="tarjeta">
      <div class="row text-center mb-3">
        <div class="col"><small class="text-secondary d-block">Entrada</small><strong><?= fecha_corta($r['fecha_entrada']) ?></strong></div>
        <div class="col"><small class="text-secondary d-block">Salida</small><strong><?= fecha_corta($r['fecha_salida']) ?></strong></div>
        <div class="col"><small class="text-secondary d-block">Noches</small><strong><?= noches($r['fecha_entrada'], $r['fecha_salida']) ?></strong></div>
      </div>
      <table class="table table-sm mb-0">
        <tr><th>Habitación</th><td><?= $r['numero'] ?> (<?= $r['tipo'] ?>)</td></tr>
        <tr><th>Personas</th><td><?= $r['huespedes'] ?></td></tr>
        <tr><th>Precio por noche</th><td><?= dinero($r['precio']) ?></td></tr>
        <tr><th>Total</th><td class="fw-semibold"><?= dinero($r['total']) ?></td></tr>
        <?php if (!$esCliente): ?>
          <tr><th>Cliente</th><td><?= Auth::es(Auth::ADMIN, Auth::RECEPCIONISTA) ? '<a href="' . url('clientes/ver', ['id' => $r['id_cliente']]) . '">' . e($r['cliente']) . '</a>' : e($r['cliente']) ?> · <?= e($r['documento']) ?> · <?= e($r['telefono']) ?></td></tr>
          <tr><th>Registrada por</th><td><?= e($r['registro']) ?: 'El cliente (en línea)' ?></td></tr>
        <?php endif; ?>
        <?php if ($r['notas']): ?><tr><th>Notas</th><td><?= e($r['notas']) ?></td></tr><?php endif; ?>
        <tr><th>Fecha de reserva</th><td><?= fecha($r['fecha_reserva'], true) ?></td></tr>
      </table>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="d-grid gap-2" data-acciones>
      <?php if ($atiende && $r['estado'] === 'Confirmada' && $r['fecha_entrada'] <= $hoy): ?>
        <button class="btn btn-dorado" data-estado="Hospedado" data-reserva="<?= $r['id'] ?>" data-recargar>Registrar check-in</button>
      <?php endif; ?>
      <?php if ($atiende && $r['estado'] === 'Hospedado'): ?>
        <button class="btn btn-dorado" data-estado="Finalizada" data-reserva="<?= $r['id'] ?>" data-recargar>Registrar check-out</button>
      <?php endif; ?>
      <?php if ($r['estado'] === 'Confirmada' && ($atiende || $r['fecha_entrada'] > $hoy)): ?>
        <button class="btn btn-outline-danger" data-estado="Cancelada" data-reserva="<?= $r['id'] ?>" data-recargar>Cancelar reserva</button>
      <?php elseif ($esCliente && $r['estado'] === 'Confirmada'): ?>
        <p class="small text-secondary">Para cambios el mismo día de llegada, comunícate con recepción.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
