<?php
// Funciones de uso general

defined('APP') or exit;

// color de cada estado para las etiquetas
const COLORES = [
    'Confirmada' => 'primary', 'Hospedado' => 'success', 'Finalizada' => 'secondary', 'Cancelada' => 'danger',
    'Disponible' => 'success', 'Mantenimiento' => 'warning', 'Ocupada' => 'danger',
    'Abierta' => 'info', 'Adjudicada' => 'success', 'Cerrada' => 'secondary',
    'Pendiente' => 'warning', 'Aceptada' => 'success', 'Rechazada' => 'secondary',
];

function e($v): string
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $ruta, array $params = []): string
{
    return 'index.php?' . http_build_query(['r' => $ruta] + $params);
}

function asset(string $ruta): string
{
    $f = __DIR__ . '/../assets/' . $ruta;
    return 'assets/' . $ruta . '?v=' . (is_file($f) ? filemtime($f) : 1);
}

function redirect(string $ruta, array $params = []): never
{
    header('Location: ' . url($ruta, $params));
    exit;
}

// guarda un mensaje (2 parámetros) o los devuelve y borra (sin parámetros)
function flash(?string $tipo = null, ?string $texto = null): array
{
    if ($tipo) {
        $_SESSION['flash'][] = [$tipo, $texto];
        return [];
    }
    $m = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $m;
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_ok(): bool
{
    $t = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($t) && hash_equals(csrf_token(), $t);
}

function post(string $k): string
{
    return is_string($_POST[$k] ?? null) ? trim($_POST[$k]) : '';
}

function post_int(string $k): int
{
    return (int) post($k);
}

function post_num(string $k): ?float
{
    $v = str_replace(',', '.', post($k));
    return is_numeric($v) ? round((float) $v, 2) : null;
}

function get(string $k): string
{
    return is_string($_GET[$k] ?? null) ? trim($_GET[$k]) : '';
}

function vacio_null(string $v): ?string
{
    return $v === '' ? null : $v;
}

function dinero($n): string
{
    return '$' . number_format((float) $n, 2);
}

function fecha(?string $f, bool $hora = false): string
{
    return $f ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($f)) : '';
}

// fecha corta tipo "lun 23/11"
function fecha_corta(string $f): string
{
    $dias = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
    $t = strtotime($f);
    return $dias[(int) date('w', $t)] . ' ' . date('d/m', $t);
}

function noches(string $entrada, string $salida): int
{
    return (int) ((strtotime($salida) - strtotime($entrada)) / 86400);
}

function es_fecha(string $f): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $f);
    return $d && $d->format('Y-m-d') === $f;
}

function etiqueta(string $estado): string
{
    $c = COLORES[$estado] ?? 'secondary';
    return "<span class=\"badge text-bg-$c\">" . e($estado) . '</span>';
}

function iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre));
    return mb_strtoupper(mb_substr($partes[0], 0, 1) . mb_substr($partes[1] ?? '', 0, 1));
}
