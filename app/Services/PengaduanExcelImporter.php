<?php

namespace App\Services;

use App\Models\Pengaduan;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

/**
 * Import file Excel export dari LAPOR.go.id.
 *
 * Catatan penting soal file aslinya:
 * - Baris paling atas sheet bukan header tabel, tapi info umum
 *   (Judul Laporan, Nama Instansi, Tanggal Generate, dst).
 * - Header tabel yang sebenarnya (Tracking ID, Tanggal Laporan Masuk, ...)
 *   baru muncul beberapa baris ke bawah.
 * Makanya importer ini TIDAK asal baca baris ke-2, tapi nyari dulu
 * baris mana yang isinya "Tracking ID" di kolom pertama.
 *
 * PERFORMA: proses insert/update dilakukan lewat upsert() batch
 * (bukan query satu-satu per baris), supaya cepat walau koneksi
 * ke database cloud (Supabase) punya latency.
 *
 * CATATAN STATUS (Tanggapan & Keterangan):
 * - File Excel dari LAPOR.go.id TIDAK punya kolom Tanggapan/Keterangan.
 *   Kolom "Status Laporan" di Excel itu status internal LAPOR.go.id
 *   (Ditutup oleh Admin/Sistem, Diarsipkan, dll), BUKAN representasi
 *   dari "Selesai"/"Belum Selesai" yang dipakai di aplikasi ini.
 * - Karena itu, `status` TIDAK boleh ditebak/di-derive otomatis dari
 *   kolom "Status Laporan", DAN TIDAK boleh diisi default apapun
 *   ("Selesai" maupun "Belum Selesai"). Data baru dari Excel masuk
 *   dengan `status` = NULL (benar-benar kosong / belum ditentukan),
 *   dan admin yang menentukan sendiri lewat dropdown Keterangan di
 *   halaman Data Pengaduan. Kalau di-default ke 'Belum Selesai',
 *   itu keliatan seolah-olah sudah ada keputusan padahal belum.
 * - `status` juga sengaja TIDAK dimasukkan ke $updateColumns, supaya
 *   kalau file Excel yang sama di-import ulang, status yang sudah
 *   di-set manual oleh admin tidak ketimpa balik ke NULL/default.
 *
 * PENTING: kolom `status` di tabel `pengaduan` harus NULLABLE di
 * database. Kalau migration-nya masih NOT NULL, perlu bikin migration
 * baru untuk ubah jadi nullable, kalau tidak insert bakal error.
 */
class PengaduanExcelImporter
{
    /** Ukuran per batch upsert, biar query gak kepanjangan sekaligus. */
    private const CHUNK_SIZE = 200;

    /** @return array{inserted:int, updated:int, skipped:int} */
    public function import(string $path): array
    {
        set_time_limit(300);

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false); // array 0-based

        // 1) Cari baris header (kolom pertama = "Tracking ID")
        $headerIndex = null;
        foreach ($rows as $i => $row) {
            if (isset($row[0]) && trim((string) $row[0]) === 'Tracking ID') {
                $headerIndex = $i;
                break;
            }
        }

        if ($headerIndex === null) {
            throw new \RuntimeException('Baris header "Tracking ID" tidak ditemukan di file Excel ini.');
        }

        $header = $rows[$headerIndex];
        $colIndex = array_flip(array_map(fn ($h) => trim((string) $h), $header));

        $records = [];
        $skipped = 0;
        $now = now();

