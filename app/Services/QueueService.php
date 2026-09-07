<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Cache;

class QueueService
{
    /**
     * Empurra um job para a fila Redis e registra auditoria no MySQL.
     * Retorna o UUID do job.
     */
    public static function push(string $queue, array $payload): string
    {
        $jobId = uuid4();

        $job = [
            'id'         => $jobId,
            'queue'      => $queue,
            'payload'    => $payload,
            'created_at' => date('c'),
            'attempts'   => 0,
        ];

        Cache::client()->lpush('queue:' . $queue, json_encode($job));

        // Auditoria MySQL — falha silenciosa (não bloqueia o enqueue)
        try {
            // Injeta _job_id no payload para que markDone/markFailed consigam localizar a linha
            $auditPayload = array_merge(['_job_id' => $jobId], $payload);
            \App\Models\Job::create([
                'queue'   => $queue,
                'payload' => json_encode($auditPayload),
                'status'  => 'pending',
            ]);
        } catch (\Throwable) {
            // Ignorado — Redis é a fila real; MySQL é apenas auditoria
        }

        return $jobId;
    }

    /**
     * Bloqueia até $timeout segundos esperando um job da fila.
     * Retorna o job decodificado ou null em caso de timeout / erro de conexão.
     *
     * Nota: o $timeout deve ser menor que o read_write_timeout configurado no Redis
     * (config/redis.php) para evitar que o socket expire antes do BRPOP.
     */
    public static function pop(string $queue, int $timeout = 1): ?array
    {
        try {
            $raw = Cache::client()->brpop(['queue:' . $queue], $timeout);
        } catch (\Predis\Connection\ConnectionException $e) {
            // Reconecta silenciosamente na próxima iteração
            return null;
        }

        if ($raw === null) {
            return null;
        }

        // brpop retorna [key, value]
        return json_decode($raw[1], true);
    }
}
