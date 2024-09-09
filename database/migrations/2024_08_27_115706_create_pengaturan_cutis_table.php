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
        Schema::create('pengaturan_cutis', function (Blueprint $table) {
            $table->id();
            $table->integer('defualt_cuti')->nullable()->default(6);
            $table->string('reset_cuti')->nullable();
            $table->date('tanggal_reset')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_cutis');
    }
};
