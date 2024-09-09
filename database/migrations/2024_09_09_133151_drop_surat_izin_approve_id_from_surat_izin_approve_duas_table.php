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
        Schema::table('surat_izin_approve_duas', function (Blueprint $table) {
            $table->dropForeign(['surat_izin_approve_id']); // Drop foreign key constraint
            $table->dropColumn('surat_izin_approve_id');    // Drop the column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_izin_approve_duas', function (Blueprint $table) {
            $table->foreignId('surat_izin_approve_id')
                ->nullable()
                ->constrained('surat_izin_approves')
                ->cascadeOnDelete();
        });
    }
};
