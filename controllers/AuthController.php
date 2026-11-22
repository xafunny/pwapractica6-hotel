<?php
// Login, registro de clientes y salir

defined('APP') or exit;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) redirect('inicio/index');
        $error = '';
        $correo = mb_strtolower(post('correo'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fallos = $_SESSION['fallos'] ?? ['n' => 0, 't' => 0];
            $u = Usuario::porCorreo($correo);

            if (!csrf_ok()) {
                $error = 'La página expiró, vuelve a intentarlo.';
            } elseif ($fallos['n'] >= 5 && time() - $fallos['t'] < 60) {
                $error = 'Demasiados intentos, espera un minuto.';
            } elseif (!$u || !password_verify($_POST['contrasena'] ?? '', $u['contrasena'])) {
                $_SESSION['fallos'] = ['n' => $fallos['n'] + 1, 't' => time()];
                $error = 'Correo o contraseña incorrectos.';
            } elseif (!$u['activo']) {
                $error = 'Tu cuenta está desactivada.';
            } else {
                unset($_SESSION['fallos']);
                Auth::login($u);
                redirect('inicio/index');
            }
        }
        $this->render('auth/login', ['titulo' => 'Ingresar', 'error' => $error, 'correo' => $correo]);
    }

    // solo los clientes se registran solos; el resto lo crea el administrador
    public function registro(): void
    {
        if (Auth::check()) redirect('inicio/index');
        $errores = [];
        $d = ['nombre' => post('nombre'), 'correo' => mb_strtolower(post('correo')), 'telefono' => post('telefono'), 'documento' => post('documento')];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clave = $_POST['contrasena'] ?? '';
            if (!csrf_ok()) $errores[] = 'La página expiró, vuelve a intentarlo.';
            if (mb_strlen($d['nombre']) < 3) $errores[] = 'Escribe tu nombre completo.';
            if (!filter_var($d['correo'], FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
            elseif (Usuario::correoUsado($d['correo'])) $errores[] = 'Ese correo ya tiene una cuenta.';
            if (!preg_match('/^\d{7,15}$/', $d['telefono'])) $errores[] = 'El teléfono debe tener solo números.';
            if (!preg_match('/^[A-Za-z0-9]{5,20}$/', $d['documento'])) $errores[] = 'Escribe tu cédula o pasaporte.';
            if (strlen($clave) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
            if ($clave !== ($_POST['contrasena2'] ?? '')) $errores[] = 'Las contraseñas no coinciden.';

            if (!$errores) {
                Usuario::create($d + ['contrasena' => password_hash($clave, PASSWORD_DEFAULT), 'id_rol' => Auth::CLIENTE]);
                Auth::login(Usuario::porCorreo($d['correo']));
                flash('success', '¡Cuenta creada! Ya puedes buscar habitaciones.');
                redirect('inicio/index');
            }
        }
        $this->render('auth/registro', ['titulo' => 'Crear cuenta', 'errores' => $errores, 'd' => $d]);
    }

    public function salir(): void
    {
        $this->soloPost();
        Auth::logout();
        redirect('auth/login');
    }
}
