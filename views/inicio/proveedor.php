<?php
defined('APP') or exit;
$pendientes = count(array_filter($ofertas, fn($o) => $o['estado'] === 'Pendiente'));
$aceptadas = count(array_filter($ofertas, fn($o) => $o['estado'] === 'Aceptada'));
$sinOfertar = count(array_filter($necesidades, fn($n) => !$n['mi_oferta']));
?>
<h1 class="titulo">Hola, <?= e(Auth::user()['nombre']) ?></h1>
<p class="text-secondary">Revisa lo que necesita el hotel y envía tus ofertas.</p>

<div class="row g-3 mb-4">
  <div class="col-4"><a href="<?= url('suministros/index') ?>" class="cifra"><span><?= $sinOfertar ?></span>Por ofertar</a></div>
  <div class="col-4"><div class="cifra"><span><?= $pendientes ?></span>Ofertas en espera</div></div>
  <div class="col-4"><div class="cifra"><span><?= $aceptadas ?></span>Ofertas ganadas</div></div>
</div>
<a href="<?= url('suministros/index') ?>" class="btn btn-dorado">Ver necesidades del hotel</a>
