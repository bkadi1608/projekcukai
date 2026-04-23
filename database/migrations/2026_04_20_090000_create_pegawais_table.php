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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->date('ulang_tahun')->nullable();
            $table->string('nm_pegawai')->nullable();
            $table->string('nickname')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('pangkat_golongan')->nullable();
            $table->text('url_foto')->nullable();
            $table->string('nip')->nullable();
            $table->string('email_kemenkeu')->nullable();
            $table->string('grade')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->date('tgl_ulang_tahun')->nullable();
            $table->string('umur')->nullable();
            $table->string('bulan_if')->nullable();
            $table->string('bulan')->nullable();
            $table->string('urutan')->nullable();
            $table->string('jabatan2')->nullable();
            $table->string('nama_pegawai')->nullable();
            $table->string('atasan')->nullable();
            $table->string('seksi')->nullable();
            $table->string('nomor')->nullable();
            $table->string('jabatan_duk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
