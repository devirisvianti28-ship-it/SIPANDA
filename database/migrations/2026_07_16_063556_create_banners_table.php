<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->string('deskripsi', 255)->nullable();
            $table->string('gambar'); // path file di storage/app/public/banners
            $table->boolean('status')->default(true);   // aktif / nonaktif
            $table->boolean('utama')->default(false);   // banner utama di homepage
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};