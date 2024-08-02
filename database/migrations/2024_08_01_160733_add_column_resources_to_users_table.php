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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->renameColumn('name', 'first_name');
            $table->string('last_name')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->cascadeOnDelete();
            $table->enum('jk', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('status_karyawan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
            $table->renameColumn('first_name', 'name');
            $table->dropColumn('last_name');
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->dropForeign(['office_id']);
            $table->dropColumn('office_id');
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
            $table->dropColumn('jk');
            $table->dropColumn('status_karyawan');
        });
    }
};
