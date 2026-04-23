<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->string('nomor_st')->nullable()->after('tanggal_nd');
            $table->date('tanggal_st')->nullable()->after('nomor_st');
            $table->string('st_pdf_path')->nullable()->after('nd_pdf_uploaded_at');
            $table->longText('st_pdf_ocr_text')->nullable()->after('st_pdf_path');
            $table->string('st_pdf_ocr_engine')->nullable()->after('st_pdf_ocr_text');
            $table->timestamp('st_pdf_ocr_at')->nullable()->after('st_pdf_ocr_engine');
            $table->timestamp('st_pdf_uploaded_at')->nullable()->after('st_pdf_ocr_at');
        });
    }

    public function down(): void
    {
        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_st',
                'tanggal_st',
                'st_pdf_path',
                'st_pdf_ocr_text',
                'st_pdf_ocr_engine',
                'st_pdf_ocr_at',
                'st_pdf_uploaded_at',
            ]);
        });
    }
};
