<?php
// Gerente/admin: necesidades publicadas y suministros comprados
defined('APP') or exit;
$filtros = ['' => 'Todas', 'Abierta' => 'Abiertas', 'Adjudicada' => 'Adjudicadas', 'Cerrada' => 'Cerradas'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="titulo mb-0">Suministros</h1>
  <button class="btn btn-dorado" data-bs-toggle="modal" data-bs-target="#modalNecesidad"><i class="bi bi-megaphone"></i> Publicar necesidad</button>
</div>

<div class="row g-4">
  <div class="col-xl-7">
    <h2 class="subtitulo">Necesidades</h2>
    <ul class="nav nav-underline mb-2">
      <?php foreach ($filtros as $k => $txt): ?><li class="nav-item"><a class="nav-link <?= $estado === $k ? 'active' : '' ?>" href="<?= url('suministros/index', array_filter(['estado' => $k])) ?>"><?= $txt ?></a></li><?php endforeach; ?>
    </ul>
    <div class="tarjeta p-0">
      <?php foreach ($necesidades as $n): $vencida = $n['estado'] === 'Abierta' && $n['fecha_limite'] < date('Y-m-d'); ?>
        <a href="<?= url('suministros/ver', ['id' => $n['id']]) ?>" class="fila">
          <span><strong><?= e($n['articulo']) ?></strong> <small class="text-secondary">· <?= $n['categoria'] ?></small><br>
            <small class="text-secondary"><?= $n['cantidad'] ?> <?= e($n['unidad']) ?> · hasta el <?= fecha($n['fecha_limite']) ?><?= $vencida ? ' <span class="text-danger">(vencida)</span>' : '' ?></small></span>
          <span class="text-end"><?= etiqueta($n['estado']) ?><br><small class="text-secondary"><?= $n['ofertas'] ?> oferta(s)<?= $n['mejor'] ? ' · desde ' . dinero($n['mejor']) : '' ?></small></span>
        </a>
      <?php endforeach; ?>
      <?php if (!$necesidades): ?><p class="text-center text-secondary py-4 mb-0">No hay necesidades.</p><?php endif; ?>
    </div>
  </div>
  <div class="col-xl-5">
    <h2 class="subtitulo">Comprado</h2>
    <div class="tarjeta p-0">
      <?php foreach ($suministros as $s): ?>
        <div class="fila"><span><?= e($s['nombre']) ?> <small class="text-secondary">× <?= $s['cantidad'] ?></small><br><small class="text-secondary"><?= e($s['empresa'] ?: $s['proveedor']) ?> · <?= fecha($s['fecha']) ?></small></span><strong><?= dinero($s['costo']) ?></strong></div>
      <?php endforeach; ?>
      <?php if (!$suministros): ?><p class="text-center text-secondary py-4 mb-0">Aún no se ha comprado nada.</p><?php endif; ?>
    </div>
  </div>
</div>

<div class="modal fade" id="modalNecesidad" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" method="post" action="<?= url('suministros/guardar') ?>" novalidate>
      <div class="modal-header"><h2 class="modal-title h5">Publicar necesidad</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
      <div class="modal-body row g-2">
        <?= csrf_field() ?>
        <div class="col-12"><label class="form-label small">¿Qué se necesita?</label><input class="form-control" name="articulo" required maxlength="80" placeholder="Ej.: sábanas de 2 plazas"></div>
        <div class="col-6"><label class="form-label small">Categoría</label><select class="form-select" name="categoria"><?php foreach (Necesidad::CATEGORIAS as $c): ?><option><?= $c ?></option><?php endforeach; ?></select></div>
        <div class="col-3"><label class="form-label small">Cantidad</label><input type="number" class="form-control" name="cantidad" required min="1"></div>
        <div class="col-3"><label class="form-label small">Unidad</label><input class="form-control" name="unidad" value="unidades" maxlength="20"></div>
        <div class="col-6"><label class="form-label small">Recibir ofertas hasta</label><input type="date" class="form-control" name="fecha_limite" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+7 day')) ?>"></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-dorado">Publicar</button></div>
    </form>
  </div>
</div>
