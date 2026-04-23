<?php

namespace App\Services;

use Carbon\Carbon;

class NdPermohonanStPdfExtractService
{
    public function __construct(
        private PdfTextExtractService $pdfTextExtractService
    ) {
    }

    public function extract(string $path): array
    {
        $result = $this->pdfTextExtractService->extract($path);
        $text = trim((string) ($result['text'] ?? ''));

        return [
            'text' => $text,
            'engine' => $result['engine'] ?? null,
            'fields' => $this->parseFields($text),
        ];
    }

    private function parseFields(string $text): array
    {
        $normalized = trim(preg_replace('/[ \t]+/', ' ', str_replace("\r", "\n", $text)));
        $lines = collect(explode("\n", $normalized))
            ->map(fn ($line) => trim(preg_replace('/\s+/', ' ', $line)))
            ->filter()
            ->values();

        $nomor = $this->labeledValue($lines, ['nomor nd', 'nomor', 'no nd', 'no'])
            ?? $this->matchFirst($normalized, [
                '/\bnomor\s+nd\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bnomor\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bno\.?\s+nd\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bno\.?\s*[:\-]?\s*([^\n\r]+)/iu',
            ]);

        $tanggal = $this->labeledValue($lines, ['tanggal nd', 'tgl nd', 'tanggal', 'tgl'])
            ?? $this->matchFirst($normalized, [
                '/\btanggal\s+nd\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btanggal\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btgl\.?\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\b([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\b([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{4})/u',
            ]);

        return [
            'nomor_nd' => $nomor ? $this->normalizeNomorNd($this->cleanValue($nomor)) : null,
            'tanggal_nd' => $this->parseDate($tanggal),
        ];
    }

    private function labeledValue($lines, array $labels): ?string
    {
        usort($labels, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($lines as $index => $line) {
            foreach ($labels as $label) {
                $pattern = '/^'.preg_quote($label, '/').'(?:\.)?(?=\W|$)\s*[:\-]?\s*(.*)$/iu';

                if (! preg_match($pattern, $line, $matches)) {
                    continue;
                }

                $value = $this->stripLeadingSeparator(trim($matches[1] ?? ''));

                if ($value === '') {
                    $value = $this->stripLeadingSeparator($lines[$index + 1] ?? '');
                }

                return $value !== '' ? $value : null;
            }
        }

        return null;
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

    private function cleanValue(string $value): string
    {
        $value = $this->stripLeadingSeparator($value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }

    private function normalizeNomorNd(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        $value = preg_replace('/^ND\s*[-\/]?\s*/iu', '', $value);

        return 'ND-'.ltrim((string) $value, '-/');
    }

    private function stripLeadingSeparator(?string $value): string
    {
        return trim((string) preg_replace('/^[\s:;\-\.]+/u', '', (string) $value));
    }

    private function parseDate(?string $value): ?string
    {
        $value = $this->stripLeadingSeparator($value);
        $value = preg_replace('/(?<=\d)(?=[[:alpha:]])/u', ' ', $value);
        $value = preg_replace('/(?<=[[:alpha:]])(?=\d)/u', ' ', $value);
        $value = trim((string) preg_replace('/\s+/', ' ', $value));

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
}
