<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Extrai o conteúdo de um PDF via `pdftohtml -xml` (poppler-utils) e formata
 * como Markdown, reconhecendo estrutura básica a partir do tamanho de fonte
 * (títulos) e do atributo <b> (negrito) que o poppler já identifica no PDF —
 * não é análise de layout genérica, é o que o próprio PDF declara.
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

    /** Gap vertical (em relação à altura da linha) que indica quebra de parágrafo, não só linha "quebrada". */
    private const PARAGRAPH_GAP_RATIO = 1.6;

    public static function isAvailable(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $output = [];
        $exitCode = 0;
        @exec('command -v pdftohtml 2>/dev/null', $output, $exitCode);

        return $exitCode === 0 && !empty($output);
    }

    /**
     * @return array{markdown:string, pages:int, likely_scanned:bool}|null
     */
    public static function convert(string $pdfPath): ?array
    {
        $cmd = sprintf(
            'LD_LIBRARY_PATH= timeout %d pdftohtml -xml -stdout -i -q %s 2>/dev/null',
            self::TIMEOUT_SECONDS,
            escapeshellarg($pdfPath)
        );

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || empty($output)) {
            return null;
        }

        $xml = implode("\n", $output);

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        libxml_use_internal_errors(false);

        if ($doc === false) {
            return null;
        }

        return self::buildMarkdown($doc);
    }

    /**
     * @return array{markdown:string, pages:int, likely_scanned:bool}
     */
    private static function buildMarkdown(\SimpleXMLElement $doc): array
    {
        // 1ª passada: mapear tamanho de fonte por id (declarados uma vez, reusados nas páginas seguintes).
        $fontSizes = [];
        foreach ($doc->xpath('//fontspec') as $spec) {
            $fontSizes[(string) $spec['id']] = (int) $spec['size'];
        }

        // 2ª passada: descobrir o tamanho "de corpo de texto" — o que soma mais caracteres no total
        // (títulos são curtos, texto corrido domina a contagem).
        $charsBySize = [];
        foreach ($doc->xpath('//text') as $node) {
            $size = $fontSizes[(string) $node['font']] ?? 12;
            $len = mb_strlen(self::nodeText($node));
            $charsBySize[$size] = ($charsBySize[$size] ?? 0) + $len;
        }
        arsort($charsBySize);
        $bodySize = $charsBySize !== [] ? (int) array_key_first($charsBySize) : 12;

        $pageCount = 0;
        $totalChars = 0;
        $pageSections = [];

        foreach ($doc->page as $page) {
            $pageCount++;
            $lines = [];

            $prevBottom = null;
            $paragraph = [];

            $flush = function () use (&$paragraph, &$lines) {
                if ($paragraph !== []) {
                    $lines[] = implode(' ', $paragraph);
                    $paragraph = [];
                }
            };

            foreach ($page->text as $node) {
                $raw = self::nodeText($node);
                if ($raw === '') {
                    continue;
                }

                $totalChars += mb_strlen($raw);

                $top = (float) $node['top'];
                $height = (float) $node['height'] ?: 12.0;
                $size = $fontSizes[(string) $node['font']] ?? $bodySize;
                $isBold = self::isFullyWrapped($node, 'b');
                $isItalic = self::isFullyWrapped($node, 'i');

                $ratio = $bodySize > 0 ? $size / $bodySize : 1.0;
                $headingLevel = match (true) {
                    $ratio >= 1.8 => 1,
                    $ratio >= 1.4 => 2,
                    $ratio >= 1.15 => 3,
                    default => 0,
                };

                $text = self::escapeMarkdown($raw);
                if ($isBold && $headingLevel === 0) {
                    $text = '**' . $text . '**';
                } elseif ($isItalic && $headingLevel === 0) {
                    $text = '*' . $text . '*';
                }

                if ($headingLevel > 0) {
                    $flush();
                    $lines[] = str_repeat('#', $headingLevel) . ' ' . self::escapeMarkdown($raw, false);
                    $prevBottom = $top + $height;
                    continue;
                }

                $isNewParagraph = $prevBottom !== null && ($top - $prevBottom) > $height * self::PARAGRAPH_GAP_RATIO;
                if ($isNewParagraph) {
                    $flush();
                }

                $paragraph[] = $text;
                $prevBottom = $top + $height;
            }

            $flush();

            if ($lines !== []) {
                $pageSections[] = implode("\n\n", $lines) . "\n\n<sub>— página " . $pageCount . " —</sub>";
            } else {
                $pageSections[] = "<sub>— página " . $pageCount . " (sem texto extraível) —</sub>";
            }
        }

        $markdown = implode("\n\n---\n\n", $pageSections);
        $avgCharsPerPage = $pageCount > 0 ? $totalChars / $pageCount : 0;

        return [
            'markdown'       => $markdown,
            'pages'          => $pageCount,
            'likely_scanned' => $avgCharsPerPage < self::MIN_CHARS_PER_PAGE,
        ];
    }

    /**
     * Verifica se todo o conteúdo do <text> está envolto por uma única tag
     * (ex: <text><b>...</b></text>) — só então tratamos a linha inteira como
     * negrito/itálico, evitando falsos positivos de negrito parcial.
     */
    private static function isFullyWrapped(\SimpleXMLElement $node, string $tag): bool
    {
        $children = $node->children();
        if (count($children) !== 1) {
            return false;
        }

        foreach ($children as $child) {
            if ($child->getName() !== $tag) {
                return false;
            }
            $childText = self::nodeText($child);
            return $childText !== '' && $childText === self::nodeText($node);
        }

        return false;
    }

    /**
     * Extrai o texto completo de um nó, incluindo conteúdo dentro de filhos
     * como <b>/<i> — (string)$node do SimpleXML NÃO recursa em filhos, só
     * pega texto direto do nó, por isso passamos pelo DOM.
     */
    private static function nodeText(\SimpleXMLElement $node): string
    {
        $dom = dom_import_simplexml($node);
        return trim($dom->textContent ?? '');
    }

    private static function escapeMarkdown(string $text, bool $escape = true): string
    {
        if (!$escape) {
            return $text;
        }
        // Evita que caracteres do texto original virem formatação markdown acidental.
        return preg_replace('/([*_`\\\\])/u', '\\\\$1', $text) ?? $text;
    }
}
