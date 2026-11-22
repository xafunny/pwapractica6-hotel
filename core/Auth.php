<?php
// Sesión del usuario y roles del hotel

defined('APP') or exit;

class Auth
{
    public const ADMIN         = 1;
    public const GERENTE       = 2;
    public const RECEPCIONISTA = 3;
    public const CLIENTE       = 4;
    public const PROVEEDOR     = 5;

    public static function login(array $u): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'     => (int) $u['id'],
            'nombre' => $u['nombre'],
            'id_rol' => (int) $u['id_rol'],
            'rol'    => $u['rol'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function user(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['usuario']['id'] ?? null;
    }

    public static function rol(): ?int
    {
        return $_SESSION['usuario']['id_rol'] ?? null;
    }

    public static function es(int ...$roles): bool
    {
        return self::check() && in_array(self::rol(), $roles, true);
    }

    public static function actualizarNombre(string $nombre): void
    {
        $_SESSION['usuario']['nombre'] = $nombre;
    }
}
