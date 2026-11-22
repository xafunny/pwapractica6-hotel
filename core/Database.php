<?php
// Conexión PDO a MySQL (una sola para toda la app)

defined('APP') or exit;

class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo) return self::$pdo;

        try {
            self::$pdo = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
            self::$pdo->exec("SET time_zone = '" . date('P') . "'");
        } catch (PDOException $e) {
            http_response_code(500);
            $info = DEBUG ? '<br><small>' . htmlspecialchars($e->getMessage()) . '</small>' : '';
            exit('<h3 style="font-family:sans-serif">No hay conexión con la base de datos. Revisa la configuración e importa hotel.sql.' . $info . '</h3>');
        }
        return self::$pdo;
    }
}
