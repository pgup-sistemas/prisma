<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache;

class CepService
{
    private const TTL = 86400; // endereços mudam raramente — cache de 24h
    private const API_URL = 'https://viacep.com.br/ws/%s/json/';

    /**
     * Consulta um CEP brasileiro via ViaCEP.
     * Retorna null se o CEP for inválido, não existir, ou a API falhar.
     */
    public static function lookup(string $cep): ?array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        $cacheKey = 'cep:' . $cep;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached === false ? null : $cached;
        }

        $result = self::fetchApi($cep);

        // Cacheia inclusive o "não encontrado" (como false) pra não martelar a API
        // com o mesmo CEP inválido repetidamente.
        Cache::set($cacheKey, $result ?? false, self::TTL);

        return $result;
    }

    private static function fetchApi(string $cep): ?array
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout'       => 4,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents(sprintf(self::API_URL, $cep), false, $ctx);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !empty($data['erro'])) {
            return null;
        }

        return [
            'cep'         => $data['cep'] ?? $cep,
            'logradouro'  => $data['logradouro'] ?? '',
            'complemento' => $data['complemento'] ?? '',
            'bairro'      => $data['bairro'] ?? '',
            'cidade'      => $data['localidade'] ?? '',
            'uf'          => $data['uf'] ?? '',
            'ddd'         => $data['ddd'] ?? '',
        ];
    }
}
