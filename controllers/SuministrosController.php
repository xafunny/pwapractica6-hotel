<?php
// Necesidades del hotel y ofertas de proveedores
// Gerente/admin: publican necesidades y aceptan ofertas. Proveedor: ve lo que falta y oferta.

defined('APP') or exit;

class SuministrosController extends Controller
{
    public function index(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE, Auth::PROVEEDOR);
        if (Auth::es(Auth::PROVEEDOR)) {
            $this->render('suministros/proveedor', [
                'titulo'      => 'Necesidades del hotel',
                'necesidades' => Necesidad::paraProveedor(Auth::id()),
                'ofertas'     => Necesidad::misOfertas(Auth::id()),
            ]);
            return;
        }
        $estado = in_array(get('estado'), ['Abierta', 'Adjudicada', 'Cerrada'], true) ? get('estado') : '';
        $this->render('suministros/index', [
            'titulo'      => 'Suministros',
            'necesidades' => Necesidad::listar($estado),
            'suministros' => Necesidad::suministros(),
            'estado'      => $estado,
        ]);
    }

    public function ver(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $n = Necesidad::find((int) get('id'));
        if (!$n) redirect('suministros/index');
        $this->render('suministros/ver', ['titulo' => $n['articulo'], 'n' => $n, 'ofertas' => Necesidad::ofertas((int) $n['id'])]);
    }

    public function guardar(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $this->soloPost();
        $d = [
            'articulo'     => post('articulo'),
            'categoria'    => post('categoria'),
            'cantidad'     => post_int('cantidad'),
            'unidad'       => post('unidad') ?: 'unidades',
            'fecha_limite' => post('fecha_limite'),
        ];
        if ($d['articulo'] === '' || !in_array($d['categoria'], Necesidad::CATEGORIAS, true) || $d['cantidad'] < 1
            || !es_fecha($d['fecha_limite']) || $d['fecha_limite'] < date('Y-m-d')) {
            flash('danger', 'Revisa el artículo, la categoría, la cantidad y la fecha límite.');
        } else {
            Necesidad::create($d + ['creado_por' => Auth::id()]);
            flash('success', 'Necesidad publicada. Los proveedores ya pueden ofertar.');
        }
        redirect('suministros/index');
    }

    public function ofertar(): void
    {
        $this->requiere(Auth::PROVEEDOR);
        $this->soloPost();
        $n = Necesidad::find(post_int('id_necesidad'));
        $precio = post_num('precio_unitario');
        $dias = post_int('dias_entrega');
        $error = match (true) {
            !$n || $n['estado'] !== 'Abierta' || $n['fecha_limite'] < date('Y-m-d') => 'Esta necesidad ya no recibe ofertas.',
            !$precio || $precio <= 0 => 'Escribe un precio válido.',
            $dias < 1 || $dias > 60 => 'Los días de entrega deben estar entre 1 y 60.',
            default => null,
        };
        if ($error) {
            flash('danger', $error);
        } else {
            Necesidad::ofertar((int) $n['id'], Auth::id(), $precio, $dias, vacio_null(post('comentario')));
            flash('success', 'Oferta enviada por ' . dinero($precio * $n['cantidad']) . '.');
        }
        redirect('suministros/index');
    }

    public function aceptar(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $this->soloPost();
        $error = Necesidad::aceptarOferta(post_int('id_oferta'));
        flash($error ? 'danger' : 'success', $error ?? 'Oferta aceptada y suministro registrado.');
        redirect('suministros/ver', ['id' => post_int('id_necesidad')]);
    }

    public function cerrar(): void
    {
        $this->requiere(Auth::ADMIN, Auth::GERENTE);
        $this->soloPost();
        $n = Necesidad::find(post_int('id'));
        if ($n && $n['estado'] === 'Abierta') {
            Necesidad::update((int) $n['id'], ['estado' => 'Cerrada']);
            flash('success', 'Necesidad cerrada sin adjudicar.');
        }
        redirect('suministros/index');
    }
}
