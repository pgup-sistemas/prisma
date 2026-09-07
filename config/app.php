<?php

return [
    'name'  => env('APP_NAME', 'PRISMA'),
    'env'   => env('APP_ENV', 'local'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url'   => env('APP_URL', 'http://localhost:8000'),
    'key'   => env('APP_KEY', ''),
    'timezone' => 'America/Porto_Velho',
    'locale'   => 'pt_BR',
];
