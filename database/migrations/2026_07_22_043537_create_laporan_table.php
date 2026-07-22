<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->string('skpd')->nullable();
            $table->string('jenis');
            $table->string('periode');
            $table->date('tanggal_dibuat');
            $table->string('format')->default('DOCX');
            $table->string('path_file')->nullable();
            $table->unsignedBigInteger('ukuran_bytes')->nullable();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};