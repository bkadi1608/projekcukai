<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('jenis_permohonan')->nullable()->after('user_id');
            $table->string('no_surat_permohonan')->nullable()->after('jenis_permohonan');
            $table->date('tgl_surat_permohonan')->nullable()->after('no_surat_permohonan');
            $table->string('hal_surat_permohonan')->nullable()->after('tgl_surat_permohonan');
            $table->text('pengirim')->nullable()->after('hal_surat_permohonan');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_permohonan',
                'no_surat_permohonan',
                'tgl_surat_permohonan',
                'hal_surat_permohonan',
                'pengirim',
            ]);
        });
    }
};
