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
            $table->string('waktu_pelaksanaan_text')->nullable()->after('waktu_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->dropColumn('waktu_pelaksanaan_text');
        });
    }
};
