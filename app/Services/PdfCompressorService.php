<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Reduz o tamanho de um PDF via Ghostscript (gs -sDEVICE=pdfwrite).
 * IMPORTANTE: depende de um binário externo instalado no servidor — em
 * hospedagem compartilhada (ex: shared hosting) o Ghostscript costuma NÃO
 * estar disponível e exec()/shell_exec() costumam vir desabilitados por
 * segurança. Por isso isAvailable() sempre checa em runtime antes de tentar
 * comprimir, em vez de assumir que a feature funciona.
 *
 * Em ambientes XAMPP, o Apache herda LD_LIBRARY_PATH apontando pra libstdc++
 * antiga empacotada em /opt/lampp/lib — o `gs` do sistema (compilado contra
 * uma libstdc++ mais nova) quebra ao herdar essa variável de um processo
 * filho via exec(). Por isso zeramos LD_LIBRARY_PATH explicitamente no
 * comando, mesmo fora do XAMPP isso é inofensivo.
 */
class PdfCompressorService
{
    private const LEVELS = [
        'low'    => '/printer', // ~300dpi, prioriza qualidade
        'medium' => '/ebook',   // ~150dpi, equilíbrio
        'high'   => '/screen',  // ~72dpi, prioriza tamanho
    ];

    private const TIMEOUT_SECONDS = 45;

    public static function isAvailable(): bool
    {
        if (!function_exists('exec') || !function_exists('escapeshellarg')) {
            return false;
        }

        $output = [];
        $exitCode = 0;
        @exec('command -v gs 2>/dev/null', $output, $exitCode);

        return $exitCode === 0 && !empty($output);
    }

    /**
     * Comprime o PDF em $inputPath e retorna o caminho do arquivo de saída
     * (responsabilidade do chamador apagar depois), ou null se falhar.
     */
    public static function compress(string $inputPath, string $level = 'medium'): ?string
    {
        $settings = self::LEVELS[$level] ?? self::LEVELS['medium'];
        $outputPath = sys_get_temp_dir() . '/prisma-pdf-' . uuid4() . '.pdf';

        $cmd = sprintf(
            'LD_LIBRARY_PATH= timeout %d gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=%s '
            . '-dNOPAUSE -dQUIET -dBATCH -dSAFER -sOutputFile=%s %s 2>&1',
            self::TIMEOUT_SECONDS,
            escapeshellarg($settings),
            escapeshellarg($outputPath),
            escapeshellarg($inputPath)
        );

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            if (is_file($outputPath)) {
                @unlink($outputPath);
            }
            return null;
        }

        return $outputPath;
    }
}
