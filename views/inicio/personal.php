<?php
// Inicio de administrador, gerente y recepcionista
defined('APP') or exit;
$js = ['reservas.js'];
$puedeAtender = Auth::es(Auth::ADMIN, Auth::RECEPCIONISTA);
?>
<h1 class="titulo">Buen día, <?= e(explode(' ', Auth::user()['nombre'])[0]) ?></h1>
<p class="text-secondary"><?= fecha_corta(date('Y-m-d')) ?> · ocupación actual <strong><?= $ocupacion ?>%</strong></p>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3"><a href="<?= url('reservas/index', ['f' => 'llegadas']) ?>" class="cifra"><span><?= (int) $hoy['llegadas'] ?></span>Llegadas hoy</a></div>
  <div class="col-6 col-lg-3"><a href="<?= url('reservas/index', ['f' => 'salidas']) ?>" class="cifra"><span><?= (int) $hoy['salidas'] ?></span>Salidas hoy</a></div>
  <div class="col-6 col-lg-3"><a href="<?= url('reservas/index', ['f' => 'Hospedado']) ?>" class="cifra"><span><?= (int) $hoy['hospedados'] ?></span>Huéspedes</a></div>
  <?php if ($ingresos !== null): ?>
    <div class="col-6 col-lg-3"><div class="cifra"><span><?= dinero($ingresos) ?></span>Reservado este mes</div></div>
  <?php else: ?>
    <div class="col-6 col-lg-3"><a href="<?= url('reservas/index', ['f' => 'Confirmada']) ?>" class="cifra"><span><?= (int) $hoy['proximas'] ?></span>Próximas reservas</a></div>
  <?php endif; ?>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="tarjeta">
      <h2 class="subtitulo">Movimiento de hoy</h2>
      <?php if (!$llegadas && !$salidas): ?><p class="text-secondary mb-0">Hoy no hay llegadas ni salidas.</p><?php endif; ?>
      <?php foreach ([['Llegada', $llegadas, 'Hospedado', 'Check-in'], ['Salida', $salidas, 'Finalizada', 'Check-out']] as [$tipo, $lista, $estado, $boton]): ?>
        <?php foreach ($lista as $r): ?>
          <div class="movimiento">
            <span class="badge <?= $tipo === 'Llegada' ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= $tipo ?></span>
            <div class="flex-grow-1"><a href="<?= url('reservas/ver', ['id' => $r['id']]) ?>"><?= e($r['cliente']) ?></a>
              <div class="small text-secondary">Hab. <?= $r['numero'] ?> · <?= $r['huespedes'] ?> persona(s)</div></div>
            <?php if ($puedeAtender): ?><button class="btn btn-sm btn-outline-dark" data-estado="<?= $estado ?>" data-reserva="<?= $r['id'] ?>"><?= $boton ?></button><?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="tarjeta">
      <h2 class="subtitulo">Habitaciones hoy</h2>
      <div class="mapa">
        <?php foreach ($habitaciones as $h): ?>
          <div class="hab hab-<?= strtolower($h['situacion']) ?>" title="<?= e($h['tipo'] . ($h['huesped'] ? ' · ' . $h['huesped'] : '')) ?>">
            <strong><?= $h['numero'] ?></strong><small><?= $h['situacion'] ?></small>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($necesidades !== null): ?>
      <a href="<?= url('suministros/index') ?>" class="tarjeta d-block mt-3 text-decoration-none text-body">
        <i class="bi bi-box-seam"></i> <strong><?= $necesidades ?></strong> necesidad(es) de suministros abiertas
      </a>
    <?php endif; ?>
    <?php if ($usuarios !== null): ?>
      <div class="tarjeta mt-3 small">
        <?php foreach ([1 => 'Admins', 2 => 'Gerentes', 3 => 'Recepción', 4 => 'Clientes', 5 => 'Proveedores'] as $id => $nombre): ?>
          <span class="me-3"><?= $nombre ?>: <strong><?= $usuarios[$id] ?? 0 ?></strong></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
