<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\QRCode;

class ShortURLService
{
    private const CODE_LEN = 7;
    private const MAX_TRIES = 10;

    /**
     * Generate a unique short code not already in the qrcodes table.
     */
    public static function generateCode(): string
    {
        for ($i = 0; $i < self::MAX_TRIES; $i++) {
            $code = shortCode(self::CODE_LEN);
            if (QRCode::findByShortCode($code) === null) {
                return $code;
            }
        }

        // Extremely unlikely — extend length as fallback
        return shortCode(self::CODE_LEN + 3);
    }

    /**
     * Build the full short URL for a given code.
     */
    public static function buildUrl(string $code): string
    {
        return url('/r/' . $code);
    }

    /**
     * Assign a short code to a QR code that doesn't have one yet.
     */
    public static function assignToQR(int $qrId): string
    {
        $code = self::generateCode();
        $shortUrl = self::buildUrl($code);

        QRCode::update($qrId, [
            'short_code' => $code,
            'short_url'  => $shortUrl,
        ]);

        return $code;
    }
}
