<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

function env(string $key, $default = null)
{
    static $values = null;
    if ($values === null) {
        $values = [];
        $file = BASE_PATH . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }
                [$name, $value] = explode('=', $line, 2);
                $values[trim($name)] = trim(trim($value), '"\'');
            }
        }
    }

    return $values[$key] ?? $_ENV[$key] ?? $default;
}

function config(string $key, $default = null)
{
    static $configs = [];
    [$file, $item] = array_pad(explode('.', $key, 2), 2, null);
    if (!isset($configs[$file])) {
        $path = BASE_PATH . "/config/{$file}.php";
        $configs[$file] = is_file($path) ? require $path : [];
    }

    return $item ? ($configs[$file][$item] ?? $default) : $configs[$file];
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('sv_session');
    session_start();
}
