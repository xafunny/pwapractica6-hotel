<?php defined('APP') or exit; ?>
<div class="portada">
  <div class="portada-caja ancha">
    <h1 class="titulo">Crear cuenta</h1>
    <p class="text-secondary small">Regístrate como cliente para reservar habitaciones.</p>

    <?php if ($errores): ?><div class="alert alert-danger small text-start"><?= implode('<br>', array_map('e', $errores)) ?></div><?php endif; ?>

    <form method="post" action="<?= url('auth/registro') ?>" class="needs-validation text-start" novalidate>
      <?= csrf_field() ?>
      <div class="row g-2">
        <div class="col-12"><label class="form-label small">Nombre completo</label><input class="form-control" name="nombre" value="<?= e($d['nombre']) ?>" required minlength="3"></div>
        <div class="col-md-7"><label class="form-label small">Correo</label><input type="email" class="form-control" name="correo" value="<?= e($d['correo']) ?>" required></div>
        <div class="col-md-5"><label class="form-label small">Teléfono</label><input class="form-control" name="telefono" value="<?= e($d['telefono']) ?>" required pattern="\d{7,15}"></div>
        <div class="col-12"><label class="form-label small">Cédula o pasaporte</label><input class="form-control" name="documento" value="<?= e($d['documento']) ?>" required pattern="[A-Za-z0-9]{5,20}"></div>
        <div class="col-6"><label class="form-label small">Contraseña</label><input type="password" class="form-control" name="contrasena" id="clave" required minlength="6"></div>
        <div class="col-6"><label class="form-label small">Repetir</label><input type="password" class="form-control" name="contrasena2" required data-igual="clave"></div>
      </div>
      <button class="btn btn-dorado w-100 mt-4">Registrarme</button>
    </form>
    <p class="small mt-3 mb-0"><a href="<?= url('auth/login') ?>">Ya tengo cuenta</a></p>
  </div>
</div>
