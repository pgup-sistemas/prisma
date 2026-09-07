<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        // Este XAMPP tem serialize_precision=100 no php.ini, o que expõe artefatos
        // de ponto flutuante binário (ex: 511.145199999999988...) em vez do valor
        // arredondado esperado. -1 usa a representação mais curta que faz round-trip.
        $previous = ini_set('serialize_precision', '-1');
        echo json_encode($data);
        if ($previous !== false) {
            ini_set('serialize_precision', $previous);
        }
    }
}
