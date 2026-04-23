<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $fillable = [
        'ulang_tahun',
        'nm_pegawai',
        'nickname',
        'jabatan',
        'pangkat_golongan',
        'url_foto',
        'nip',
        'email_kemenkeu',
        'grade',
        'jenis_kelamin',
        'tanggal_lahir',
        'tgl_ulang_tahun',
        'umur',
        'bulan_if',
        'bulan',
        'urutan',
        'jabatan2',
        'nama_pegawai',
        'atasan',
        'seksi',
        'nomor',
        'jabatan_duk',
    ];

    protected $casts = [
        'ulang_tahun' => 'date',
        'tanggal_lahir' => 'date',
        'tgl_ulang_tahun' => 'date',
    ];
}
