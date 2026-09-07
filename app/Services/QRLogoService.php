<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\QRCode;
use App\Models\User;
use App\Models\CreditTransaction;
use chillerlan\QRCode\QRCode as QRLib;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Data\QRMatrix;

class QRLogoLegibilityException extends \RuntimeException {}

/**
 * QRLogoService — QR Code cujos módulos são pintados com as cores da logo do cliente.
 *
 * Conceito: a logo DO CLIENTE vira o QR Code.
 *   - A matriz de módulos é gerada normalmente (ECC H, 30% tolerância)
 *   - A logo é escalada para cobrir exatamente a área de módulos
 *   - Cada módulo escuro (dado) recebe a cor da logo naquele pixel
 *   - Módulos estruturais (finder, alignment, timing) ficam em fg_color padrão
 *   - Módulos claros ficam em bg_color
 *   - Resultado: QR visualmente composto pelas cores da logo, sem sobreposição
 */
class QRLogoService
{
    private const MIN_LEGIBILITY_INDEX = 70;
    private const MODULE_SIZE          = 20;  // px por módulo (maior = mais nítido)
    private const QUIET_ZONE          = 4;   // módulos de margem
    private const LUMA_THRESHOLD      = 215; // acima → fundo branco → usa fg_color

    /**
     * Gera QR Code pintado com as cores da logo do cliente.
     *
     * @throws QRLogoLegibilityException quando índice < MIN_LEGIBILITY_INDEX
     * @return array{path: string, index: int}
     */
    public function render(int $qrcodeId, string $logoAbsPath, array $options = []): array
    {
        $qr = QRCode::find($qrcodeId);
        if ($qr === null) {
            throw new \RuntimeException("QR Code #{$qrcodeId} não encontrado.");
        }

        $fgColor = $this->parseHex($qr['fg_color'] ?? '#000000');
        $bgColor = $this->parseHex($qr['bg_color'] ?? '#FFFFFF');

        // 1. Obtém a matriz de módulos (raw int[][]) do chillerlan
        $matrix = $this->buildMatrix($qr['content']);
        $matrixData = $matrix->getMatrix(); // int[][] com flags de tipo

        $moduleCount = count($matrixData);  // N (QR é N×N módulos)
        $ms          = self::MODULE_SIZE;
        $qz          = self::QUIET_ZONE;

        // 2. Tamanho total da imagem
        $imgSize     = ($moduleCount + $qz * 2) * $ms;
        $logoAreaPx  = $moduleCount * $ms;  // área apenas dos módulos (sem quiet zone)

        // 3. Carrega e escala a logo para exatamente $logoAreaPx × $logoAreaPx
        $logoScaled = $this->loadAndScale($logoAbsPath, $logoAreaPx, $bgColor);

        // 4. Cria imagem de saída com fundo bg_color
        $img    = imagecreatetruecolor($imgSize, $imgSize);
        $white  = imagecolorallocate($img, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        $black  = imagecolorallocate($img, $fgColor['r'], $fgColor['g'], $fgColor['b']);
        imagefill($img, 0, 0, $white);

        // 5. Renderiza módulo a módulo
        $qzOffset = $qz * $ms;

        foreach ($matrixData as $y => $row) {
            foreach ($row as $x => $moduleType) {
                $isDark = ($moduleType & QRMatrix::IS_DARK) !== 0;

                // Coordenadas em pixels na imagem de saída
                $px0 = $qzOffset + $x * $ms;
                $py0 = $qzOffset + $y * $ms;
                $px1 = $px0 + $ms - 1;
                $py1 = $py0 + $ms - 1;

                if (!$isDark) {
                    // Módulo claro → fundo branco (já preenchido, mas garante cor correta)
                    imagefilledrectangle($img, $px0, $py0, $px1, $py1, $white);
                    continue;
                }

                // Módulo escuro: verifica se é estrutural ou dado
                if ($this->isStructural($moduleType)) {
                    // Finder / alignment / timing / format → fg_color padrão (preto)
                    imagefilledrectangle($img, $px0, $py0, $px1, $py1, $black);
                } else {
                    // Módulo de dado → pinta com cor da logo nessa posição
                    $logoX = min((int)(($x + 0.5) * $ms), $logoAreaPx - 1);
                    $logoY = min((int)(($y + 0.5) * $ms), $logoAreaPx - 1);

                    $lp = imagecolorat($logoScaled, $logoX, $logoY);
                    $lr = ($lp >> 16) & 0xFF;
                    $lg = ($lp >> 8)  & 0xFF;
                    $lb = $lp         & 0xFF;
                    $luma = (int)(0.299 * $lr + 0.587 * $lg + 0.114 * $lb);

                    if ($luma < self::LUMA_THRESHOLD) {
                        // Logo tem conteúdo colorido aqui → pinta o módulo com essa cor
                        $color = imagecolorallocate($img, $lr, $lg, $lb);
                    } else {
                        // Logo é branca aqui → usa fg_color (mantém o módulo presente)
                        $color = $black;
                    }

                    imagefilledrectangle($img, $px0, $py0, $px1, $py1, $color);
                }
            }
        }

        imagedestroy($logoScaled);

        // 6. Calcula Índice PRISMA (compara com QR padrão da mesma matriz)
        $index = $this->calculateIndexFromMatrix($matrixData, $img, $imgSize, $qzOffset, $ms, $moduleCount);

        if ($index < self::MIN_LEGIBILITY_INDEX) {
            imagedestroy($img);
            throw new QRLogoLegibilityException(
                "Índice PRISMA de Legibilidade ({$index}) abaixo do mínimo (" . self::MIN_LEGIBILITY_INDEX . "). " .
                'Tente uma logo com mais contraste ou QR com conteúdo mais curto.'
            );
        }

        // 7. Salva
        $outFilename = 'storage/qr-logos/' . $qr['uuid'] . '.png';
        imagepng($img, ROOT . '/' . $outFilename, 6);
        imagedestroy($img);

        return ['path' => $outFilename, 'index' => $index];
    }

    /**
     * Ponto de entrada para o worker.
     */
    public function processJob(array $payload): void
    {
        $qrcodeId    = (int)($payload['qrcode_id'] ?? 0);
        $logoRelPath = (string)($payload['logo_path'] ?? '');

        if ($qrcodeId === 0 || $logoRelPath === '') {
            throw new \RuntimeException('Payload inválido para qrlogo job.');
        }

        try {
            $result = $this->render($qrcodeId, ROOT . '/' . ltrim($logoRelPath, '/'));

            QRCode::update($qrcodeId, [
                'file_logo_png'    => $result['path'],
                'legibility_index' => $result['index'],
                'has_qr_logo'      => 1,
                'render_status'    => 'done',
            ]);
        } catch (QRLogoLegibilityException $e) {
            $this->handleFailure($qrcodeId);
            throw $e;
        } catch (\Throwable $e) {
            $this->handleFailure($qrcodeId);
            throw $e;
        }
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Gera a QRMatrix bruta usando chillerlan com ECC H.
     */
    private function buildMatrix(string $data): QRMatrix
    {
        $options = new QROptions([
            'version'     => Version::AUTO,
            'eccLevel'    => EccLevel::H,
            'maskPattern' => \chillerlan\QRCode\Common\MaskPattern::AUTO,
        ]);

        return (new QRLib($options))
            ->addByteSegment($data)
            ->getQRMatrix();
    }

    /**
     * Retorna true para módulos estruturais (finder, alignment, timing, format).
     * Estes ficam em fg_color padrão independente da logo.
     *
     * Remove IS_DARK antes de comparar: M_FINDER_DARK → M_FINDER, etc.
     * M_FINDER_DOT (dark) → M_FINDER_DOT_LIGHT após remoção do bit.
     */
    private function isStructural(int $moduleType): bool
    {
        $t = $moduleType & ~QRMatrix::IS_DARK;

        return $t === QRMatrix::M_FINDER           // finder pattern
            || $t === QRMatrix::M_FINDER_DOT_LIGHT // centro do finder (dot)
            || $t === QRMatrix::M_SEPARATOR        // separador ao redor do finder
            || $t === QRMatrix::M_ALIGNMENT        // alignment pattern (versões > 1)
            || $t === QRMatrix::M_TIMING           // timing pattern
            || $t === QRMatrix::M_FORMAT           // format information
            || $t === QRMatrix::M_VERSION          // version information
            || $t === QRMatrix::M_DARKMODULE_LIGHT; // dark module obrigatório
    }

    /**
     * Carrega imagem e escala para $targetPx × $targetPx com letterbox em bg_color.
     */
    private function loadAndScale(string $path, int $targetPx, array $bgColor): \GdImage
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Logo não encontrada: {$path}");
        }

        $info = @getimagesize($path);
        $mime = $info['mime'] ?? '';

        $raw = match (true) {
            str_contains($mime, 'jpeg') => imagecreatefromjpeg($path),
            str_contains($mime, 'png')  => imagecreatefrompng($path),
            str_contains($mime, 'gif')  => imagecreatefromgif($path),
            str_contains($mime, 'webp') => imagecreatefromwebp($path),
            default => throw new \RuntimeException("Formato não suportado: {$mime}"),
        };

        if ($raw === false) {
            throw new \RuntimeException("GD não conseguiu carregar: {$path}");
        }

        $sw = imagesx($raw);
        $sh = imagesy($raw);

        // Escala mantendo proporção (fit inside target)
        $scale = min($targetPx / $sw, $targetPx / $sh);
        $dw    = max(1, (int)round($sw * $scale));
        $dh    = max(1, (int)round($sh * $scale));
        $dx    = (int)(($targetPx - $dw) / 2);
        $dy    = (int)(($targetPx - $dh) / 2);

        $out = imagecreatetruecolor($targetPx, $targetPx);
        $bg  = imagecolorallocate($out, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        imagefill($out, 0, 0, $bg);
        imagealphablending($out, true);
        imagecopyresampled($out, $raw, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);
        imagedestroy($raw);

        return $out;
    }

    /**
     * Índice PRISMA de Legibilidade (0–100).
     *
     * Como agora a imagem é gerada módulo a módulo, compara diretamente:
     * para cada módulo da matriz original, verifica se o pixel correspondente
     * na imagem gerada mantém o mesmo tom (escuro/claro).
     * Finder patterns têm peso 2×.
     */
    private function calculateIndexFromMatrix(
        array $matrixData,
        \GdImage $img,
        int $imgSize,
        int $qzOffset,
        int $ms,
        int $moduleCount
    ): int {
        $totalWeight  = 0;
        $intactWeight = 0;

        foreach ($matrixData as $y => $row) {
            foreach ($row as $x => $moduleType) {
                $origDark = ($moduleType & QRMatrix::IS_DARK) !== 0;

                // Finder patterns: peso 2×
                $isFinder = ($moduleType & ~QRMatrix::IS_DARK) === QRMatrix::M_FINDER
                         || $moduleType === QRMatrix::M_FINDER_DARK
                         || $moduleType === QRMatrix::M_FINDER_DOT;
                $weight   = $isFinder ? 2 : 1;
                $totalWeight += $weight;

                // Pega a cor do centro do módulo na imagem gerada
                $px   = min($qzOffset + (int)(($x + 0.5) * $ms), $imgSize - 1);
                $py   = min($qzOffset + (int)(($y + 0.5) * $ms), $imgSize - 1);
                $pixel = imagecolorat($img, $px, $py);
                $modDark = $this->getLuma($pixel) < 128;

                if ($origDark === $modDark) {
                    $intactWeight += $weight;
                }
            }
        }

        return $totalWeight > 0 ? (int)(($intactWeight / $totalWeight) * 100) : 100;
    }

    private function getLuma(int $rgba): int
    {
        return (int)(0.299 * (($rgba >> 16) & 0xFF)
                   + 0.587 * (($rgba >> 8)  & 0xFF)
                   + 0.114 * ($rgba         & 0xFF));
    }

    private function parseHex(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private function handleFailure(int $qrcodeId): void
    {
        $qr = QRCode::find($qrcodeId);
        if ($qr) {
            $this->refundCredit((int)$qr['user_id'], (string)$qr['uuid']);
        }
        QRCode::update($qrcodeId, ['render_status' => 'failed']);
    }

    private function refundCredit(int $userId, string $reference): void
    {
        User::addCredit($userId, 1);
        $balance = User::getCredits($userId);

        CreditTransaction::create([
            'user_id'      => $userId,
            'type'         => 'refund',
            'amount'       => 1,
            'description'  => 'Reembolso QR-Logo — índice de legibilidade insuficiente',
            'reference'    => $reference,
            'balance_after'=> $balance,
        ]);
    }
}
