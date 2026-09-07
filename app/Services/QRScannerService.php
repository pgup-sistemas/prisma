<?php

declare(strict_types=1);

namespace App\Services;

use Zxing\QrReader;

class QRScannerService
{
    /**
     * Decodifica um QR Code a partir de um arquivo de imagem.
     * Retorna ['text' => string, 'type' => string] ou null se nada for encontrado.
     */
    public function decode(string $imagePath): ?array
    {
        try {
            $reader = new QrReader($imagePath, QrReader::SOURCE_TYPE_FILE);
            $text = $reader->text();
        } catch (\Throwable $e) {
            return null;
        }

        if (!is_string($text) || $text === '') {
            return null;
        }

        return ['text' => $text, 'type' => $this->guessType($text)];
    }

    /**
     * Heurística simples para sugerir o tipo PRISMA a partir do conteúdo decodificado.
     */
    private function guessType(string $text): string
    {
        return match (true) {
            preg_match('/^https?:\/\//i', $text) === 1        => 'url',
            str_starts_with(strtoupper($text), 'BEGIN:VCARD') => 'vcard',
            str_starts_with(strtoupper($text), 'BEGIN:VEVENT'),
            str_starts_with(strtoupper($text), 'BEGIN:VCALENDAR') => 'event',
            str_starts_with(strtoupper($text), 'WIFI:')        => 'wifi',
            str_starts_with(strtolower($text), 'mailto:')      => 'email',
            str_starts_with(strtolower($text), 'tel:')         => 'phone',
            str_starts_with(strtolower($text), 'smsto:'),
            str_starts_with(strtolower($text), 'sms:')         => 'sms',
            str_starts_with(strtolower($text), 'geo:')         => 'geo',
            str_starts_with(strtolower($text), 'bitcoin:')     => 'bitcoin',
            str_starts_with($text, '000201') && str_contains($text, 'br.gov.bcb.pix') => 'pix',
            default => 'text',
        };
    }
}
