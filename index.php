<?php
// Punto de entrada: todas las páginas pasan por aquí (index.php?r=controlador/accion)

define('APP', true);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

require __DIR__ . '/config/config.php';
ini_set('display_errors', DEBUG ? '1' : '0');
error_reporting(E_ALL);

require __DIR__ . '/core/helpers.php';
require __DIR__ . '/core/Database.php';
require __DIR__ . '/core/Auth.php';
require __DIR__ . '/core/Model.php';
require __DIR__ . '/core/Controller.php';
require __DIR__ . '/core/Router.php';

foreach (glob(__DIR__ . '/models/*.php') as $modelo) {
    require_once $modelo;
}

Router::dispatch(is_string($_GET['r'] ?? null) ? $_GET['r'] : '');
