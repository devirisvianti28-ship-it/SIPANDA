<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('skpd')->nullable();
            $table->string('jenis');
            $table->string('periode');
            $table->date('tanggal_dibuat');
            $table->string('format')->default('DOCX');
            $table->string('path_file');
            $table->unsignedBigInteger('ukuran_bytes')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};