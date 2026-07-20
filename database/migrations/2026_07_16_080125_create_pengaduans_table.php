<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaduans', 'tracking_id')) {
                $table->string('tracking_id')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'tanggal')) {
                $table->date('tanggal')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'pelapor')) {
                $table->string('pelapor')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'judul')) {
                $table->string('judul')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'isi_awal')) {
                $table->text('isi_awal')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'isi_akhir')) {
                $table->text('isi_akhir')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'skpd')) {
                $table->string('skpd')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'status_laporan_raw')) {
                $table->string('status_laporan_raw')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'status')) {
                $table->string('status')->default('Belum Selesai');
            }
            if (! Schema::hasColumn('pengaduans', 'tanggapan')) {
                $table->text('tanggapan')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'keterangan')) {
                $table->string('keterangan')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'sudah_ditanggapi')) {
                $table->boolean('sudah_ditanggapi')->default(false);
            }
            if (! Schema::hasColumn('pengaduans', 'excel_synced_at')) {
                $table->timestamp('excel_synced_at')->nullable();
            }
            if (! Schema::hasColumn('pengaduans', 'word_synced_at')) {
                $table->timestamp('word_synced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        
    }
};