<?php
// Clase base de los modelos: CRUD sencillo con consultas preparadas

defined('APP') or exit;

abstract class Model
{
    protected static string $tabla;

    protected static function db(): PDO
    {
        return Database::get();
    }

    protected static function query(string $sql, array $params = []): PDOStatement
    {
        $st = self::db()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    public static function find(int $id): ?array
    {
        return self::query('SELECT * FROM ' . static::$tabla . ' WHERE id = ?', [$id])->fetch() ?: null;
    }

    public static function all(string $orden = 'id'): array
    {
        return self::query('SELECT * FROM ' . static::$tabla . ' ORDER BY ' . $orden)->fetchAll();
    }

    public static function create(array $datos): int
    {
        $campos = array_keys($datos);
        $marcas = implode(', ', array_fill(0, count($campos), '?'));
        self::query('INSERT INTO ' . static::$tabla . ' (' . implode(', ', $campos) . ") VALUES ($marcas)", array_values($datos));
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $datos): void
    {
        $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($datos)));
        self::query('UPDATE ' . static::$tabla . " SET $set WHERE id = ?", [...array_values($datos), $id]);
    }

    public static function delete(int $id): void
    {
        self::query('DELETE FROM ' . static::$tabla . ' WHERE id = ?', [$id]);
    }
}
