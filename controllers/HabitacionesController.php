<?php
// Habitaciones y precios (gerente y administrador)

defined('APP') or exit;

class HabitacionesController extends Controller
{
    public function index(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $this->render('habitaciones/index', ['titulo' => 'Habitaciones', 'habitaciones' => Habitacion::estadoHoy()]);
    }

    public function guardar(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $this->soloPost();
        $id = post_int('id');
        $d = [
            'numero'      => post_int('numero'),
            'tipo'        => post('tipo'),
            'capacidad'   => post_int('capacidad'),
            'precio'      => post_num('precio'),
            'descripcion' => vacio_null(post('descripcion')),
        ];
        $error = match (true) {
            $d['numero'] < 1 => 'Escribe el número de habitación.',
            Habitacion::numeroUsado($d['numero'], $id) => "Ya existe la habitación {$d['numero']}.",
            !in_array($d['tipo'], Habitacion::TIPOS, true) => 'Elige el tipo.',
            $d['capacidad'] < 1 || $d['capacidad'] > 10 => 'La capacidad debe estar entre 1 y 10.',
            !$d['precio'] || $d['precio'] <= 0 => 'El precio por noche debe ser mayor a 0.',
            default => null,
        };
        if ($error) {
            flash('danger', $error);
        } else {
            $id ? Habitacion::update($id, $d) : Habitacion::create($d);
            flash('success', "Habitación {$d['numero']} guardada.");
        }
        redirect('habitaciones/index');
    }

    // poner o quitar de mantenimiento
    public function estado(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $this->soloPost();
        $h = Habitacion::find(post_int('id'));
        if (!$h) $this->json(['ok' => false, 'error' => 'No existe'], 404);
        $nuevo = $h['estado'] === 'Disponible' ? 'Mantenimiento' : 'Disponible';
        Habitacion::update((int) $h['id'], ['estado' => $nuevo]);
        $this->json(['ok' => true, 'estado' => $nuevo]);
    }

    public function eliminar(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $this->soloPost();
        $id = post_int('id');
        if (Habitacion::tieneReservas($id)) {
            flash('warning', 'La habitación tiene reservas registradas; ponla en mantenimiento en lugar de borrarla.');
        } else {
            Habitacion::delete($id);
            flash('success', 'Habitación eliminada.');
        }
        redirect('habitaciones/index');
    }
}
