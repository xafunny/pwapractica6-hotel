<?php
// Reservas: buscar disponibilidad, reservar, check-in, check-out y cancelar
// Cliente: solo las suyas. Recepcionista: reserva para cualquier cliente. Gerente y admin: consultan todas.

defined('APP') or exit;

class ReservasController extends Controller
{
    public function index(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE, Auth::RECEPCIONISTA, Auth::CLIENTE);
        if (Auth::es(Auth::CLIENTE)) {
            $this->render('reservas/mias', ['titulo' => 'Mis reservas', 'reservas' => Reserva::deCliente(Auth::id())]);
            return;
        }
        $filtro = get('f');
        if (!in_array($filtro, ['', 'llegadas', 'salidas', 'todas', 'Confirmada', 'Hospedado', 'Finalizada', 'Cancelada'], true)) $filtro = '';
        $this->render('reservas/index', [
            'titulo'   => 'Reservas',
            'reservas' => Reserva::listar($filtro, get('q')),
            'filtro'   => $filtro,
            'q'        => get('q'),
        ]);
    }

    // búsqueda de habitaciones libres (cliente y recepcionista)
    public function buscar(): void
    {
        $this->requiere(Auth::RECEPCIONISTA, Auth::CLIENTE, Auth::ADMIN);
        $entrada = get('entrada') ?: date('Y-m-d');
        $salida = get('salida') ?: date('Y-m-d', strtotime($entrada . ' +1 day'));
        $huespedes = max(1, (int) (get('huespedes') ?: 1));
        $tipo = in_array(get('tipo'), Habitacion::TIPOS, true) ? get('tipo') : '';

        $error = $this->validarFechas($entrada, $salida);
        $this->render('reservas/buscar', [
            'titulo'       => 'Buscar habitación',
            'entrada'      => $entrada,
            'salida'       => $salida,
            'huespedes'    => $huespedes,
            'tipo'         => $tipo,
            'error'        => $error,
            'habitaciones' => $error ? [] : Habitacion::disponibles($entrada, $salida, $huespedes, $tipo),
            'clientes'     => Auth::es(Auth::CLIENTE) ? [] : Usuario::clientes(),
        ]);
    }

    public function crear(): void
    {
        $this->requiere(Auth::RECEPCIONISTA, Auth::CLIENTE, Auth::ADMIN);
        $this->soloPost();
        $entrada = post('entrada');
        $salida = post('salida');
        $huespedes = max(1, post_int('huespedes'));
        $volver = ['entrada' => $entrada, 'salida' => $salida, 'huespedes' => $huespedes];

        // el cliente reserva para sí mismo; recepción elige al cliente
        $cliente = Auth::es(Auth::CLIENTE) ? Auth::id() : post_int('id_cliente');
        $c = Usuario::find($cliente);
        $error = $this->validarFechas($entrada, $salida)
            ?? (!$c || (int) $c['id_rol'] !== Auth::CLIENTE ? 'Elige un cliente.' : null);
        if ($error) {
            flash('danger', $error);
            redirect('reservas/buscar', $volver);
        }

        $registro = Auth::es(Auth::CLIENTE) ? null : Auth::id();
        [$id, $error] = Reserva::crear($cliente, post_int('id_habitacion'), $entrada, $salida, $huespedes, $registro, vacio_null(mb_substr(post('notas'), 0, 255)));
        if ($error) {
            flash('danger', $error);
            redirect('reservas/buscar', $volver);
        }
        flash('success', "Reserva #$id confirmada.");
        redirect('reservas/ver', ['id' => $id]);
    }

    public function ver(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE, Auth::RECEPCIONISTA, Auth::CLIENTE);
        $r = $this->reserva((int) get('id'));
        $this->render('reservas/ver', ['titulo' => 'Reserva #' . $r['id'], 'r' => $r]);
    }

    // check-in, check-out o cancelar
    public function estado(): void
    {
        $this->requiere(Auth::ADMIN, Auth::RECEPCIONISTA, Auth::CLIENTE);
        $this->soloPost();
        $r = $this->reserva(post_int('id'));
        $nuevo = post('estado');

        // el cliente solo puede cancelar, y hasta un día antes de la entrada
        if (Auth::es(Auth::CLIENTE)) {
            if ($nuevo !== 'Cancelada') $this->json(['ok' => false, 'error' => 'La llegada y la salida las registra recepción.'], 403);
            if ($r['fecha_entrada'] <= date('Y-m-d')) $this->json(['ok' => false, 'error' => 'Para cancelar el mismo día comunícate con recepción.'], 403);
        }
        $error = Reserva::cambiarEstado($r, $nuevo);
        if ($error) $this->json(['ok' => false, 'error' => $error], 409);
        $this->json(['ok' => true, 'estado' => $nuevo]);
    }

    private function validarFechas(string $entrada, string $salida): ?string
    {
        return match (true) {
            !es_fecha($entrada) || !es_fecha($salida) => 'Las fechas no son válidas.',
            $entrada < date('Y-m-d') => 'La fecha de entrada no puede ser anterior a hoy.',
            $salida <= $entrada => 'La salida debe ser después de la entrada.',
            noches($entrada, $salida) > 30 => 'Máximo 30 noches por reserva.',
            default => null,
        };
    }

    // busca la reserva; el cliente solo ve las suyas
    private function reserva(int $id): array
    {
        $r = Reserva::detalle($id);
        if (!$r || (Auth::es(Auth::CLIENTE) && (int) $r['id_cliente'] !== Auth::id())) {
            if ($this->esFetch()) $this->json(['ok' => false, 'error' => 'Reserva no encontrada'], 404);
            flash('danger', 'Reserva no encontrada.');
            redirect('reservas/index');
        }
        return $r;
    }
}
