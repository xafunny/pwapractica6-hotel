<?php
// Tabla usuarios (los 5 roles)

defined('APP') or exit;

class Usuario extends Model
{
    protected static string $tabla = 'usuarios';

    public static function porCorreo(string $correo): ?array
    {
        return self::query('SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.id_rol WHERE u.correo = ?', [$correo])->fetch() ?: null;
    }

    public static function correoUsado(string $correo, int $menosId = 0): bool
    {
        return (bool) self::query('SELECT 1 FROM usuarios WHERE correo = ? AND id <> ?', [$correo, $menosId])->fetch();
    }

    public static function roles(): array
    {
        return self::query('SELECT * FROM roles ORDER BY id')->fetchAll();
    }

    public static function listar(int $rol = 0, string $buscar = ''): array
    {
        $sql = 'SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.id_rol WHERE 1=1';
        $p = [];
        if ($rol) {
            $sql .= ' AND u.id_rol = ?';
            $p[] = $rol;
        }
        if ($buscar !== '') {
            $sql .= ' AND (u.nombre LIKE ? OR u.correo LIKE ? OR u.documento LIKE ?)';
            array_push($p, "%$buscar%", "%$buscar%", "%$buscar%");
        }
        return self::query($sql . ' ORDER BY u.id_rol, u.nombre', $p)->fetchAll();
    }

    // clientes con el número de reservas (para recepción)
    public static function clientes(string $buscar = ''): array
    {
        $sql = 'SELECT u.*, (SELECT COUNT(*) FROM reservas r WHERE r.id_cliente = u.id) AS reservas
                  FROM usuarios u WHERE u.id_rol = ?';
        $p = [Auth::CLIENTE];
        if ($buscar !== '') {
            $sql .= ' AND (u.nombre LIKE ? OR u.correo LIKE ? OR u.documento LIKE ? OR u.telefono LIKE ?)';
            array_push($p, ...array_fill(0, 4, "%$buscar%"));
        }
        return self::query($sql . ' ORDER BY u.nombre', $p)->fetchAll();
    }

    public static function porRol(): array
    {
        $filas = self::query('SELECT id_rol, COUNT(*) n FROM usuarios GROUP BY id_rol')->fetchAll();
        return array_column($filas, 'n', 'id_rol');
    }
}
