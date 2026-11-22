<?php defined('APP') or exit; ?>
<h1 class="titulo">Mi perfil</h1>
<div class="row g-4">
  <div class="col-lg-6">
    <form method="post" action="<?= url('perfil/guardar') ?>" class="tarjeta needs-validation" novalidate>
      <?= csrf_field() ?>
      <h2 class="subtitulo">Mis datos</h2>
      <label class="form-label small">Nombre</label><input class="form-control mb-2" name="nombre" value="<?= e($u['nombre']) ?>" required minlength="3">
      <label class="form-label small">Correo</label><input class="form-control mb-2" value="<?= e($u['correo']) ?>" disabled>
      <label class="form-label small">Teléfono</label><input class="form-control mb-3" name="telefono" value="<?= e($u['telefono']) ?>" pattern="\d{7,15}">
      <button class="btn btn-dorado">Guardar</button>
    </form>
  </div>
  <div class="col-lg-6">
    <form method="post" action="<?= url('perfil/clave') ?>" class="tarjeta needs-validation" novalidate>
      <?= csrf_field() ?>
      <h2 class="subtitulo">Cambiar contraseña</h2>
      <input type="password" class="form-control mb-2" name="actual" placeholder="Actual" required aria-label="Contraseña actual">
      <input type="password" class="form-control mb-2" name="nueva" id="nueva" placeholder="Nueva (mínimo 6)" required minlength="6" aria-label="Nueva contraseña">
      <input type="password" class="form-control mb-3" name="nueva2" placeholder="Repetir" required data-igual="nueva" aria-label="Repetir contraseña">
      <button class="btn btn-outline-dark">Cambiar</button>
    </form>
  </div>
</div>
