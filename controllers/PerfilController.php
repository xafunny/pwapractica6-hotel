<?php
// Datos propios y cambio de contraseña

defined('APP') or exit;

class PerfilController extends Controller
{
    public function index(): void
    {
        $this->requiere();
        $this->render('perfil/index', ['titulo' => 'Mi perfil', 'u' => Usuario::find(Auth::id())]);
    }

    public function guardar(): void
    {
        $this->requiere();
        $this->soloPost();
        $d = ['nombre' => post('nombre'), 'telefono' => vacio_null(post('telefono'))];
        if (mb_strlen($d['nombre']) < 3 || ($d['telefono'] && !preg_match('/^\d{7,15}$/', $d['telefono']))) {
            flash('danger', 'Revisa el nombre y el teléfono.');
        } else {
            Usuario::update(Auth::id(), $d);
            Auth::actualizarNombre($d['nombre']);
            flash('success', 'Datos actualizados.');
        }
        redirect('perfil/index');
    }

    public function clave(): void
    {
        $this->requiere();
        $this->soloPost();
        $u = Usuario::find(Auth::id());
        $nueva = $_POST['nueva'] ?? '';
        if (!password_verify($_POST['actual'] ?? '', $u['contrasena'])) {
            flash('danger', 'La contraseña actual no es correcta.');
        } elseif (strlen($nueva) < 6 || $nueva !== ($_POST['nueva2'] ?? '')) {
            flash('danger', 'La nueva contraseña debe tener 6 caracteres o más y coincidir.');
        } else {
            Usuario::update(Auth::id(), ['contrasena' => password_hash($nueva, PASSWORD_DEFAULT)]);
            flash('success', 'Contraseña cambiada.');
        }
        redirect('perfil/index');
    }
}
