<?php
defined('APP') or exit;
$proximas = array_filter($reservas, fn($r) => in_array($r['estado'], ['Confirmada', 'Hospedado'], true));
?>
<div class="bienvenida">
  <h1 class="titulo text-white">Hola, <?= e(explode(' ', Auth::user()['nombre'])[0]) ?></h1>
  <p class="mb-3">¿Cuándo nos visitas?</p>
  <form class="row g-2 buscador" action="index.php">
    <input type="hidden" name="r" value="reservas/buscar">
    <div class="col-6 col-md-3"><label class="small">Entrada</label><input type="date" name="entrada" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required></div>
    <div class="col-6 col-md-3"><label class="small">Salida</label><input type="date" name="salida" class="form-control" value="<?= date('Y-m-d', strtotime('+2 day')) ?>" required></div>
    <div class="col-6 col-md-3"><label class="small">Personas</label><input type="number" name="huespedes" class="form-control" value="2" min="1" max="10"></div>
    <div class="col-6 col-md-3 d-flex align-items-end"><button class="btn btn-dorado w-100">Ver habitaciones</button></div>
  </form>
</div>

<h2 class="subtitulo mt-4">Tus próximas estadías</h2>
<?php foreach ($proximas as $r): ?>
  <a href="<?= url('reservas/ver', ['id' => $r['id']]) ?>" class="tarjeta d-flex justify-content-between align-items-center mb-2 text-decoration-none text-body">
    <span><strong>Habitación <?= $r['numero'] ?></strong> · <?= $r['tipo'] ?><br><small class="text-secondary"><?= fecha_corta($r['fecha_entrada']) ?> → <?= fecha_corta($r['fecha_salida']) ?></small></span>
    <?= etiqueta($r['estado']) ?>
  </a>
<?php endforeach; ?>
<?php if (!$proximas): ?><p class="text-secondary">No tienes reservas próximas.</p><?php endif; ?>
