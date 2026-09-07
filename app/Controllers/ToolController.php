<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Services\CepService;
use App\Services\CurrencyService;
use App\Services\PdfCompressorService;

class ToolController extends Controller
{
    public function index(): void
    {
        $this->render('tools/index', [
            'title'           => 'Mini Ferramentas',
            'meta_description' => 'Ferramentas gratuitas: conversor de unidades, moedas, gerador de senha, validador de CPF/CNPJ e mais.',
        ], 'public');
    }

    public function currency(): void
    {
        Middleware::rateLimit('tools:currency:' . $this->request->ip(), 60, 60);

        $from   = (string) $this->request->input('from', 'USD');
        $to     = (string) $this->request->input('to', 'BRL');
        $amount = (float) $this->request->input('amount', 1);

        if ($amount < 0 || $amount > 1_000_000_000) {
            $this->json(['success' => false, 'error' => 'Valor inválido.'], 422);
            return;
        }

        $result = CurrencyService::convert($from, $to, $amount);

        if ($result === null) {
            $this->json(['success' => false, 'error' => 'Moeda não suportada.'], 422);
            return;
        }

        $this->json([
            'success' => true,
            'from'    => strtoupper($from),
            'to'      => strtoupper($to),
            'amount'  => $amount,
            'result'  => $result,
            'rates'   => CurrencyService::rates(),
        ]);
    }

    public function cep(): void
    {
        Middleware::rateLimit('tools:cep:' . $this->request->ip(), 60, 60);

        $cep = (string) $this->request->input('cep', '');
        $result = CepService::lookup($cep);

        if ($result === null) {
            $this->json(['success' => false, 'error' => 'CEP não encontrado.'], 404);
            return;
        }

        $this->json(['success' => true, 'data' => $result]);
    }

    public function pdfCompress(): void
    {
        Middleware::rateLimit('tools:pdf:' . $this->request->ip(), 10, 300);

        if (!PdfCompressorService::isAvailable()) {
            $this->json(['success' => false, 'error' => 'Recurso indisponível neste servidor no momento.'], 503);
            return;
        }

        $file = $this->request->file('pdf');

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0) {
            $this->json(['success' => false, 'error' => 'Selecione um arquivo PDF válido.'], 422);
            return;
        }

        if ($file['size'] > 15 * 1024 * 1024) {
            $this->json(['success' => false, 'error' => 'Arquivo muito grande (máx. 15MB).'], 422);
            return;
        }

        if (!validMime($file['tmp_name'], ['application/pdf'])) {
            $this->json(['success' => false, 'error' => 'Envie um arquivo PDF válido.'], 422);
            return;
        }

        $level = (string) $this->request->input('level', 'medium');
        if (!in_array($level, ['low', 'medium', 'high'], true)) {
            $level = 'medium';
        }

        $outputPath = PdfCompressorService::compress($file['tmp_name'], $level);

        if ($outputPath === null) {
            $this->json(['success' => false, 'error' => 'Falha ao reduzir o PDF. Tente outro arquivo.'], 500);
            return;
        }

        $compressedSize = filesize($outputPath);
        $data = file_get_contents($outputPath);
        @unlink($outputPath);

        if ($data === false) {
            $this->json(['success' => false, 'error' => 'Falha ao ler o PDF reduzido.'], 500);
            return;
        }

        $this->json([
            'success'         => true,
            'pdf'             => 'data:application/pdf;base64,' . base64_encode($data),
            'original_size'   => (int) $file['size'],
            'compressed_size' => $compressedSize,
        ]);
    }
}
