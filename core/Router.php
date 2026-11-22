<?php
// Lee ?r=controlador/accion y llama al método del controlador

defined('APP') or exit;

class Router
{
    public static function dispatch(string $ruta): void
    {
        if ($ruta === '') {
            $ruta = Auth::check() ? 'inicio/index' : 'auth/login';
        }

        if (!preg_match('#^([a-z]+)/([a-z]+)$#', $ruta, $m)) self::error404();

        $clase = ucfirst($m[1]) . 'Controller';
        $accion = $m[2];
        $archivo = __DIR__ . '/../controllers/' . $clase . '.php';
        if (!is_file($archivo)) self::error404();
        require_once $archivo;

        // solo métodos públicos del propio controlador
        if (!method_exists($clase, $accion)) self::error404();
        $metodo = new ReflectionMethod($clase, $accion);
        if (!$metodo->isPublic() || $metodo->getDeclaringClass()->getName() !== $clase) self::error404();

        (new $clase())->$accion();
    }

    private static function error404(): never
    {
        http_response_code(404);
        $titulo = 'No encontrado';
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/errores/404.php';
        require __DIR__ . '/../views/layout/footer.php';
        exit;
    }
}
