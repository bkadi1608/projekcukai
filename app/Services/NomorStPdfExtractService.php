<?php

namespace App\Services;

use App\Models\Pegawai;
use Carbon\Carbon;

class NomorStPdfExtractService
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
            'pegawai_ids' => $this->detectPegawaiIds($text),
        ];
    }

    private function parseFields(string $text): array
    {
        $normalized = trim(preg_replace('/[ \t]+/', ' ', str_replace("\r", "\n", $text)));
        $lines = collect(explode("\n", $normalized))
            ->map(fn ($line) => trim(preg_replace('/\s+/', ' ', $line)))
            ->filter()
            ->values();

        $nomor = $this->labeledValue($lines, ['nomor st', 'nomor surat tugas', 'nomor', 'no st', 'no'])
            ?? $this->matchFirst($normalized, [
                '/\bnomor\s+st\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bnomor\s+surat\s+tugas\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bnomor\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bno\.?\s+st\s*[:\-]?\s*([^\n\r]+)/iu',
                '/\bno\.?\s*[:\-]?\s*([^\n\r]+)/iu',
            ]);

        $tanggal = $this->labeledValue($lines, ['tanggal st', 'tanggal surat tugas', 'tgl st', 'tanggal', 'tgl'])
            ?? $this->matchFirst($normalized, [
                '/\btanggal\s+st\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btanggal\s+surat\s+tugas\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btanggal\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\btgl\.?\s*[:\-]?\s*([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\b([0-9]{1,2}\s*[[:alpha:]]+\.?\s*[0-9]{4})/iu',
                '/\b([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{4})/u',
            ]);

        return [
            'nomor_st' => $nomor ? $this->normalizeNomorSt($this->cleanValue($nomor)) : null,
            'tanggal_st' => $this->parseDate($tanggal),
        ];
    }

    private function detectPegawaiIds(string $text): array
    {
        $normalizedText = $this->normalizeForMatch($text);

        if ($normalizedText === '') {
            return [];
        }

        return Pegawai::query()
            ->get(['id', 'nm_pegawai', 'jabatan', 'jabatan_duk'])
            ->filter(function (Pegawai $pegawai) use ($normalizedText) {
                $jabatan = $this->normalizeForMatch($pegawai->jabatan);
                $jabatanDuk = $this->normalizeForMatch($pegawai->jabatan_duk);

                if (str_contains($jabatan, 'kepala kantor') || str_contains($jabatanDuk, 'kepala kantor')) {
                    return false;
                }

                $name = $this->normalizeForMatch($pegawai->nm_pegawai);

                return $name !== '' && str_contains($normalizedText, $name);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
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

    private function normalizeNomorSt(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        $value = preg_replace('/^ST\s*[-\/]?\s*/iu', '', $value);

        return 'ST-'.ltrim((string) $value, '-/');
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

    private function normalizeForMatch(?string $value): string
    {
        $value = mb_strtolower((string) $value, 'UTF-8');
        $value = preg_replace('/[^[:alnum:]\s]/u', ' ', $value);

        return trim((string) preg_replace('/\s+/', ' ', $value));
    }
}
