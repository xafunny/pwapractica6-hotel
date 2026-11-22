<?php
// Configuración general. En el hosting los datos van en config.local.php (no se sube a GitHub).

defined('APP') or exit;

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

defined('DB_HOST') or define('DB_HOST', 'localhost');
defined('DB_PORT') or define('DB_PORT', 3306);
defined('DB_NAME') or define('DB_NAME', 'hotel');
defined('DB_USER') or define('DB_USER', 'root');
defined('DB_PASS') or define('DB_PASS', '');
defined('DEBUG')   or define('DEBUG', true);

define('APP_NAME', 'Hotel Mirador');
date_default_timezone_set('America/Guayaquil');
