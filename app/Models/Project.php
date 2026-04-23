<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // ⬅️ tambahin ini

class Project extends Model
{
    protected $fillable = [
        'jenis_permohonan',
        'no_surat_permohonan',
        'tgl_surat_permohonan',
        'hal_surat_permohonan',
        'pengirim',
        'nama_project',
        'perusahaan',
        'tanggal',
        'keterangan',
        'surat_permohonan_pdf_path',
        'surat_permohonan_ocr_text',
        'surat_permohonan_ocr_engine',
        'surat_permohonan_ocr_at',
        'user_id' // ⬅️ tambahin juga ini
    ];

    protected $casts = [
        'tgl_surat_permohonan' => 'date',
        'tanggal' => 'date',
        'surat_permohonan_ocr_at' => 'datetime',
    ];

    // 🔥 TAMBAH DI SINI
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ndPermohonanSt()
    {
        return $this->hasOne(ProjectNdPermohonanSt::class);
    }

    public function getNamaPabrikAttribute(): string
    {
        $value = trim((string) ($this->pengirim ?: $this->perusahaan));

        if ($value === '') {
            return 'Tidak ada pabrik tujuan';
        }

        if (str_contains($value, ' - ')) {
            $parts = array_map('trim', explode(' - ', $value));
            $value = end($parts) ?: $value;
        }

        $value = preg_replace('/\s+/', ' ', $value);
        $value = mb_convert_case(mb_strtolower($value), MB_CASE_TITLE, 'UTF-8');

        foreach (['Pt', 'Cv', 'Pr', 'Pd', 'Ud', 'Ksu', 'Kud'] as $word) {
            $value = preg_replace('/\b'.$word.'\b/u', mb_strtoupper($word), $value);
        }

        return preg_replace('/(?<!\w)Tbk\.?(?!\w)/u', 'Tbk.', $value);
    }

    public function getLabelProjectTanpaPabrikAttribute(): ?string
    {
        if (filled($this->pengirim) || filled($this->perusahaan)) {
            return null;
        }

        $namaProject = trim((string) $this->nama_project);
        $jenisPermohonan = trim((string) $this->jenis_permohonan);

        if ($namaProject === '') {
            return null;
        }

        $prefix = $jenisPermohonan !== '' ? $jenisPermohonan.' - ' : '';
        $label = $prefix !== '' && str_starts_with($namaProject, $prefix)
            ? substr($namaProject, strlen($prefix))
            : $namaProject;

        $label = trim((string) $label);

        return $label !== '' && $label !== 'Tanpa Pabrik Tujuan'
            ? $label
            : null;
    }
}
