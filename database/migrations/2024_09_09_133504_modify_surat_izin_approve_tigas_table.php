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
        Schema::table('surat_izin_approve_tigas', function (Blueprint $table) {
            // Drop the surat_izin_approve_dua_id column
            $table->dropForeign(['surat_izin_approve_dua_id']); // Drop foreign key constraint
            $table->dropColumn('surat_izin_approve_dua_id');    // Drop the column

            // Add the surat_izin_id column
            $table->foreignId('surat_izin_id')->nullable()->constrained('tb_izin')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_izin_approve_tigas', function (Blueprint $table) {
            // Reverse: Add surat_izin_approve_dua_id column back
            $table->foreignId('surat_izin_approve_dua_id')
                ->nullable()
                ->constrained('surat_izin_approve_duas')
                ->cascadeOnDelete();

            // Reverse: Drop surat_izin_id column
            $table->dropForeign(['surat_izin_id']);
            $table->dropColumn('surat_izin_id');
        });
    }
};
