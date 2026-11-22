<?php
// Detalle de una necesidad con las ofertas recibidas (gerente/admin)
defined('APP') or exit;
$abierta = $n['estado'] === 'Abierta';
?>
<a href="<?= url('suministros/index') ?>" class="small"><i class="bi bi-arrow-left"></i> Suministros</a>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-3">
  <div>
    <h1 class="titulo mb-0"><?= e($n['articulo']) ?></h1>
    <span class="text-secondary"><?= $n['categoria'] ?> · <?= $n['cantidad'] ?> <?= e($n['unidad']) ?> · ofertas hasta el <?= fecha($n['fecha_limite']) ?></span>
  </div>
  <div class="d-flex gap-2 align-items-center">
    <?= etiqueta($n['estado']) ?>
    <?php if ($abierta): ?>
      <form method="post" action="<?= url('suministros/cerrar') ?>" data-confirmar="¿Cerrar sin aceptar ninguna oferta?">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $n['id'] ?>"><button class="btn btn-sm btn-outline-secondary">Cerrar</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="tarjeta p-0">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Proveedor</th><th class="text-end">Precio unit.</th><th class="text-end">Total</th><th>Entrega</th><th>Estado</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($ofertas as $i => $o): ?>
        <tr class="<?= $i === 0 && $abierta ? 'table-warning' : '' ?>">
          <td><?= e($o['empresa'] ?: $o['proveedor']) ?><br><small class="text-secondary"><?= e($o['telefono']) ?><?= $o['comentario'] ? ' · ' . e($o['comentario']) : '' ?></small></td>
          <td class="text-end"><?= dinero($o['precio_unitario']) ?></td>
          <td class="text-end fw-semibold"><?= dinero($o['precio_unitario'] * $n['cantidad']) ?></td>
          <td><?= $o['dias_entrega'] ?> día(s)</td>
          <td><?= etiqueta($o['estado']) ?></td>
          <td class="text-end">
            <?php if ($abierta && $o['estado'] === 'Pendiente'): ?>
              <form method="post" action="<?= url('suministros/aceptar') ?>" data-confirmar="¿Aceptar esta oferta? Las demás quedarán rechazadas.">
                <?= csrf_field() ?><input type="hidden" name="id_oferta" value="<?= $o['id'] ?>"><input type="hidden" name="id_necesidad" value="<?= $n['id'] ?>">
                <button class="btn btn-sm btn-dorado">Aceptar</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$ofertas): ?><tr><td colspan="6" class="text-center text-secondary py-4">Todavía no llegan ofertas.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php if ($abierta && $ofertas): ?><p class="small text-secondary mt-2">La fila resaltada es la oferta más barata.</p><?php endif; ?>
