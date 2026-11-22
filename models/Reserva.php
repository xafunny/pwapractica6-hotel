<?php
// Tabla reservas. Estados: Confirmada -> Hospedado (check-in) -> Finalizada (check-out); o Cancelada.

defined('APP') or exit;

class Reserva extends Model
{
    protected static string $tabla = 'reservas';

    private const SELECT = "SELECT r.*, h.numero, h.tipo, h.precio,
               u.nombre AS cliente, u.correo, u.telefono, u.documento,
               rp.nombre AS registro
          FROM reservas r
          JOIN habitaciones h ON h.id = r.id_habitacion
          JOIN usuarios u     ON u.id = r.id_cliente
          LEFT JOIN usuarios rp ON rp.id = r.registrada_por";

    /**
     * Crea la reserva si la habitación sigue libre. Bloquea la habitación (FOR UPDATE)
     * para que dos personas no la reserven a la vez. Devuelve [id, null] o [null, error].
     */
    public static function crear(int $cliente, int $habitacion, string $entrada, string $salida, int $huespedes, ?int $registro, ?string $notas): array
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $h = self::query('SELECT * FROM habitaciones WHERE id = ? FOR UPDATE', [$habitacion])->fetch();
            $error = match (true) {
                !$h => 'La habitación no existe.',
                $h['estado'] !== 'Disponible' => 'La habitación está en mantenimiento.',
                $huespedes > $h['capacidad'] => "La habitación {$h['numero']} es para máximo {$h['capacidad']} persona(s).",
                default => null,
            };
            if (!$error) {
                $ocupada = self::query("SELECT 1 FROM reservas WHERE id_habitacion = ? AND estado IN ('Confirmada','Hospedado')
                                          AND fecha_entrada < ? AND fecha_salida > ? LIMIT 1", [$habitacion, $salida, $entrada])->fetch();
                if ($ocupada) $error = "La habitación {$h['numero']} ya está reservada en esas fechas.";
            }
            if ($error) {
                $db->rollBack();
                return [null, $error];
            }

            $id = self::create([
                'id_cliente'     => $cliente,
                'id_habitacion'  => $habitacion,
                'registrada_por' => $registro,
                'fecha_entrada'  => $entrada,
                'fecha_salida'   => $salida,
                'huespedes'      => $huespedes,
                'total'          => round($h['precio'] * noches($entrada, $salida), 2),
                'notas'          => $notas,
            ]);
            $db->commit();
            return [$id, null];
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function detalle(int $id): ?array
    {
        return self::query(self::SELECT . ' WHERE r.id = ?', [$id])->fetch() ?: null;
    }

    public static function deCliente(int $cliente): array
    {
        return self::query(self::SELECT . ' WHERE r.id_cliente = ? ORDER BY r.fecha_entrada DESC', [$cliente])->fetchAll();
    }

    // filtros: '' activas, llegadas, salidas, todas o un estado
    public static function listar(string $filtro = '', string $buscar = ''): array
    {
        $where = match ($filtro) {
            ''         => "r.estado IN ('Confirmada','Hospedado')",
            'llegadas' => "r.estado = 'Confirmada' AND r.fecha_entrada = CURDATE()",
            'salidas'  => "r.estado = 'Hospedado' AND r.fecha_salida = CURDATE()",
            'todas'    => '1=1',
            default    => 'r.estado = ?',
        };
        $p = in_array($filtro, ['Confirmada', 'Hospedado', 'Finalizada', 'Cancelada'], true) ? [$filtro] : [];
        if ($buscar !== '') {
            $where .= ' AND (u.nombre LIKE ? OR u.documento LIKE ? OR h.numero = ? OR r.id = ?)';
            array_push($p, "%$buscar%", "%$buscar%", (int) $buscar, (int) $buscar);
        }
        $orden = in_array($filtro, ['', 'llegadas', 'salidas', 'Confirmada'], true) ? 'r.fecha_entrada' : 'r.fecha_entrada DESC';
        return self::query(self::SELECT . " WHERE $where ORDER BY $orden LIMIT 300", $p)->fetchAll();
    }

    public static function cambiarEstado(array $r, string $nuevo): ?string
    {
        $hoy = date('Y-m-d');
        $error = match ($nuevo) {
            'Hospedado'  => $r['estado'] !== 'Confirmada' ? 'Solo se hace check-in a reservas confirmadas.'
                          : ($r['fecha_entrada'] > $hoy ? 'Todavía no es la fecha de entrada.' : null),
            'Finalizada' => $r['estado'] !== 'Hospedado' ? 'Solo se hace check-out a huéspedes hospedados.' : null,
            'Cancelada'  => $r['estado'] !== 'Confirmada' ? 'Solo se cancelan reservas confirmadas.' : null,
            default      => 'Estado no válido.',
        };
        if (!$error) self::update((int) $r['id'], ['estado' => $nuevo]);
        return $error;
    }

    public static function resumenHoy(): array
    {
        return self::query("SELECT
                SUM(estado = 'Confirmada' AND fecha_entrada = CURDATE()) AS llegadas,
                SUM(estado = 'Hospedado'  AND fecha_salida  = CURDATE()) AS salidas,
                SUM(estado = 'Hospedado') AS hospedados,
                SUM(estado = 'Confirmada' AND fecha_entrada > CURDATE()) AS proximas
              FROM reservas")->fetch();
    }

    // dinero de las reservas que inician en el mes actual (sin canceladas)
    public static function ingresosMes(): float
    {
        return (float) self::query("SELECT COALESCE(SUM(total), 0) FROM reservas
                                     WHERE estado <> 'Cancelada' AND DATE_FORMAT(fecha_entrada, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")->fetchColumn();
    }
}
