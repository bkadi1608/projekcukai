<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class SuratPermohonanOcrService
{
    private const EXTRACT_TIMEOUT_SECONDS = 20;
    private const TEXT_EXTRACT_TIMEOUT_SECONDS = 5;

    public function extract(string $path): array
    {
        $result = $this->extractWithPyMuPdf($path)
            ?? $this->extractWithPdfToText($path)
            ?? $this->extractWithPaddleOcr($path)
            ?? ['text' => '', 'engine' => null];

        $text = trim($result['text'] ?? '');

        return [
            'text' => $text,
            'engine' => $result['engine'] ?? null,
            'fields' => $this->parseFields($text),
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

    private function extractWithPaddleOcr(string $path): ?array
    {
        if (! filter_var(env('OCR_ENABLE_PADDLEOCR', false), FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        $python = $this->findCommand('python3') ?: $this->findCommand('python');

        if (! $python) {
            return null;
        }

        $process = new Process([$python, base_path('scripts/paddleocr_extract.py'), $path, '1']);
        $process->setTimeout(self::EXTRACT_TIMEOUT_SECONDS);
        $process->setIdleTimeout(self::EXTRACT_TIMEOUT_SECONDS);
        $process->setEnv([
            'PADDLE_PDX_DISABLE_MODEL_SOURCE_CHECK' => 'True',
        ]);
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
            'engine' => 'paddleocr',
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

    private function parseFields(string $text): array
    {
        $normalized = trim(preg_replace('/[ \t]+/', ' ', str_replace("\r", "\n", $text)));
        $lines = collect(explode("\n", $normalized))
            ->map(fn ($line) => trim(preg_replace('/\s+/', ' ', $line)))
            ->filter()
            ->values();

        $nomor = $this->labeledValue($lines, ['nomor surat', 'nomor', 'no surat', 'no'])
            ?? $this->matchFirst($normalized, [
                '/\bnomor\s+surat\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bnomor\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bno\.?\s+surat\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bno\.?\s*[:\-]?\s*([^\n\r]+)/iu',
            ]);

        $tanggal = $this->labeledValue($lines, ['tanggal surat', 'tgl surat', 'tanggal', 'tgl'])
            ?? $this->matchFirst($normalized, [
                '/\btanggal\s+surat\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btanggal\s+surat\s*[:\-]?\s*([0-9]{1,2}\s+[[:alpha:]]+\.?\s+[0-9]{4})/iu',
                '/\btanggal\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btanggal\s*[:\-]?\s*([0-9]{1,2}\s+[[:alpha:]]+\.?\s+[0-9]{4})/iu',
                '/\btgl\.?\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btgl\.?\s*[:\-]?\s*([0-9]{1,2}\s+[[:alpha:]]+\.?\s+[0-9]{4})/iu',
                '/\b([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\b([0-9]{1,2}\s+[[:alpha:]]+\.?\s+[0-9]{4})/iu',
                '/\b([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{4})/u',
            ]);

        $hal = $this->labeledValue($lines, ['perihal', 'ihwal', 'hal'], true)
            ?? $this->matchFirst($normalized, [
                '/\bhal\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bperihal\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bihwal\s*[:\-]?\s*([^\n\r]+)/iu',
            ]);

        return [
            'no_surat_permohonan' => $nomor ? $this->cleanExtractedValue($nomor) : null,
            'tgl_surat_permohonan' => $this->parseDate($tanggal),
            'hal_surat_permohonan' => $hal ? $this->cleanExtractedValue($hal) : null,
        ];
    }

    private function labeledValue($lines, array $labels, bool $joinContinuation = false): ?string
    {
        usort($labels, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($lines as $index => $line) {
            foreach ($labels as $label) {
                $pattern = '/^'.preg_quote($label, '/').'(?:\.)?(?=\W|$)\s*[:\-]?\s*(.*)$/iu';

                if (! preg_match($pattern, $line, $matches)) {
                    continue;
                }

                $value = $this->stripLeadingSeparator(trim($matches[1] ?? ''));
                $valueLineIndex = $index;

                if ($value === '') {
                    $value = $this->stripLeadingSeparator($lines[$index + 1] ?? '');
                    $valueLineIndex = $index + 1;
                }

                if ($joinContinuation) {
                    return $this->joinContinuationLines($lines, $valueLineIndex, $value);
                }

                return $value !== '' ? $value : null;
            }
        }

        return null;
    }

    private function cleanExtractedValue(string $value): string
    {
        $value = $this->stripLeadingSeparator($value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = preg_replace('/\s+([,.;:])/', '$1', $value);
        $value = preg_replace('/\(\s+/', '(', $value);
        $value = preg_replace('/\s+\)/', ')', $value);

        return trim($value);
    }

    private function matchFirst(string $text, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function stripLeadingSeparator(?string $value): string
    {
        return trim(preg_replace('/^[\s:;\-\.]+/u', '', (string) $value));
    }

    private function joinContinuationLines($lines, int $valueLineIndex, string $firstValue): ?string
    {
        $parts = [];
        $firstValue = $this->stripLeadingSeparator($firstValue);

        if ($firstValue !== '') {
            $parts[] = $firstValue;
        }

        $start = $valueLineIndex + 1;

        for ($i = $start; $i < count($lines); $i++) {
            $line = $this->stripLeadingSeparator($lines[$i] ?? '');

            if ($line === '') {
                continue;
            }

            if ($this->isKnownLabelLine($line)) {
                break;
            }

            $parts[] = $line;

            if (str_ends_with($line, '.') || str_ends_with($line, ')')) {
                break;
            }
        }

        return $parts ? implode(' ', $parts) : null;
    }

    private function isKnownLabelLine(string $line): bool
    {
        return (bool) preg_match('/^(nomor|no|tanggal|tgl|lampiran|kepada|yth|dari|alamat|tembusan)(?:\.|\b)/iu', $line);
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

    private function parseDate(?string $value): ?string
    {
        $value = $this->stripLeadingSeparator($value);
        $value = preg_replace('/(?<=\d)(?=[[:alpha:]])/u', ' ', $value);
        $value = preg_replace('/(?<=[[:alpha:]])(?=\d)/u', ' ', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value));

        if ($value === '') {
            return null;
        }

        $months = [
            'januari' => 'January',
            'jan' => 'January',
            'februari' => 'February',
            'feb' => 'February',
            'maret' => 'March',
            'mar' => 'March',
            'april' => 'April',
            'apr' => 'April',
            'mei' => 'May',
            'juni' => 'June',
            'jun' => 'June',
            'juli' => 'July',
            'jul' => 'July',
            'agustus' => 'August',
            'agu' => 'August',
            'september' => 'September',
            'sep' => 'September',
            'oktober' => 'October',
            'okt' => 'October',
            'november' => 'November',
            'nov' => 'November',
            'desember' => 'December',
            'des' => 'December',
        ];

        $normalized = preg_replace_callback(
            '/\b('.implode('|', array_map('preg_quote', array_keys($months))).')\b/i',
            fn (array $matches) => $months[strtolower($matches[1])] ?? $matches[1],
            $value
        );

        try {
            if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}$/', $normalized)) {
                return Carbon::createFromFormat(str_contains($normalized, '/') ? 'd/m/Y' : 'd-m-Y', $normalized)->format('Y-m-d');
            }

            return Carbon::parse($normalized)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
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

    private function findFiles(string $directory, string $extension): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === strtolower($extension)) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