        for ($i = $headerIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $trackingId = trim((string) ($row[$colIndex['Tracking ID']] ?? ''));

            if ($trackingId === '' || ! ctype_digit($trackingId)) {
                $skipped++;
                continue;
            }

            $records[$trackingId] = [
                'tracking_id'                 => $trackingId,
                'tanggal'                     => $this->parseTanggal($row[$colIndex['Tanggal Laporan Masuk']] ?? null),
                'waktu'                       => $this->col($row, $colIndex, 'Waktu Laporan Masuk'),
                'pelapor'                     => $this->col($row, $colIndex, 'Nama Pelapor'),
                'klasifikasi'                 => $this->col($row, $colIndex, 'Klasifikasi Laporan'),
                'id_kategori'                 => $this->col($row, $colIndex, 'ID Kategori'),
                'kategori'                    => $this->col($row, $colIndex, 'Kategori'),
                'judul'                       => $this->col($row, $colIndex, 'Judul Laporan'),
                'isi_awal'                    => $row[$colIndex['Isi Laporan Awal']] ?? null,
                'isi_akhir'                   => $row[$colIndex['Isi Laporan Akhir']] ?? null,
                'tipe_laporan'                => $this->col($row, $colIndex, 'Tipe Laporan (Anonim/Rahasia)'),
                'sumber_laporan'              => $this->col($row, $colIndex, 'Sumber Laporan'),
                'instansi_induk'              => $this->col($row, $colIndex, 'Instansi Induk'),
                'id_instansi_terdisposisi'    => $this->col($row, $colIndex, 'ID Instansi Terdisposisi'),
                'skpd'                        => $this->col($row, $colIndex, 'Instansi Terdisposisi'),
                'status_laporan_raw'          => $this->col($row, $colIndex, 'Status Laporan'),
                // NULL = benar-benar kosong / belum ditentukan. Ini kolom yang
                // dikendalikan admin lewat dropdown Keterangan (atau importer
                // Word/PDF), bukan diturunkan dari data Excel.
                'status'                      => null,
                'alasan_tunda_arsip'          => $this->col($row, $colIndex, 'Alasan Tunda/Arsip'),
                'provinsi'                    => $this->col($row, $colIndex, 'Provinsi'),
                'kabupaten'                   => $this->col($row, $colIndex, 'Kota/Kabupaten'),
                'kecamatan'                   => $this->col($row, $colIndex, 'Kecamatan'),
                'kelurahan'                   => $this->col($row, $colIndex, 'Kelurahan'),
                'nomor_sk'                    => $this->col($row, $colIndex, 'Nomor SK'),
                'url_sk'                      => $this->col($row, $colIndex, 'Url SK'),
                'url_dokumen_laporan_tahunan' => $this->col($row, $colIndex, 'Url Dokumen Laporan Tahunan'),
                'laporan_setwapres'           => $this->col($row, $colIndex, 'Laporan yang Masuk melalui Kanal Aduan Setwapres'),
                'rating'                      => $this->col($row, $colIndex, 'Rating'),
                'excel_synced_at'             => $now,
                'updated_at'                  => $now,
                'created_at'                  => $now,
            ];
        }

        if (empty($records)) {
            return ['inserted' => 0, 'updated' => 0, 'skipped' => $skipped];
        }

        // Cek Tracking ID mana yang sudah ada, biar bisa hitung inserted vs updated.
        $trackingIds = array_keys($records);
        $existingIds = Pengaduan::whereIn('tracking_id', $trackingIds)
            ->pluck('tracking_id')
            ->all();

        $inserted = count($trackingIds) - count($existingIds);
        $updated = count($existingIds);

        // Kolom yang boleh di-overwrite kalau Tracking ID sudah ada (data mentah dari Excel).
        // 'status' SENGAJA TIDAK dimasukkan di sini — itu kolom yang dikendalikan admin
        // lewat dropdown Keterangan (dan/atau importer Word/PDF), jadi tidak boleh
        // ketimpa balik ke default 'Belum Selesai' setiap kali Excel yang sama di-import ulang.
        $updateColumns = [
            'tanggal', 'waktu', 'pelapor', 'klasifikasi', 'id_kategori', 'kategori',
            'judul', 'isi_awal', 'isi_akhir', 'tipe_laporan', 'sumber_laporan',
            'instansi_induk', 'id_instansi_terdisposisi', 'skpd', 'status_laporan_raw',
            'alasan_tunda_arsip', 'provinsi', 'kabupaten', 'kecamatan',
            'kelurahan', 'nomor_sk', 'url_sk', 'url_dokumen_laporan_tahunan',
            'laporan_setwapres', 'rating', 'excel_synced_at', 'updated_at',
        ];

        foreach (array_chunk(array_values($records), self::CHUNK_SIZE) as $chunk) {
            Pengaduan::upsert($chunk, ['tracking_id'], $updateColumns);
        }

        return ['inserted' => $inserted, 'updated' => $updated, 'skipped' => $skipped];
    }

    private function col(array $row, array $colIndex, string $key): ?string
    {
        if (! isset($colIndex[$key])) {
            return null;
        }

        $value = trim((string) ($row[$colIndex[$key]] ?? ''));

        return $value === '' ? null : $value;
    }

    private function parseTanggal(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            // Format di file: "3 Jan 2026"
            return Carbon::createFromFormat('j M Y', trim($value))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}