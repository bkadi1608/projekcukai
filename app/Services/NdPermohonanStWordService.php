<?php

namespace App\Services;

use App\Models\ProjectNdPermohonanSt;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;
use ZipArchive;

class NdPermohonanStWordService
{
    private const W_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function make(ProjectNdPermohonanSt $nd): string
    {
        $template = storage_path('app/templates/nd-permohonan-st.docx');

        if (! file_exists($template)) {
            throw new \RuntimeException('Template ND Permohonan ST tidak ditemukan.');
        }

        $path = storage_path('app/temp/nd-permohonan-st-'.$nd->id.'-'.Str::random(8).'.docx');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        copy($template, $path);

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Tidak dapat membuka template Word.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', self::W_NS);

        $this->fillDocument($dom, $xpath, $nd);

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        return $path;
    }

    private function fillDocument(DOMDocument $dom, DOMXPath $xpath, ProjectNdPermohonanSt $nd): void
    {
        $project = $nd->project;
        $body = $xpath->query('//w:body')->item(0);
        $elements = $this->bodyElements($body);

        $nomorNd = $nd->nomor_nd ?: '[@NomorND]';
        $tanggalNd = $nd->tanggal_nd ? $this->formatDate($nd->tanggal_nd) : '[@TanggalND]';
        $tglSurat = $project->tgl_surat_permohonan ? $this->formatDate($project->tgl_surat_permohonan) : '-';
        $pengirim = $this->formatPengirim(
            $project->pengirim
                ?: $project->perusahaan
                ?: $project->nama_project
        );

        $this->setParagraphText($dom, $xpath, $elements[1], 'NOMOR '.$nomorNd);

        $this->setTableCellText($dom, $xpath, $elements[4], 0, 2, $nd->yth ?: 'Kepala Kantor');
        $this->setTableCellText($dom, $xpath, $elements[4], 1, 2, $nd->dari ?: '-');
        $this->setTableCellText($dom, $xpath, $elements[4], 2, 2, $nd->sifat ?: 'Segera');
        $this->setTableCellText($dom, $xpath, $elements[4], 3, 2, $nd->hal_nd ?: '-');
        $this->setTableCellText($dom, $xpath, $elements[4], 4, 2, $tanggalNd);

        $this->setParagraphText(
            $dom,
            $xpath,
            $elements[6],
            $this->buildPembuka($project, $pengirim, $tglSurat)
        );

        $this->fillPegawaiTable($dom, $xpath, $elements[7], $nd);

        $this->setParagraphText(
            $dom,
            $xpath,
            $elements[8],
            'untuk '.($nd->kegiatan ?: 'melaksanakan kegiatan').' yang akan dilaksanakan pada:'
        );

        $this->setTableCellText($dom, $xpath, $elements[10], 0, 2, $nd->tanggal_pelaksanaan_text ?: '-');
        $this->setTableCellText($dom, $xpath, $elements[10], 1, 2, $nd->waktu_pelaksanaan_text ?: '-');
        $this->setTableCellText($dom, $xpath, $elements[10], 2, 2, $this->formatTempat($nd));

        $this->setTableCellText($dom, $xpath, $elements[19], 0, 1, 'Ditandatangani secara elektronik');
        $this->setTableCellText($dom, $xpath, $elements[19], 1, 1, $nd->penandatangan ?: $nd->dari ?: '');

        $this->setParagraphText($dom, $xpath, $elements[23], $nd->tembusan ?: 'Kepala Subbagian Umum');
    }

    private function fillPegawaiTable(DOMDocument $dom, DOMXPath $xpath, DOMElement $table, ProjectNdPermohonanSt $nd): void
    {
        $rows = $xpath->query('./w:tr', $table);
        $templateRow = $rows->item(1)?->cloneNode(true) ?: $rows->item(0)->cloneNode(true);

        while ($rows->length > 1) {
            $table->removeChild($rows->item(1));
            $rows = $xpath->query('./w:tr', $table);
        }

        $pegawais = $nd->pegawais->values();

        if ($pegawais->isEmpty()) {
            $row = $templateRow->cloneNode(true);
            $this->setRowTexts($dom, $xpath, $row, ['-', 'Belum ada pegawai', '-', '-']);
            $table->appendChild($row);

            return;
        }

        foreach ($pegawais as $index => $pegawai) {
            $row = $templateRow->cloneNode(true);
            $this->setRowTexts($dom, $xpath, $row, [
                (string) ($index + 1),
                trim($pegawai->nm_pegawai." /\n".$pegawai->nip),
                (string) $pegawai->pangkat_golongan,
                (string) $pegawai->jabatan,
            ]);
            $table->appendChild($row);
        }
    }

    private function setRowTexts(DOMDocument $dom, DOMXPath $xpath, DOMElement $row, array $texts): void
    {
        foreach ($xpath->query('./w:tc', $row) as $index => $cell) {
            $this->setCellText($dom, $xpath, $cell, $texts[$index] ?? '');
        }
    }

