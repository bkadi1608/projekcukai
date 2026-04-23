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
            $table->date('tanggal_mulai')->nullable()->after('kegiatan');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            $table->text('tanggal_pelaksanaan_text')->nullable()->after('tanggal_selesai');
        });

        Schema::create('project_nd_permohonan_st_tujuan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_nd_permohonan_st_id');
            $table->unsignedBigInteger('tujuan_st_id');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->foreign('project_nd_permohonan_st_id', 'nd_st_tujuan_nd_id_fk')
                ->references('id')
                ->on('project_nd_permohonan_st')
                ->cascadeOnDelete();
            $table->foreign('tujuan_st_id', 'nd_st_tujuan_tujuan_id_fk')
                ->references('id')
                ->on('tujuan_sts')
                ->cascadeOnDelete();
            $table->unique(['project_nd_permohonan_st_id', 'tujuan_st_id'], 'nd_st_tujuan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_nd_permohonan_st_tujuan');

        Schema::table('project_nd_permohonan_st', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_mulai',
                'tanggal_selesai',
                'tanggal_pelaksanaan_text',
            ]);
        });
    }
};
