<?php
// Pantalla de inicio según el rol

defined('APP') or exit;

class InicioController extends Controller
{
    public function index(): void
    {
        $this->requiere();

        switch (Auth::rol()) {
            case Auth::CLIENTE:
                $this->render('inicio/cliente', ['titulo' => 'Inicio', 'reservas' => Reserva::deCliente(Auth::id())]);
                return;

            case Auth::PROVEEDOR:
                $this->render('inicio/proveedor', [
                    'titulo'      => 'Inicio',
                    'necesidades' => Necesidad::paraProveedor(Auth::id()),
                    'ofertas'     => Necesidad::misOfertas(Auth::id()),
                ]);
                return;

            default: // administrador, gerente y recepcionista
                $habitaciones = Habitacion::estadoHoy();
                $ocupadas = count(array_filter($habitaciones, fn($h) => $h['situacion'] === 'Ocupada'));
                $enServicio = count(array_filter($habitaciones, fn($h) => $h['situacion'] !== 'Mantenimiento'));
                $this->render('inicio/personal', [
                    'titulo'       => 'Inicio',
                    'hoy'          => Reserva::resumenHoy(),
                    'habitaciones' => $habitaciones,
                    'ocupacion'    => $enServicio ? round($ocupadas / $enServicio * 100) : 0,
                    'llegadas'     => Reserva::listar('llegadas'),
                    'salidas'      => Reserva::listar('salidas'),
                    'ingresos'     => Auth::es(Auth::ADMIN, Auth::GERENTE) ? Reserva::ingresosMes() : null,
                    'necesidades'  => Auth::es(Auth::ADMIN, Auth::GERENTE) ? Necesidad::abiertas() : null,
                    'usuarios'     => Auth::es(Auth::ADMIN) ? Usuario::porRol() : null,
                ]);
        }
    }
}
