<?php
// Proveedor: necesidades abiertas y sus ofertas
defined('APP') or exit;
$js = ['proveedor.js'];
?>
<h1 class="titulo">Necesidades del hotel</h1>
<p class="text-secondary">Envía tu precio por unidad. Puedes cambiar tu oferta mientras la necesidad siga abierta.</p>

<div class="row g-3 mb-4">
  <?php foreach ($necesidades as $n): ?>
    <div class="col-md-6 col-xl-4">
      <div class="tarjeta h-100">
        <div class="d-flex justify-content-between"><strong><?= e($n['articulo']) ?></strong><span class="badge text-bg-light"><?= $n['categoria'] ?></span></div>
        <p class="small text-secondary mb-2"><?= $n['cantidad'] ?> <?= e($n['unidad']) ?> · hasta el <?= fecha($n['fecha_limite']) ?></p>
        <?php if ($n['mi_oferta']): ?>
          <p class="small mb-2">Tu oferta: <strong><?= dinero($n['precio_unitario']) ?></strong> c/u · <?= $n['dias_entrega'] ?> día(s) <?= etiqueta($n['estado_oferta']) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= url('suministros/ofertar') ?>" class="needs-validation oferta" novalidate data-cantidad="<?= $n['cantidad'] ?>">
          <?= csrf_field() ?><input type="hidden" name="id_necesidad" value="<?= $n['id'] ?>">
          <div class="row g-1">
            <div class="col-6"><input type="number" class="form-control form-control-sm" name="precio_unitario" placeholder="$ c/u" min="0.01" step="0.01" required value="<?= $n['precio_unitario'] ?>" aria-label="Precio por unidad"></div>
            <div class="col-6"><input type="number" class="form-control form-control-sm" name="dias_entrega" placeholder="Días" min="1" max="60" required value="<?= $n['dias_entrega'] ?>" aria-label="Días de entrega"></div>
            <div class="col-12"><input class="form-control form-control-sm" name="comentario" placeholder="Comentario (opcional)" maxlength="255" value="<?= e($n['comentario']) ?>" aria-label="Comentario"></div>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-2">
            <small>Total: <strong data-total>$0.00</strong></small>
            <button class="btn btn-sm btn-dorado"><?= $n['mi_oferta'] ? 'Actualizar' : 'Ofertar' ?></button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$necesidades): ?><div class="col-12"><div class="tarjeta text-secondary text-center">Por ahora el hotel no necesita nada.</div></div><?php endif; ?>
</div>

<h2 class="subtitulo">Mis ofertas</h2>
<div class="tarjeta p-0">
  <?php foreach ($ofertas as $o): ?>
    <div class="fila"><span><?= e($o['articulo']) ?> <small class="text-secondary">× <?= $o['cantidad'] ?> <?= e($o['unidad']) ?></small><br><small class="text-secondary"><?= dinero($o['precio_unitario']) ?> c/u · <?= fecha($o['creado_en']) ?></small></span>
      <span class="text-end"><?= etiqueta($o['estado']) ?><br><strong><?= dinero($o['precio_unitario'] * $o['cantidad']) ?></strong></span></div>
  <?php endforeach; ?>
  <?php if (!$ofertas): ?><p class="text-center text-secondary py-4 mb-0">Aún no has enviado ofertas.</p><?php endif; ?>
</div>
