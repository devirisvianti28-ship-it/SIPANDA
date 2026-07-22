<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     * Aman dijalankan berulang: setiap kolom dicek dulu sebelum ditambahkan,
     * jadi tidak bentrok dengan migrasi profil/foto yang sudah ada di project.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip', 30)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['master_admin', 'pengelola', 'kepala_dinas'])
                      ->default('pengelola')
                      ->after('nip');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['aktif', 'nonaktif'])
                      ->default('aktif')
                      ->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'role', 'status']);
        });
    }
};