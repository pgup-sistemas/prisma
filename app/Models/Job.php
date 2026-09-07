<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Job extends Model
{
    protected static string $table = 'jobs';

    protected static function timestamps(array $data, bool $update = false): array
    {
        // jobs.created_at tem DEFAULT CURRENT_TIMESTAMP; available_at/started_at/finished_at
        // são definidos explicitamente quando necessário
        return $data;
    }

    public static function markDone(?string $jobUuid): void
    {
        if ($jobUuid === null) {
            return;
        }

        $pdo = Database::get();
        $stmt = $pdo->prepare(
            "UPDATE jobs
             SET status = 'done', finished_at = NOW()
             WHERE JSON_UNQUOTE(JSON_EXTRACT(payload, '$._job_id')) = ?
               AND status IN ('pending', 'processing')
             LIMIT 1"
        );
        $stmt->execute([$jobUuid]);
    }

    public static function markFailed(?string $jobUuid, string $error): void
    {
        if ($jobUuid === null) {
            return;
        }

        $pdo = Database::get();
        $stmt = $pdo->prepare(
            "UPDATE jobs
             SET status = 'failed', error = ?, finished_at = NOW()
             WHERE JSON_UNQUOTE(JSON_EXTRACT(payload, '$._job_id')) = ?
               AND status IN ('pending', 'processing')
             LIMIT 1"
        );
        $stmt->execute([mb_substr($error, 0, 1000), $jobUuid]);
    }
}