    private function setTableCellText(DOMDocument $dom, DOMXPath $xpath, DOMElement $table, int $rowIndex, int $cellIndex, string $text): void
    {
        $row = $xpath->query('./w:tr', $table)->item($rowIndex);
        $cell = $row ? $xpath->query('./w:tc', $row)->item($cellIndex) : null;

        if ($cell instanceof DOMElement) {
            $this->setCellText($dom, $xpath, $cell, $text);
        }
    }

    private function setCellText(DOMDocument $dom, DOMXPath $xpath, DOMElement $cell, string $text): void
    {
        $paragraphs = $xpath->query('./w:p', $cell);
        $paragraph = $paragraphs->item(0);

        if (! $paragraph instanceof DOMElement) {
            $paragraph = $dom->createElementNS(self::W_NS, 'w:p');
            $cell->appendChild($paragraph);
        }

        while ($paragraphs->length > 1) {
            $cell->removeChild($paragraphs->item(1));
            $paragraphs = $xpath->query('./w:p', $cell);
        }

        $this->setParagraphText($dom, $xpath, $paragraph, $text);
    }

    private function setParagraphText(DOMDocument $dom, DOMXPath $xpath, DOMElement $paragraph, string $text): void
    {
        $runProperties = $this->firstRunProperties($xpath, $paragraph);

        foreach (iterator_to_array($paragraph->childNodes) as $child) {
            if ($child instanceof DOMElement && $child->localName === 'pPr') {
                $this->removeNumbering($xpath, $child);
                continue;
            }

            $paragraph->removeChild($child);
        }

        $lines = preg_split("/\r\n|\n|\r/", $text);

        foreach ($lines as $index => $line) {
            $run = $dom->createElementNS(self::W_NS, 'w:r');

            if ($runProperties instanceof DOMElement) {
                $run->appendChild($runProperties->cloneNode(true));
            }

            if ($index > 0) {
                $run->appendChild($dom->createElementNS(self::W_NS, 'w:br'));
            }

            $textNode = $dom->createElementNS(self::W_NS, 'w:t');
            $textNode->setAttribute('xml:space', 'preserve');
            $textNode->appendChild($dom->createTextNode($line));
            $run->appendChild($textNode);
            $paragraph->appendChild($run);
        }
    }

    private function firstRunProperties(DOMXPath $xpath, DOMElement $paragraph): ?DOMElement
    {
        $properties = $xpath->query('.//w:rPr', $paragraph)->item(0);

        return $properties instanceof DOMElement ? $properties : null;
    }

    private function removeNumbering(DOMXPath $xpath, DOMElement $paragraphProperties): void
    {
        foreach (iterator_to_array($xpath->query('./w:numPr', $paragraphProperties)) as $numbering) {
            $paragraphProperties->removeChild($numbering);
        }
    }

    private function bodyElements($body): array
    {
        return collect(iterator_to_array($body->childNodes))
            ->filter(fn ($node) => $node instanceof DOMElement)
            ->values()
            ->all();
    }

    private function formatDate($date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $date->format('d').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }

    private function buildPembuka($project, string $pengirim, string $tglSurat): string
    {
        if (blank($project->pengirim) && blank($project->perusahaan) && blank($project->no_surat_permohonan) && blank($project->hal_surat_permohonan)) {
            return 'Sehubungan dengan kebutuhan penugasan, dengan hormat kami sampaikan permohonan penerbitan surat tugas kepada pegawai sebagai berikut:';
        }

        return 'Sehubungan dengan surat dari '.$pengirim
            .' nomor '.($project->no_surat_permohonan ?: '-')
            .' tanggal '.$tglSurat
            .' hal '.($project->hal_surat_permohonan ?: '-')
            .', dengan hormat kami sampaikan permohonan penerbitan surat tugas kepada pegawai sebagai berikut:';
    }

    private function formatTempat(ProjectNdPermohonanSt $nd): string
    {
        if ($nd->relationLoaded('tujuans') && $nd->tujuans->isNotEmpty()) {
            if ($nd->tujuans->count() === 1) {
                $tujuan = $nd->tujuans->first();

                return trim($tujuan->nama_tujuan."\n".$tujuan->alamat_tujuan);
            }

            return $nd->tujuans
                ->values()
                ->map(fn ($tujuan, $index) => trim(($index + 1).'. '.$tujuan->nama_tujuan."\n".$tujuan->alamat_tujuan))
                ->implode("\n");
        }

        return trim(($nd->tempat ?: '-')."\n".($nd->alamat ?: ''));
    }

    private function formatPengirim(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || strcasecmp($value, 'NULL') === 0) {
            return '-';
        }

        if (str_contains($value, ' - ')) {
            $parts = array_map('trim', explode(' - ', $value));
            $value = end($parts) ?: $value;
        }

        $value = preg_replace('/\s+/', ' ', $value);
        $value = mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');

        $upperWords = ['Pt', 'Cv', 'Pr', 'Pd', 'Ud', 'Ksu', 'Kud'];

        foreach ($upperWords as $word) {
            $value = preg_replace('/\b'.$word.'\b/u', mb_strtoupper($word), $value);
        }

        return preg_replace('/(?<!\w)Tbk\.?(?!\w)/u', 'Tbk.', $value);
    }
}
