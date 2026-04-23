<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('perusahaans', function (Blueprint $table) {
        $table->id();

        $table->string('no')->nullable();
        $table->string('nama_pabrik_sac')->nullable();
        $table->string('nama_perusahaan')->nullable();
        $table->string('jenis_perusahaan')->nullable();
        $table->string('jenis_usaha')->nullable();
        $table->string('jenis_bkc')->nullable();

        $table->string('npwp')->nullable();
        $table->string('nib')->nullable();
        $table->string('nppbkc_10')->nullable();
        $table->string('nppbkc_22')->nullable();

        $table->string('nilku')->nullable();
        $table->string('no_kep_nppbkc')->nullable();
        $table->date('tgl_kep_nppbkc')->nullable();

        $table->string('pkp')->nullable();
        $table->string('no_pkp')->nullable();

        $table->string('status')->nullable();
        $table->string('status_beku_cabut')->nullable();

        $table->string('nomor_kep')->nullable();
        $table->date('tgl_kep')->nullable();

        $table->string('jenis_bkc_golongan')->nullable();

        $table->string('nitku_utama')->nullable();

        $table->text('alamat_pabrik_utama')->nullable();

        $table->string('nitku_cabang_1')->nullable();
        $table->text('alamat_cabang_1')->nullable();

        $table->string('nitku_cabang_2')->nullable();
        $table->text('alamat_cabang_2')->nullable();

        $table->string('nitku_cabang_3')->nullable();
        $table->text('alamat_cabang_3')->nullable();

        $table->text('lokasi_fix')->nullable();

        $table->string('nama_pemilik')->nullable();
        $table->string('npwp_pemilik')->nullable();
        $table->string('nik_pemilik')->nullable();
        $table->text('alamat_pemilik')->nullable();

        $table->text('lokasi_gmaps')->nullable();
        $table->string('longlat')->nullable();

        $table->string('kecamatan')->nullable();
        $table->text('profil')->nullable();

        $table->string('nppbkc_perusahaan')->nullable();
        $table->string('nppbkc_validasi')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perusahaans');
    }
};
