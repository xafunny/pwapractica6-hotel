<?php
// Reservas para el personal
defined('APP') or exit;
$js = ['reservas.js'];
$atiende = Auth::es(Auth::ADMIN, Auth::RECEPCIONISTA);
$filtros = ['' => 'Activas', 'llegadas' => 'Llegan hoy', 'salidas' => 'Salen hoy', 'Finalizada' => 'Finalizadas', 'Cancelada' => 'Canceladas', 'todas' => 'Todas'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="titulo mb-0">Reservas</h1>
  <div class="d-flex gap-2">
    <form class="d-flex"><input type="hidden" name="r" value="reservas/index"><input type="hidden" name="f" value="<?= e($filtro) ?>">
      <input type="search" name="q" class="form-control" placeholder="Cliente, cédula o habitación" value="<?= e($q) ?>"></form>
    <?php if ($atiende): ?><a href="<?= url('reservas/buscar') ?>" class="btn btn-dorado text-nowrap">Nueva reserva</a><?php endif; ?>
  </div>
</div>

<ul class="nav nav-underline mb-3">
  <?php foreach ($filtros as $k => $txt): ?>
    <li class="nav-item"><a class="nav-link <?= $filtro === $k ? 'active' : '' ?>" href="<?= url('reservas/index', array_filter(['f' => $k])) ?>"><?= $txt ?></a></li>
  <?php endforeach; ?>
</ul>

<div class="tarjeta p-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>#</th><th>Cliente</th><th>Hab.</th><th>Fechas</th><th class="text-end">Total</th><th>Estado</th><?php if ($atiende): ?><th></th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($reservas as $r): ?>
        <tr>
          <td><a href="<?= url('reservas/ver', ['id' => $r['id']]) ?>"><?= $r['id'] ?></a></td>
          <td><?= e($r['cliente']) ?><br><small class="text-secondary"><?= e($r['documento']) ?></small></td>
          <td><?= $r['numero'] ?> <small class="text-secondary"><?= $r['tipo'] ?></small></td>
          <td class="small text-nowrap"><?= fecha_corta($r['fecha_entrada']) ?> → <?= fecha_corta($r['fecha_salida']) ?></td>
          <td class="text-end"><?= dinero($r['total']) ?></td>
          <td data-etiqueta><?= etiqueta($r['estado']) ?></td>
          <?php if ($atiende): ?>
            <td class="text-end text-nowrap">
              <?php if ($r['estado'] === 'Confirmada' && $r['fecha_entrada'] <= date('Y-m-d')): ?>
                <button class="btn btn-sm btn-outline-dark" data-estado="Hospedado" data-reserva="<?= $r['id'] ?>">Check-in</button>
              <?php elseif ($r['estado'] === 'Hospedado'): ?>
                <button class="btn btn-sm btn-outline-dark" data-estado="Finalizada" data-reserva="<?= $r['id'] ?>">Check-out</button>
              <?php endif; ?>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      <?php if (!$reservas): ?><tr><td colspan="7" class="text-center text-secondary py-4">No hay reservas aquí.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
