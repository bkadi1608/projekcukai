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
        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->timestamp('word_generated_at')->nullable()->after('waktu_pelaksanaan_text');
            $table->string('nd_pdf_path')->nullable()->after('word_generated_at');
            $table->timestamp('nd_pdf_uploaded_at')->nullable()->after('nd_pdf_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->dropColumn([
                'word_generated_at',
                'nd_pdf_path',
                'nd_pdf_uploaded_at',
            ]);
        });
    }
};
