<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduans', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id')->unique();
            $table->date('tanggal');
            $table->string('pelapor');
            $table->string('judul');
            $table->text('isi')->nullable();
            $table->foreignId('skpd_id')->nullable()->constrained('skpds')->nullOnDelete();
            $table->boolean('sudah_ditanggapi')->default(false);
            $table->enum('status', ['Pending', 'Diproses', 'Selesai'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};