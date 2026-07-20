<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->time('waktu')->nullable()->after('tanggal');
            $table->string('klasifikasi')->nullable()->after('judul');
            $table->string('id_kategori')->nullable()->after('klasifikasi');
            $table->string('kategori')->nullable()->after('id_kategori');
            $table->string('tipe_laporan')->nullable()->after('isi_akhir');
            $table->string('sumber_laporan')->nullable()->after('tipe_laporan');
            $table->string('instansi_induk')->nullable()->after('sumber_laporan');
            $table->string('id_instansi_terdisposisi')->nullable()->after('instansi_induk');
            $table->string('alasan_tunda_arsip')->nullable()->after('status_laporan_raw');
            $table->string('provinsi')->nullable()->after('alasan_tunda_arsip');
            $table->string('kabupaten')->nullable()->after('provinsi');
            $table->string('kecamatan')->nullable()->after('kabupaten');
            $table->string('kelurahan')->nullable()->after('kecamatan');
            $table->string('nomor_sk')->nullable()->after('kelurahan');
            $table->text('url_sk')->nullable()->after('nomor_sk');
            $table->text('url_dokumen_laporan_tahunan')->nullable()->after('url_sk');
            $table->string('laporan_setwapres')->nullable()->after('url_dokumen_laporan_tahunan');
            $table->string('rating')->nullable()->after('laporan_setwapres');
        });
    }

    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table) {
            $table->dropColumn([
                'waktu', 'klasifikasi', 'id_kategori', 'kategori',
                'tipe_laporan', 'sumber_laporan', 'instansi_induk',
                'id_instansi_terdisposisi', 'alasan_tunda_arsip', 'provinsi',
                'kabupaten', 'kecamatan', 'kelurahan', 'nomor_sk',
                'url_sk', 'url_dokumen_laporan_tahunan', 'laporan_setwapres', 'rating',
            ]);
        });
    }
};