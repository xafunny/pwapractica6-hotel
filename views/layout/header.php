<?php
// Cabecera con el menú según el rol
defined('APP') or exit;

$ruta = get('r');
$yo = Auth::user();

$menus = [
    Auth::ADMIN         => [['inicio/index', 'Inicio'], ['reservas/index', 'Reservas'], ['reservas/buscar', 'Nueva reserva'], ['habitaciones/index', 'Habitaciones'],
                            ['clientes/index', 'Clientes'], ['suministros/index', 'Suministros'], ['usuarios/index', 'Usuarios']],
    Auth::GERENTE       => [['inicio/index', 'Inicio'], ['habitaciones/index', 'Habitaciones'], ['reservas/index', 'Reservas'], ['suministros/index', 'Suministros']],
    Auth::RECEPCIONISTA => [['inicio/index', 'Inicio'], ['reservas/buscar', 'Nueva reserva'], ['reservas/index', 'Reservas'], ['clientes/index', 'Clientes']],
    Auth::CLIENTE       => [['inicio/index', 'Inicio'], ['reservas/buscar', 'Reservar'], ['reservas/index', 'Mis reservas']],
    Auth::PROVEEDOR     => [['inicio/index', 'Inicio'], ['suministros/index', 'Necesidades del hotel']],
];
$menu = $yo ? $menus[$yo['id_rol']] : [];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <title><?= e($titulo) ?> · <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600&display=swap">
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
  <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
</head>
<body>

<?php if ($yo): ?>
<nav class="navbar navbar-expand-lg barra">
  <div class="container">
    <a class="navbar-brand marca" href="<?= url('inicio/index') ?>"><img src="<?= asset('img/logo.svg') ?>" alt="" width="30"> Hotel Mirador</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Menú">
      <i class="bi bi-list text-white fs-3"></i>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php // nombres con _ para no pisar las variables de la vista ($r, $tipo…)
        foreach ($menu as [$_ruta, $_texto]): ?>
          <li class="nav-item"><a class="nav-link <?= $ruta === $_ruta || ($_ruta !== 'reservas/buscar' && explode('/', $_ruta)[0] === explode('/', $ruta)[0] && $ruta !== 'reservas/buscar') ? 'active' : '' ?>" href="<?= url($_ruta) ?>"><?= $_texto ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="dropdown">
        <a href="#" class="d-flex align-items-center gap-2 text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="avatar"><?= e(iniciales($yo['nombre'])) ?></span>
          <span class="lh-sm small"><?= e($yo['nombre']) ?><br><span class="text-white-50"><?= e($yo['rol']) ?></span></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="<?= url('perfil/index') ?>"><i class="bi bi-person"></i> Mi perfil</a></li>
          <li><form method="post" action="<?= url('auth/salir') ?>"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>

<main class="<?= $yo ? 'container py-4' : '' ?>">
  <?php foreach (flash() as [$_tipo, $_msg]): ?>
    <div class="alert alert-<?= e($_tipo) ?> alert-dismissible fade show aviso" role="alert">
      <?= e($_msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endforeach; ?>
