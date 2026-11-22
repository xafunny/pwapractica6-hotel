<?php
// Datos de los clientes (recepcionista y administrador)

defined('APP') or exit;

class ClientesController extends Controller
{
    public function index(): void
    {
        $this->requiere(Auth::ADMIN, Auth::RECEPCIONISTA);
        $this->render('clientes/index', ['titulo' => 'Clientes', 'clientes' => Usuario::clientes(get('q')), 'q' => get('q')]);
    }

    public function ver(): void
    {
        $this->requiere(Auth::ADMIN, Auth::RECEPCIONISTA);
        $c = $this->cliente((int) get('id'));
        $this->render('clientes/ver', ['titulo' => $c['nombre'], 'c' => $c, 'reservas' => Reserva::deCliente((int) $c['id'])]);
    }

    public function guardar(): void
    {
        $this->requiere(Auth::ADMIN, Auth::RECEPCIONISTA);
        $this->soloPost();
        $id = post_int('id');
        if ($id) $this->cliente($id);
        $d = [
            'nombre'    => post('nombre'),
            'correo'    => mb_strtolower(post('correo')),
            'telefono'  => post('telefono'),
            'documento' => post('documento'),
        ];
        $error = match (true) {
            mb_strlen($d['nombre']) < 3 => 'Escribe el nombre completo.',
            !filter_var($d['correo'], FILTER_VALIDATE_EMAIL) => 'Correo no válido.',
            Usuario::correoUsado($d['correo'], $id) => 'Ese correo ya está registrado.',
            !preg_match('/^\d{7,15}$/', $d['telefono']) => 'Teléfono no válido.',
            !preg_match('/^[A-Za-z0-9]{5,20}$/', $d['documento']) => 'Cédula o pasaporte no válido.',
            default => null,
        };
        if ($error) {
            flash('danger', $error);
            redirect('clientes/index');
        }
        if ($id) {
            Usuario::update($id, $d);
        } else {
            // el cliente que crea recepción entra con su número de documento como clave inicial
            $id = Usuario::create($d + ['id_rol' => Auth::CLIENTE, 'contrasena' => password_hash($d['documento'], PASSWORD_DEFAULT)]);
            flash('info', 'La clave inicial del cliente es su número de documento.');
        }
        flash('success', 'Cliente guardado.');
        // si venía desde la búsqueda de habitaciones, vuelve allá
        if (post('volver') === 'buscar') redirect('reservas/buscar', ['entrada' => post('entrada'), 'salida' => post('salida'), 'huespedes' => post('huespedes'), 'cliente' => $id]);
        redirect('clientes/index');
    }

    private function cliente(int $id): array
    {
        $c = Usuario::find($id);
        if (!$c || (int) $c['id_rol'] !== Auth::CLIENTE) {
            flash('danger', 'Cliente no encontrado.');
            redirect('clientes/index');
        }
        return $c;
    }
}
