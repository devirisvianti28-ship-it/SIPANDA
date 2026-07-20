<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ganti kolom status dari enum (daftar tertutup: Pending/Diproses/Selesai)
        // jadi string biasa. Alasannya: data dari Supabase punya nilai status
        // yang lebih variatif (misal 'Belum Selesai') yang gak ada di enum lama,
        // dan aplikasi (blade) sebenarnya cuma butuh cek status === 'Selesai' vs bukan,
        // jadi string lebih aman daripada enum yang ketat.
        DB::statement("ALTER TABLE pengaduans MODIFY status VARCHAR(50) NOT NULL DEFAULT 'Belum Selesai'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pengaduans MODIFY status ENUM('Pending','Diproses','Selesai') NOT NULL DEFAULT 'Pending'");
    }
};