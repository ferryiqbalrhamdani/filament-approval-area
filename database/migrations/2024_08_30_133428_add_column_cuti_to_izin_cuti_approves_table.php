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
        Schema::table('izin_cuti_approves', function (Blueprint $table) {
            $table->foreignId('user_cuti_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('pilihan_cuti')->nullable();
            $table->string('lama_cuti')->nullable();
            $table->date('mulai_cuti')->nullable();
            $table->date('sampai_cuti')->nullable();
            $table->text('pesan_cuti')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_cuti_approves', function (Blueprint $table) {
            $table->dropColumn([
                'user_cuti_id',
                'company_id',
                'pilihan_cuti',
                'lama_cuti',
                'mulai_cuti',
                'sampai_cuti',
                'pesan_cuti',
            ]);
        });
    }
};
