<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private $routes = [];

    public function get(string $path, array $action): void
    {
        $this->routes['GET'][$this->normalize($path)] = $action;
    }

    public function post(string $path, array $action): void
    {
        $this->routes['POST'][$this->normalize($path)] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        verify_csrf();
        $path = $this->normalize($uri);
        $action = $this->routes[$method][$path] ?? null;

        if (!$action) {
            http_response_code(404);
            exit('Page not found.');
        }

        [$controller, $methodName] = $action;
        (new $controller())->$methodName();
    }

    private function normalize(string $path): string
    {
        $scriptBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $scriptBase = preg_replace('#/public$#', '', $scriptBase);
        if ($scriptBase && strpos($path, $scriptBase) === 0) {
            $path = substr($path, strlen($scriptBase));
        }
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
