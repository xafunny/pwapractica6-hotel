<?php defined('APP') or exit; ?>
<a href="<?= url('clientes/index') ?>" class="small"><i class="bi bi-arrow-left"></i> Clientes</a>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-3">
  <div>
    <h1 class="titulo mb-0"><?= e($c['nombre']) ?></h1>
    <span class="small text-secondary"><?= e($c['documento']) ?> · <?= e($c['telefono']) ?> · <?= e($c['correo']) ?></span>
  </div>
  <a href="<?= url('reservas/buscar', ['cliente' => $c['id']]) ?>" class="btn btn-dorado">Nueva reserva</a>
</div>
<?php foreach ($reservas as $r): ?>
  <a href="<?= url('reservas/ver', ['id' => $r['id']]) ?>" class="tarjeta d-flex justify-content-between align-items-center mb-2 text-decoration-none text-body">
    <span>#<?= $r['id'] ?> · Habitación <?= $r['numero'] ?><br><small class="text-secondary"><?= fecha_corta($r['fecha_entrada']) ?> → <?= fecha_corta($r['fecha_salida']) ?> · <?= dinero($r['total']) ?></small></span>
    <?= etiqueta($r['estado']) ?>
  </a>
<?php endforeach; ?>
<?php if (!$reservas): ?><p class="text-secondary">Sin reservas todavía.</p><?php endif; ?>
