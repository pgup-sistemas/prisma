<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class HubBooking extends Model
{
    protected static string $table = 'hub_bookings';

    public static function findByUuid(string $uuid): ?array
    {
        return static::findBy('uuid', $uuid);
    }

    /**
     * Agendamentos ativos (pendentes ou confirmados) de um bloco numa data —
     * usados para calcular quais horários já estão ocupados.
     */
    public static function activeForBlockAndDate(int $blockId, string $date): array
    {
        return static::rows(
            "SELECT * FROM hub_bookings
             WHERE block_id = ? AND booking_date = ? AND status IN ('pending','confirmed')
             ORDER BY start_time ASC",
            [$blockId, $date]
        );
    }

    public static function hasConflict(int $blockId, string $date, string $startTime): bool
    {
        return (bool) static::row(
            "SELECT id FROM hub_bookings
             WHERE block_id = ? AND booking_date = ? AND start_time = ? AND status IN ('pending','confirmed')
             LIMIT 1",
            [$blockId, $date, $startTime]
        );
    }

    /**
     * Todos os agendamentos dos hubs pertencentes a um usuário (painel de gestão).
     */
    public static function forUser(int $userId, string $status = ''): array
    {
        $where = 'hp.user_id = ?';
        $params = [$userId];

        if ($status !== '') {
            $where .= ' AND hb.status = ?';
            $params[] = $status;
        }

        return static::rows(
            "SELECT hb.*, hp.title AS hub_title, hp.slug AS hub_slug, hbl.title AS block_title
             FROM hub_bookings hb
             JOIN hub_pages hp ON hp.id = hb.hub_id
             JOIN hub_blocks hbl ON hbl.id = hb.block_id
             WHERE {$where}
             ORDER BY hb.booking_date ASC, hb.start_time ASC",
            $params
        );
    }

    public static function ownedByUser(int $bookingId, int $userId): ?array
    {
        return static::row(
            "SELECT hb.* FROM hub_bookings hb
             JOIN hub_pages hp ON hp.id = hb.hub_id
             WHERE hb.id = ? AND hp.user_id = ?",
            [$bookingId, $userId]
        );
    }
}
