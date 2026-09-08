<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Extrai o texto de um PDF via `pdftotext` (poppler-utils) e formata como
 * Markdown, separando por página. É extração de texto puro — não reconhece
 * títulos/tabelas por estrutura, só preserva os parágrafos como estão no PDF.
 *
 * LIMITAÇÃO IMPORTANTE: PDFs escaneados (imagem, sem texto selecionável) não
 * têm texto pra extrair — isso não faz OCR. isLikelyScanned() detecta esse
 * caso comparando texto extraído × número de páginas, pra avisar o usuário
 * em vez de devolver um markdown vazio silenciosamente.
 *
 * Mesma ressalva do PdfCompressorService quanto a LD_LIBRARY_PATH sob XAMPP.
 */
class PdfToMarkdownService
{
    private const TIMEOUT_SECONDS = 60;

    /** Abaixo disso (caracteres úteis por página, em média) consideramos "provavelmente escaneado". */
    private const MIN_CHARS_PER_PAGE = 20;

    public static function isAvailable(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $output = [];
        $exitCode = 0;
        @exec('command -v pdftotext 2>/dev/null', $output, $exitCode);

        return $exitCode === 0 && !empty($output);
    }

    public static function pageCount(string $pdfPath): int
    {
        $output = [];
        exec(sprintf('LD_LIBRARY_PATH= pdfinfo %s 2>/dev/null', escapeshellarg($pdfPath)), $output);
        foreach ($output as $line) {
            if (preg_match('/^Pages:\s*(\d+)/', $line, $m)) {
                return (int) $m[1];
            }
        }
        return 0;
    }

    /**
     * @return array{markdown:string, pages:int, likely_scanned:bool}|null
     */
    public static function convert(string $pdfPath): ?array
    {
        $tmpOut = sys_get_temp_dir() . '/prisma-pdf2md-' . uuid4() . '.txt';

        $cmd = sprintf(
            'LD_LIBRARY_PATH= timeout %d pdftotext -layout %s %s 2>&1',
            self::TIMEOUT_SECONDS,
            escapeshellarg($pdfPath),
            escapeshellarg($tmpOut)
        );

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($tmpOut)) {
            if (is_file($tmpOut)) {
                @unlink($tmpOut);
            }
            return null;
        }

        $raw = file_get_contents($tmpOut);
        @unlink($tmpOut);

        if ($raw === false) {
            return null;
        }

        $pages = explode("\f", $raw); // pdftotext separa páginas com form-feed (\f)
        // A última página costuma vir seguida de um \f extra, gerando um item vazio.
        if (count($pages) > 1 && trim(end($pages)) === '') {
            array_pop($pages);
        }
        $pageCount = count($pages);

        $totalChars = 0;
        $sections = [];
        foreach ($pages as $i => $pageText) {
            $clean = trim($pageText);
            $totalChars += mb_strlen($clean);
            $sections[] = "## Página " . ($i + 1) . "\n\n" . ($clean !== '' ? $clean : '_(sem texto extraível nesta página)_');
        }

        $markdown = implode("\n\n---\n\n", $sections);
        $avgCharsPerPage = $pageCount > 0 ? $totalChars / $pageCount : 0;

        return [
            'markdown'       => $markdown,
            'pages'          => $pageCount,
            'likely_scanned' => $avgCharsPerPage < self::MIN_CHARS_PER_PAGE,
        ];
    }
}
