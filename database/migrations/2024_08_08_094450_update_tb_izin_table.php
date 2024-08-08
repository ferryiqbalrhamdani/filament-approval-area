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
        Schema::table('tb_izin', function (Blueprint $table) {
            $table->string('status_izin')->nullable(); // Sesuaikan `some_existing_column` dengan kolom yang ada pada tabel `tb_izin`
            $table->dropColumn(['status', 'status_dua', 'status_tiga']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_izin', function (Blueprint $table) {
            $table->dropColumn('status_izin');
            $table->integer('status')->default(0);
            $table->integer('status_dua')->nullable();
            $table->integer('status_tiga')->nullable();
        });
    }
};
