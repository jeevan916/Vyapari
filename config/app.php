<?php

return [
    'name' => env('APP_NAME', 'Sanghavi Vyapari'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => env('APP_URL', ''),
];
