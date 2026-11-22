<?php
// Usuarios del sistema (solo administrador)

defined('APP') or exit;

class UsuariosController extends Controller
{
    public function index(): void
    {
        $this->requiere(Auth::ADMIN);
        $rol = (int) get('rol');
        $q = get('q');
        $this->render('usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => Usuario::listar($rol, $q),
            'roles'    => Usuario::roles(),
            'rol'      => $rol,
            'q'        => $q,
        ]);
    }

    public function guardar(): void
    {
        $this->requiere(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id');
        $d = [
            'nombre'    => post('nombre'),
            'correo'    => mb_strtolower(post('correo')),
            'id_rol'    => post_int('id_rol'),
            'telefono'  => vacio_null(post('telefono')),
            'documento' => vacio_null(post('documento')),
            'empresa'   => vacio_null(post('empresa')),
        ];
        $clave = $_POST['contrasena'] ?? '';

        $error = match (true) {
            mb_strlen($d['nombre']) < 3 => 'Escribe el nombre.',
            !filter_var($d['correo'], FILTER_VALIDATE_EMAIL) => 'Correo no válido.',
            Usuario::correoUsado($d['correo'], $id) => 'Ese correo ya está registrado.',
            $d['id_rol'] < 1 || $d['id_rol'] > 5 => 'Elige un rol.',
            $id === Auth::id() && $d['id_rol'] !== Auth::ADMIN => 'No puedes quitarte el rol de administrador.',
            !$id && strlen($clave) < 6, $clave !== '' && strlen($clave) < 6 => 'La contraseña debe tener al menos 6 caracteres.',
            default => null,
        };
        if ($error) {
            flash('danger', $error);
            redirect('usuarios/index');
        }
        if ($d['id_rol'] !== Auth::PROVEEDOR) $d['empresa'] = null;
        if ($clave !== '') $d['contrasena'] = password_hash($clave, PASSWORD_DEFAULT);

        if ($id) {
            Usuario::update($id, $d);
            if ($id === Auth::id()) Auth::actualizarNombre($d['nombre']);
        } else {
            Usuario::create($d);
        }
        flash('success', 'Usuario guardado.');
        redirect('usuarios/index');
    }

    public function estado(): void
    {
        $this->requiere(Auth::ADMIN);
        $this->soloPost();
        $u = Usuario::find(post_int('id'));
        if (!$u || (int) $u['id'] === Auth::id()) $this->json(['ok' => false, 'error' => 'No se puede desactivar este usuario.'], 400);
        Usuario::update((int) $u['id'], ['activo' => $u['activo'] ? 0 : 1]);
        $this->json(['ok' => true, 'activo' => $u['activo'] ? 0 : 1]);
    }

    public function eliminar(): void
    {
        $this->requiere(Auth::ADMIN);
        $this->soloPost();
        $id = post_int('id');
        if ($id === Auth::id()) {
            flash('danger', 'No puedes eliminar tu propia cuenta.');
        } else {
            try {
                Usuario::delete($id);
                flash('success', 'Usuario eliminado.');
            } catch (PDOException) {
                // un proveedor con suministros entregados no se puede borrar
                flash('warning', 'Este usuario tiene registros asociados; mejor desactívalo.');
            }
        }
        redirect('usuarios/index');
    }
}
