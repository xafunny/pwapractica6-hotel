<?php
// Tabla habitaciones

defined('APP') or exit;

class Habitacion extends Model
{
    protected static string $tabla = 'habitaciones';

    public const TIPOS = ['Simple', 'Doble', 'Suite', 'Familiar'];

    // condición SQL: la habitación h no tiene reservas activas que se crucen con [?, ?)
    public const LIBRE = "NOT EXISTS (SELECT 1 FROM reservas r
                          WHERE r.id_habitacion = h.id AND r.estado IN ('Confirmada','Hospedado')
                            AND r.fecha_entrada < ? AND r.fecha_salida > ?)";

    // habitaciones que se pueden reservar entre dos fechas
    public static function disponibles(string $entrada, string $salida, int $huespedes, string $tipo = ''): array
    {
        $sql = "SELECT h.* FROM habitaciones h
                 WHERE h.estado = 'Disponible' AND h.capacidad >= ? AND " . self::LIBRE;
        $p = [$huespedes, $salida, $entrada];
        if ($tipo !== '') {
            $sql .= ' AND h.tipo = ?';
            $p[] = $tipo;
        }
        return self::query($sql . ' ORDER BY h.precio, h.numero', $p)->fetchAll();
    }

    // cómo está cada habitación hoy y quién la ocupa
    public static function estadoHoy(): array
    {
        $sql = "SELECT h.*, r.id AS reserva_id, r.estado AS reserva_estado, r.fecha_salida, u.nombre AS huesped
                  FROM habitaciones h
                  LEFT JOIN reservas r ON r.id_habitacion = h.id AND r.estado IN ('Confirmada','Hospedado')
                                      AND r.fecha_entrada <= CURDATE() AND r.fecha_salida > CURDATE()
                  LEFT JOIN usuarios u ON u.id = r.id_cliente
                 ORDER BY h.numero";
        $filas = self::query($sql)->fetchAll();
        foreach ($filas as &$f) {
            $f['situacion'] = $f['estado'] === 'Mantenimiento' ? 'Mantenimiento' : ($f['reserva_id'] ? 'Ocupada' : 'Disponible');
        }
        return $filas;
    }

    public static function numeroUsado(int $numero, int $menosId = 0): bool
    {
        return (bool) self::query('SELECT 1 FROM habitaciones WHERE numero = ? AND id <> ?', [$numero, $menosId])->fetch();
    }

    public static function tieneReservas(int $id): bool
    {
        return (bool) self::query('SELECT 1 FROM reservas WHERE id_habitacion = ? LIMIT 1', [$id])->fetch();
    }
}
