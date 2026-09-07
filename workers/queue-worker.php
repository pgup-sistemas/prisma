<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(ROOT)->safeLoad();

use App\Services\QueueService;
use App\Services\QRLogoService;
use App\Services\AnalyticsService;
use App\Services\MailerService;
use App\Models\Job;

$queue = $argv[1] ?? 'default';

$pid = getmypid();
echo "[Worker:{$pid}] Iniciado. Queue: {$queue}" . PHP_EOL;

while (true) {
    $job = QueueService::pop($queue, 1);

    if ($job === null) {
        // Timeout BRPOP — continua aguardando
        continue;
    }

    $jobId   = $job['id'] ?? null;
    $payload = $job['payload'] ?? [];

    echo "[Worker:{$pid}] Processando job {$jobId} (queue: {$queue})" . PHP_EOL;

    try {
        match ($queue) {
            'qrlogo'          => (new QRLogoService())->processJob($payload),
            'click'           => AnalyticsService::recordClick($payload),
            'email'           => MailerService::processJob($payload),
            'bookmark_health' => \App\Services\BookmarkImporterService::checkHealth($payload),
            default           => throw new \RuntimeException("Job type desconhecido: {$queue}"),
        };

        Job::markDone($jobId);
        echo "[Worker:{$pid}] Concluído: {$jobId}" . PHP_EOL;

    } catch (\Throwable $e) {
        Job::markFailed($jobId, $e->getMessage());
        error_log("[Worker:{$pid}] ERRO em {$jobId}: " . $e->getMessage());
        echo "[Worker:{$pid}] Falhou: {$jobId} — " . $e->getMessage() . PHP_EOL;
    }
}
