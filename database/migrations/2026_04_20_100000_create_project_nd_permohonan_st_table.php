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
        Schema::create('project_nd_permohonan_st', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_nd')->nullable();
            $table->date('tanggal_nd')->nullable();
            $table->string('yth')->default('Kepala Kantor');
            $table->string('dari')->nullable();
            $table->string('sifat')->default('Segera');
            $table->text('hal_nd')->nullable();
            $table->text('kegiatan')->nullable();
            $table->date('tanggal_pelaksanaan')->nullable();
            $table->string('waktu_mulai')->nullable();
            $table->string('waktu_selesai')->nullable();
            $table->text('tempat')->nullable();
            $table->text('alamat')->nullable();
            $table->string('penandatangan')->nullable();
            $table->text('tembusan')->nullable();
            $table->timestamps();

            $table->unique('project_id');
        });

        Schema::create('project_nd_permohonan_st_pegawai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_nd_permohonan_st_id');
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->foreign('project_nd_permohonan_st_id', 'nd_st_pegawai_nd_id_fk')
                ->references('id')
                ->on('project_nd_permohonan_st')
                ->cascadeOnDelete();
            $table->foreign('pegawai_id', 'nd_st_pegawai_pegawai_id_fk')
                ->references('id')
                ->on('pegawais')
                ->cascadeOnDelete();
            $table->unique(['project_nd_permohonan_st_id', 'pegawai_id'], 'nd_st_pegawai_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_nd_permohonan_st_pegawai');
        Schema::dropIfExists('project_nd_permohonan_st');
    }
};
