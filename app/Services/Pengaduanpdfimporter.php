<?php

namespace App\Services;

use App\Models\Pengaduan;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Import file PDF rekap manual (versi PDF dari REKAP_SP4N_LAPOR...).
 *
 * CATATAN PENTING:
 * PDF tidak punya struktur tabel seperti Word (baris/kolom).
 * Saat di-extract, semua teks jadi satu blok panjang, urutannya
 * TERGANTUNG cara PDF itu dibuat (export dari Word biasanya masih
 * urut per baris, tapi kalau PDF hasil scan/gambar, TIDAK akan terbaca
 * sama sekali — perlu OCR, importer ini tidak menangani itu).
 *
 * Strategi importer ini: cari baris yang mengandung Tracking ID
 * (angka 7-9 digit berdiri sendiri), lalu ambil kata kunci
 * "Sudah ada tanggapan" / "Belum ada tanggapan" untuk kolom Tanggapan,
 * dan kata kunci status ("selesai", "Sedang diproses", dst) untuk Keterangan
 * dari baris yang sama.
 *
 * Kalau hasil parsing berantakan, kirim PDF contohnya buat disesuaikan
 * pola regex-nya.
 */
class PengaduanPdfImporter
{
    /** @return array{updated:int, skipped:int, not_found:string[]} */
    public function import(string $path): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        // Pecah jadi baris, buang baris kosong
        $lines = array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text)),
            fn ($line) => $line !== ''
        );

        $updated = 0;
        $skipped = 0;
        $notFound = [];

        foreach ($lines as $line) {
            // Cari angka 7-9 digit yang berdiri sendiri (kemungkinan Tracking ID)
            if (! preg_match('/\b(\d{7,9})\b/', $line, $m)) {
                continue;
            }
            $trackingId = $m[1];

            // Kata kunci Tanggapan
            $tanggapan = null;
            if (stripos($line, 'sudah ada tanggapan') !== false) {
                $tanggapan = 'Sudah ada tanggapan';
            } elseif (stripos($line, 'belum ada tanggapan') !== false) {
                $tanggapan = 'Belum ada tanggapan';
            }

            // Kata kunci Keterangan / status
            $keterangan = null;
            foreach (['selesai', 'sedang diproses', 'diarsipkan', 'ditutup'] as $kw) {
                if (stripos($line, $kw) !== false) {
                    $keterangan = ucfirst($kw);
                    break;
                }
            }

            // Kalau baris ini cuma nyerempet angka doang tanpa konteks apapun, lewati
            if ($tanggapan === null && $keterangan === null) {
                $skipped++;
                continue;
            }

            $pengaduan = Pengaduan::where('tracking_id', $trackingId)->first();

            if (! $pengaduan) {
                $notFound[] = $trackingId;
                continue;
            }

            $pengaduan->update([
                'tanggapan' => $tanggapan ?? $pengaduan->tanggapan,
                'keterangan' => $keterangan ?? $pengaduan->keterangan,
                'sudah_ditanggapi' => $tanggapan
                    ? str_contains(strtolower($tanggapan), 'sudah')
                    : $pengaduan->sudah_ditanggapi,
                'word_synced_at' => now(), // dipakai bersama, artinya "sinkron dari dokumen rekap"
            ]);

            $updated++;
        }

        return ['updated' => $updated, 'skipped' => $skipped, 'not_found' => $notFound];
    }
}