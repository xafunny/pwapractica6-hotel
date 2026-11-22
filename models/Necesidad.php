<?php
// Necesidades del hotel, ofertas de los proveedores y suministros comprados

defined('APP') or exit;

class Necesidad extends Model
{
    protected static string $tabla = 'necesidades';

    public const CATEGORIAS = ['Alimentos', 'Limpieza', 'Lencería', 'Amenidades', 'Mantenimiento'];

    // listado para gerente/admin con cuántas ofertas tiene cada una
    public static function listar(string $estado = ''): array
    {
        $sql = "SELECT n.*, (SELECT COUNT(*) FROM ofertas o WHERE o.id_necesidad = n.id) AS ofertas,
                       (SELECT MIN(o.precio_unitario) FROM ofertas o WHERE o.id_necesidad = n.id AND o.estado = 'Pendiente') AS mejor
                  FROM necesidades n";
        $p = [];
        if ($estado !== '') {
            $sql .= ' WHERE n.estado = ?';
            $p[] = $estado;
        }
        return self::query($sql . " ORDER BY FIELD(n.estado, 'Abierta', 'Adjudicada', 'Cerrada'), n.fecha_limite", $p)->fetchAll();
    }

    // lo que ve el proveedor: necesidades abiertas y su propia oferta si ya ofertó
    public static function paraProveedor(int $proveedor): array
    {
        return self::query("SELECT n.*, o.id AS mi_oferta, o.precio_unitario, o.dias_entrega, o.comentario, o.estado AS estado_oferta
                              FROM necesidades n
                              LEFT JOIN ofertas o ON o.id_necesidad = n.id AND o.id_proveedor = ?
                             WHERE n.estado = 'Abierta' AND n.fecha_limite >= CURDATE()
                             ORDER BY n.fecha_limite", [$proveedor])->fetchAll();
    }

    public static function misOfertas(int $proveedor): array
    {
        return self::query("SELECT o.*, n.articulo, n.cantidad, n.unidad, n.estado AS estado_necesidad
                              FROM ofertas o JOIN necesidades n ON n.id = o.id_necesidad
                             WHERE o.id_proveedor = ? ORDER BY o.creado_en DESC", [$proveedor])->fetchAll();
    }

    public static function ofertas(int $necesidad): array
    {
        return self::query("SELECT o.*, u.nombre AS proveedor, u.empresa, u.telefono
                              FROM ofertas o JOIN usuarios u ON u.id = o.id_proveedor
                             WHERE o.id_necesidad = ? ORDER BY o.precio_unitario", [$necesidad])->fetchAll();
    }

    // crea o actualiza la oferta del proveedor (solo mientras siga pendiente)
    public static function ofertar(int $necesidad, int $proveedor, float $precio, int $dias, ?string $comentario): void
    {
        self::query("INSERT INTO ofertas (id_necesidad, id_proveedor, precio_unitario, dias_entrega, comentario)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE precio_unitario = VALUES(precio_unitario), dias_entrega = VALUES(dias_entrega),
                                             comentario = VALUES(comentario), creado_en = NOW()",
            [$necesidad, $proveedor, $precio, $dias, $comentario]);
    }

    // aceptar una oferta: se rechazan las demás y se registra el suministro
    public static function aceptarOferta(int $oferta): ?string
    {
        $db = self::db();
        $db->beginTransaction();
        try {
            $o = self::query('SELECT o.*, n.articulo, n.cantidad, n.estado AS estado_n FROM ofertas o
                               JOIN necesidades n ON n.id = o.id_necesidad WHERE o.id = ? FOR UPDATE', [$oferta])->fetch();
            if (!$o || $o['estado_n'] !== 'Abierta' || $o['estado'] !== 'Pendiente') {
                $db->rollBack();
                return 'Esta oferta ya no se puede aceptar.';
            }
            self::query("UPDATE ofertas SET estado = IF(id = ?, 'Aceptada', 'Rechazada') WHERE id_necesidad = ?", [$oferta, $o['id_necesidad']]);
            self::query("UPDATE necesidades SET estado = 'Adjudicada' WHERE id = ?", [$o['id_necesidad']]);
            self::query('INSERT INTO suministros (nombre, cantidad, id_proveedor, id_oferta, costo, fecha) VALUES (?, ?, ?, ?, ?, CURDATE())',
                [$o['articulo'], $o['cantidad'], $o['id_proveedor'], $oferta, round($o['precio_unitario'] * $o['cantidad'], 2)]);
            $db->commit();
            return null;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function suministros(): array
    {
        return self::query('SELECT s.*, u.nombre AS proveedor, u.empresa FROM suministros s
                              JOIN usuarios u ON u.id = s.id_proveedor ORDER BY s.fecha DESC, s.id DESC')->fetchAll();
    }

    public static function abiertas(): int
    {
        return (int) self::query("SELECT COUNT(*) FROM necesidades WHERE estado = 'Abierta'")->fetchColumn();
    }
}
