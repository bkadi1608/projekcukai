<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    protected $fillable = [
        'no',
        'nama_pabrik_sac',
        'nama_perusahaan',
        'jenis_perusahaan',
        'jenis_usaha',
        'jenis_bkc',
        'npwp',
        'nib',
        'nppbkc_10',
        'nppbkc_22',
        'nilku',
        'no_kep_nppbkc',
        'tgl_kep_nppbkc',
        'pkp',
        'no_pkp',
        'status',
        'status_beku_cabut',
        'nomor_kep',
        'tgl_kep',
        'jenis_bkc_golongan',
        'nitku_utama',
        'alamat_pabrik_utama',
        'nitku_cabang_1',
        'alamat_cabang_1',
        'nitku_cabang_2',
        'alamat_cabang_2',
        'nitku_cabang_3',
        'alamat_cabang_3',
        'lokasi_fix',
        'nama_pemilik',
        'npwp_pemilik',
        'nik_pemilik',
        'alamat_pemilik',
        'lokasi_gmaps',
        'longlat',
        'kecamatan',
        'profil',
        'nppbkc_perusahaan',
        'nppbkc_validasi'
    ];
}