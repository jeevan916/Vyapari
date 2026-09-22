<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewPath = BASE_PATH . '/resources/views/' . $view . '.php';
        require BASE_PATH . '/resources/views/layouts/app.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $base = $base === '/public' ? '' : preg_replace('#/public$#', '', $base);
    return ($base ?: '') . '/' . ltrim($path, '/');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf']))) {
        http_response_code(419);
        exit('Session expired. Please go back and try again.');
    }
}

function flash(?string $key = null, ?string $value = null)
{
    if ($key && $value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    if ($key) {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }
    return $_SESSION['_flash'] ?? [];
}

function money($value): string
{
    return number_format((float) $value, 2);
}

function weight3($value): string
{
    return number_format((float) $value, 3);
}
