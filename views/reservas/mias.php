<?php defined('APP') or exit; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="titulo mb-0">Mis reservas</h1>
  <a href="<?= url('reservas/buscar') ?>" class="btn btn-dorado">Reservar</a>
</div>
<?php foreach ($reservas as $r): ?>
  <a href="<?= url('reservas/ver', ['id' => $r['id']]) ?>" class="tarjeta d-flex justify-content-between align-items-center mb-2 text-decoration-none text-body">
    <span><strong>Habitación <?= $r['numero'] ?> · <?= $r['tipo'] ?></strong><br>
      <small class="text-secondary"><?= fecha_corta($r['fecha_entrada']) ?> → <?= fecha_corta($r['fecha_salida']) ?> · <?= noches($r['fecha_entrada'], $r['fecha_salida']) ?> noche(s) · <?= dinero($r['total']) ?></small></span>
    <?= etiqueta($r['estado']) ?>
  </a>
<?php endforeach; ?>
<?php if (!$reservas): ?><p class="text-secondary">Aún no tienes reservas.</p><?php endif; ?>
