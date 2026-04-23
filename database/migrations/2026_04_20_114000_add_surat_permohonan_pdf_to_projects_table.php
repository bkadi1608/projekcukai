<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('surat_permohonan_pdf_path')->nullable()->after('pengirim');
            $table->longText('surat_permohonan_ocr_text')->nullable()->after('surat_permohonan_pdf_path');
            $table->string('surat_permohonan_ocr_engine')->nullable()->after('surat_permohonan_ocr_text');
            $table->timestamp('surat_permohonan_ocr_at')->nullable()->after('surat_permohonan_ocr_engine');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'surat_permohonan_pdf_path',
                'surat_permohonan_ocr_text',
                'surat_permohonan_ocr_engine',
                'surat_permohonan_ocr_at',
            ]);
        });
    }
};
