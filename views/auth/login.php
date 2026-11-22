<?php defined('APP') or exit; ?>
<div class="portada">
  <div class="portada-caja">
    <img src="<?= asset('img/logo.svg') ?>" alt="" width="56">
    <h1 class="titulo mt-2 mb-0">Hotel Mirador</h1>
    <p class="text-secondary mb-4">Ingresa para continuar</p>

    <?php if ($error): ?><div class="alert alert-danger py-2 small"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="<?= url('auth/login') ?>" class="needs-validation text-start" novalidate>
      <?= csrf_field() ?>
      <label class="form-label small" for="correo">Correo</label>
      <input type="email" class="form-control mb-3" id="correo" name="correo" value="<?= e($correo) ?>" required autofocus>
      <label class="form-label small" for="contrasena">Contraseña</label>
      <input type="password" class="form-control mb-4" id="contrasena" name="contrasena" required>
      <button class="btn btn-dorado w-100 py-2">Ingresar</button>
    </form>
    <p class="small mt-3 mb-0">¿Quieres reservar? <a href="<?= url('auth/registro') ?>">Crea tu cuenta</a></p>
  </div>
</div>
