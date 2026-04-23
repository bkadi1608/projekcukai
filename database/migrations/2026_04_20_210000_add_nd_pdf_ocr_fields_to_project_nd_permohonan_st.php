<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->longText('nd_pdf_ocr_text')->nullable()->after('nd_pdf_path');
            $table->string('nd_pdf_ocr_engine')->nullable()->after('nd_pdf_ocr_text');
            $table->timestamp('nd_pdf_ocr_at')->nullable()->after('nd_pdf_ocr_engine');
        });
    }

    public function down(): void
    {
        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->dropColumn([
                'nd_pdf_ocr_text',
                'nd_pdf_ocr_engine',
                'nd_pdf_ocr_at',
            ]);
        });
    }
};
