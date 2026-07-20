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
            if (!Schema::hasColumn('users', 'nama_lengkap')) {
                $table->string('nama_lengkap')->nullable();
            }
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip')->nullable();
            }
            if (!Schema::hasColumn('users', 'peran')) {
                $table->string('peran')->default('Pengguna');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('Aktif');
            }
            if (!Schema::hasColumn('users', 'login_terakhir')) {
                $table->timestamp('login_terakhir')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['nama_lengkap', 'peran', 'status', 'login_terakhir'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};