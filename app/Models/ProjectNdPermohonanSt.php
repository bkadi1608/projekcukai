<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectNdPermohonanSt extends Model
{
    protected $table = 'project_nd_permohonan_st';

    protected $fillable = [
        'project_id',
        'nomor_nd',
        'tanggal_nd',
        'nomor_st',
        'tanggal_st',
        'yth',
        'dari',
        'sifat',
        'hal_nd',
        'kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_pelaksanaan_text',
        'tanggal_pelaksanaan',
        'waktu_mulai',
        'waktu_selesai',
        'waktu_pelaksanaan_text',
        'word_generated_at',
        'nd_pdf_path',
        'nd_pdf_ocr_text',
        'nd_pdf_ocr_engine',
        'nd_pdf_ocr_at',
        'nd_pdf_uploaded_at',
        'st_pdf_path',
        'st_pdf_ocr_text',
        'st_pdf_ocr_engine',
        'st_pdf_ocr_at',
        'st_pdf_uploaded_at',
        'tempat',
        'alamat',
        'penandatangan',
        'tembusan',
    ];

    protected $casts = [
        'tanggal_nd' => 'date',
        'tanggal_st' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_pelaksanaan' => 'date',
        'word_generated_at' => 'datetime',
        'nd_pdf_ocr_at' => 'datetime',
        'nd_pdf_uploaded_at' => 'datetime',
        'st_pdf_ocr_at' => 'datetime',
        'st_pdf_uploaded_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function pegawais()
    {
        return $this->belongsToMany(Pegawai::class, 'project_nd_permohonan_st_pegawai')
            ->withPivot('urutan')
            ->withTimestamps()
            ->orderBy('project_nd_permohonan_st_pegawai.urutan');
    }

    public function tujuans()
    {
        return $this->belongsToMany(TujuanSt::class, 'project_nd_permohonan_st_tujuan')
            ->withPivot('urutan')
            ->withTimestamps()
            ->orderBy('project_nd_permohonan_st_tujuan.urutan');
    }
}
