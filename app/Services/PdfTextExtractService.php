<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class PdfTextExtractService
{
    private const TEXT_EXTRACT_TIMEOUT_SECONDS = 5;

    public function extract(string $path): array
    {
        $result = $this->extractWithPyMuPdf($path)
            ?? $this->extractWithPdfToText($path)
            ?? ['text' => '', 'engine' => null];

        return [
            'text' => trim((string) ($result['text'] ?? '')),
            'engine' => $result['engine'] ?? null,
        ];
    }

    private function extractWithPyMuPdf(string $path): ?array
    {
        $python = $this->findCommand('python3') ?: $this->findCommand('python');

        if (! $python) {
            return null;
        }

        $process = new Process([$python, base_path('scripts/pdf_text_extract.py'), $path, '2']);
        $process->setTimeout(self::TEXT_EXTRACT_TIMEOUT_SECONDS);
        $process->setIdleTimeout(self::TEXT_EXTRACT_TIMEOUT_SECONDS);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $payload = $this->decodeJsonOutput($process->getOutput());

        if (! is_array($payload) || isset($payload['error'])) {
            return null;
        }

        return [
            'text' => (string) ($payload['text'] ?? ''),
            'engine' => 'pymupdf',
        ];
    }

    private function extractWithPdfToText(string $path): ?array
    {
        $pdfToText = $this->findCommand('pdftotext');

        if (! $pdfToText) {
            return null;
        }

        $process = new Process([$pdfToText, '-layout', $path, '-']);
        $process->setTimeout(self::TEXT_EXTRACT_TIMEOUT_SECONDS);
        $process->setIdleTimeout(self::TEXT_EXTRACT_TIMEOUT_SECONDS);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        return [
            'text' => $process->getOutput(),
            'engine' => 'pdftotext',
        ];
    }

    private function decodeJsonOutput(string $output): ?array
    {
        $payload = json_decode(trim($output), true);

        if (is_array($payload)) {
            return $payload;
        }

        $lines = array_reverse(preg_split('/\R/', trim($output)) ?: []);

        foreach ($lines as $line) {
            $payload = json_decode(trim($line), true);

            if (is_array($payload)) {
                return $payload;
            }
        }

        return null;
    }

    private function findCommand(string $command): ?string
    {
        $candidates = in_array($command, ['python', 'python3'], true)
            ? [
                base_path('storage/app/paddleocr/.venv/bin/'.$command),
                rtrim((string) getenv('HOME'), '/').'/.local/bin/'.$command,
                $command,
            ]
            : [
                $command,
                rtrim((string) getenv('HOME'), '/').'/.local/bin/'.$command,
                base_path('storage/app/paddleocr/.venv/bin/'.$command),
            ];

        foreach ($candidates as $candidate) {
            if ($candidate !== $command && is_executable($candidate)) {
                return $candidate;
            }
        }

        $process = Process::fromShellCommandline('command -v '.escapeshellarg($command));
        $process->run();

        return $process->isSuccessful() ? trim($process->getOutput()) : null;
    }
}
