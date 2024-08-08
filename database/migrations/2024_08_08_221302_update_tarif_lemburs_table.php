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
        Schema::table('tarif_lemburs', function (Blueprint $table) {
            // Ubah kolom yang ada
            $table->float('tarif_lembur_perjam', 12, 2)->default(0)->change();
            $table->float('uang_makan', 12, 2)->default(0)->change();

            // Tambahkan kolom baru
            $table->float('tarif_lumsum', 12, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarif_lemburs', function (Blueprint $table) {
            // Kembalikan perubahan jika migration di-rollback
            $table->float('tarif_lembur_perjam', 12, 2)->nullable()->change();
            $table->float('uang_makan', 12, 2)->nullable()->change();

            // Hapus kolom baru
            $table->dropColumn('tarif_lumsum');
        });
    }
};
