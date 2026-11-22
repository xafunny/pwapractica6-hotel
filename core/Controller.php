<?php
// Clase base de los controladores

defined('APP') or exit;

abstract class Controller
{
    // muestra una vista con el header y footer
    protected function render(string $vista, array $datos = []): void
    {
        extract($datos);
        $titulo ??= APP_NAME;
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/' . $vista . '.php';
        require __DIR__ . '/../views/layout/footer.php';
    }

    protected function json(array $datos, int $codigo = 200): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function esFetch(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    // corta si no hay sesión o si el rol no está entre los permitidos
    protected function requiere(int ...$roles): void
    {
        if (!Auth::check()) {
            if ($this->esFetch()) $this->json(['ok' => false, 'error' => 'Tu sesión terminó'], 401);
            redirect('auth/login');
        }
        if ($roles && !Auth::es(...$roles)) {
            if ($this->esFetch()) $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
            flash('warning', 'No tienes acceso a esa sección.');
            redirect('inicio/index');
        }
    }

    // para acciones que cambian datos: POST + token
    protected function soloPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) {
            if ($this->esFetch()) $this->json(['ok' => false, 'error' => 'Solicitud no válida'], 400);
            flash('danger', 'La solicitud no es válida, intenta de nuevo.');
            redirect('inicio/index');
        }
    }
}
