<?php

namespace App\Services;

use App\Models\Pengaduan;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\IOFactory;

/**
 * Import file Word rekap bulanan (REKAP_SP4N_LAPOR_TAHUN_....docx).
 *
 * File ini isinya beberapa tabel terpisah (satu tabel per bulan).
 * Struktur tiap tabel:
 *   Row 0        : baris judul bulan (merged cell, mis. "BULAN JANUARI")
 *   Row 1        : header kolom -> No | Tanggal | Tracking ID | USER | JUDUL | STAKEHOLDER | TANGGAPAN | KETERANGAN
 *   Row 2..n     : data
 *
 * Importer ini HANYA mengambil 3 kolom: Tracking ID, TANGGAPAN, KETERANGAN,
 * lalu UPDATE record Pengaduan yang tracking_id-nya sudah ada dari hasil import Excel.
 * Tidak membuat record baru — kalau tracking_id tidak ditemukan, dicatat sebagai "not_found"
 * supaya bisa dicek manual oleh petugas (Excel-nya belum di-import, atau memang beda ID).
 *
 * CATATAN PERFORMA:
 * Seluruh dokumen Word di-parse dulu ke memori (tidak menyentuh DB sama sekali).
 * Setelah semua baris terkumpul, importer HANYA melakukan 2 query ke database:
 *   1. SELECT tracking_id yang ada (whereIn)
 *   2. UPDATE massal pakai CASE WHEN untuk semua baris yang match sekaligus
 * Ini supaya waktu proses tidak naik linear terhadap jumlah baris (menghindari
 * N+1 query yang sebelumnya bikin request timeout di file besar).
 */
class PengaduanWordImporter
{
    /** Maksimal baris yang dicek untuk menemukan baris header (mis. ada baris judul bulan sebelum header). */
    private const MAX_HEADER_SCAN_ROWS = 3;

    /** @return array{updated:int, skipped:int, skipped_details:array<int,array{table:string,row:int,cells:string[]}>, not_found:string[]} */
    public function import(string $path): array
    {
        $phpWord = IOFactory::load($path);

        // tracking_id => ['tanggapan' => ?string, 'keterangan' => ?string, 'status' => ?string, 'sudah_ditanggapi' => bool]
        $rows = [];
        $skipped = 0;
        $skippedDetails = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (! $element instanceof Table) {
                    continue;
                }

                $result = $this->collectRowsFromTable($element);
                $skipped += $result['skipped'];
                $skippedDetails = array_merge($skippedDetails, $result['skippedDetails']);

                // Kalau ada tracking_id yang muncul di lebih dari satu tabel/baris,
                // data paling terakhir yang menang (sama seperti perilaku sebelumnya).
                foreach ($result['rows'] as $trackingId => $data) {
                    $rows[$trackingId] = $data;
                }
            }
        }

        if (empty($rows)) {
            return ['updated' => 0, 'skipped' => $skipped, 'skipped_details' => $skippedDetails, 'not_found' => []];
        }

        $trackingIds = array_keys($rows);

        // Query #1: cari tracking_id mana saja yang beneran ada di DB.
        $existingIds = Pengaduan::whereIn('tracking_id', $trackingIds)
            ->pluck('tracking_id')
            ->all();

        $notFound = array_values(array_diff($trackingIds, $existingIds));

        if (empty($existingIds)) {
            return ['updated' => 0, 'skipped' => $skipped, 'skipped_details' => $skippedDetails, 'not_found' => $notFound];
        }

        // Query #2: update semua baris yang match sekaligus dalam satu statement.
        $this->bulkUpdate($rows, $existingIds);

        return [
            'updated' => count($existingIds),
            'skipped' => $skipped,
            'skipped_details' => $skippedDetails,
            'not_found' => $notFound,
        ];
    }

    /**
     * Parse satu tabel jadi array baris (tanpa menyentuh DB).
     *
     * @return array{rows: array<string, array{tanggapan: ?string, keterangan: ?string, status: ?string, sudah_ditanggapi: bool}>, skipped: int, skippedDetails: array<int,array{table:string,row:int,cells:string[]}>}
     */
    private function collectRowsFromTable(Table $table): array
    {
        $rows = [];
        $skipped = 0;
        $skippedDetails = [];

        $tableRows = $table->getRows();
        if (empty($tableRows)) {
            return compact('rows', 'skipped', 'skippedDetails');
        }

        // Judul tabel (mis. "BULAN JANUARI") diambil dari baris pertama, buat label di skippedDetails.
        $tableTitle = trim($this->extractCellText($tableRows[0]->getCells()[0] ?? null)) ?: '(tanpa judul)';

        // Cari baris header di antara beberapa baris pertama, karena bisa ada
        // baris judul bulan (mis. "BULAN JANUARI", merged cell) sebelum baris header asli.
        $headerRowIndex = null;
        $col = ['tracking_id' => null, 'tanggapan' => null, 'keterangan' => null];

        $scanLimit = min(self::MAX_HEADER_SCAN_ROWS, count($tableRows));
        for ($h = 0; $h < $scanLimit; $h++) {
            $candidate = $this->findHeaderColumns($tableRows[$h]);
            if ($candidate['tracking_id'] !== null) {
                $headerRowIndex = $h;
                $col = $candidate;
                break;
            }
        }

        // Kalau tabel ini bukan tabel rekap pengaduan (mis. tabel lain di dokumen), lewati.
        if ($headerRowIndex === null) {
            return compact('rows', 'skipped', 'skippedDetails');
        }

        for ($r = $headerRowIndex + 1; $r < count($tableRows); $r++) {
            $cells = $tableRows[$r]->getCells();

            $rawTrackingId = $this->extractCellText($cells[$col['tracking_id']] ?? null);
            $trackingId = preg_replace('/\D/', '', $rawTrackingId); // buang selain digit, termasuk syntax [xxx](javascript:;)

            $tanggapan = $col['tanggapan'] !== null
                ? trim($this->extractCellText($cells[$col['tanggapan']] ?? null))
                : null;

            $keterangan = $col['keterangan'] !== null
                ? trim($this->extractCellText($cells[$col['keterangan']] ?? null))
                : null;

            // Baris kosong / baris pemisah antar bulan, atau tracking_id kosong
            if ($trackingId === '') {
                $skipped++;
                $skippedDetails[] = [
                    'table' => $tableTitle,
                    'row' => $r + 1, // nomor baris fisik di tabel (1-based), termasuk baris judul & header
                    'cells' => array_map(
                        fn ($cell) => $this->extractCellText($cell),
                        $cells
                    ),
                ];
                continue;
            }

            $rows[$trackingId] = [
                'tanggapan' => $tanggapan ?: null,
                'keterangan' => $keterangan ?: null,
                'status' => $this->mapStatusFromKeterangan($keterangan),
                'sudah_ditanggapi' => ! empty($tanggapan),
            ];
        }

        return compact('rows', 'skipped', 'skippedDetails');
    }

    /**
     * Update massal: satu statement SQL yang meng-update banyak tracking_id sekaligus
     * pakai CASE WHEN, supaya tidak perlu 1 query per baris.
     *
     * @param array<string, array{tanggapan: ?string, keterangan: ?string, status: ?string, sudah_ditanggapi: bool}> $rows
     * @param string[] $trackingIds tracking_id yang sudah dipastikan ada di DB (hasil query #1)
     */
    private function bulkUpdate(array $rows, array $trackingIds): void
    {
        $now = now();

        $tanggapanCase = 'CASE tracking_id ';
        $keteranganCase = 'CASE tracking_id ';
        $statusCase = 'CASE tracking_id ';
        $sudahCase = 'CASE tracking_id ';

        $tanggapanBindings = [];
        $keteranganBindings = [];
        $statusBindings = [];
        $sudahBindings = [];

        foreach ($trackingIds as $id) {
            $data = $rows[$id];

            $tanggapanCase .= 'WHEN ? THEN ? ';
            $tanggapanBindings[] = $id;
            $tanggapanBindings[] = $data['tanggapan'];

            $keteranganCase .= 'WHEN ? THEN ? ';
            $keteranganBindings[] = $id;
            $keteranganBindings[] = $data['keterangan'];

            $statusCase .= 'WHEN ? THEN ? ';
            $statusBindings[] = $id;
            $statusBindings[] = $data['status'];

            // Literal TRUE/FALSE (bukan binding) supaya tipe boolean postgres aman.
            $sudahCase .= 'WHEN ? THEN ' . ($data['sudah_ditanggapi'] ? 'TRUE' : 'FALSE') . ' ';
            $sudahBindings[] = $id;
        }

        $tanggapanCase .= 'END';
        $keteranganCase .= 'END';
        $statusCase .= 'END';
        $sudahCase .= 'END';

        $placeholders = implode(',', array_fill(0, count($trackingIds), '?'));

        // status pakai COALESCE: kalau keterangan kosong (mapping menghasilkan NULL),
        // pertahankan nilai status yang sudah ada di DB (sama seperti perilaku sebelumnya).
        $sql = "
            UPDATE pengaduans SET
                tanggapan = {$tanggapanCase},
                keterangan = {$keteranganCase},
                status = COALESCE({$statusCase}, status),
                sudah_ditanggapi = {$sudahCase},
                word_synced_at = ?,
                updated_at = ?
            WHERE tracking_id IN ({$placeholders})
        ";

        $bindings = array_merge(
            $tanggapanBindings,
            $keteranganBindings,
            $statusBindings,
            $sudahBindings,
            [$now, $now],
            $trackingIds
        );

        DB::update($sql, $bindings);
    }

    /**
     * Coba baca satu baris sebagai baris header dan cari posisi kolom yang kita butuhkan.
     * Mengembalikan array kolom dengan nilai null semua kalau baris ini bukan baris header
     * (mis. baris judul bulan yang cell-nya merged / tidak mengandung "tracking").
     *
     * @return array{tracking_id: int|null, tanggapan: int|null, keterangan: int|null}
     */
    private function findHeaderColumns($row): array
    {
        $col = ['tracking_id' => null, 'tanggapan' => null, 'keterangan' => null];

        foreach ($row->getCells() as $i => $cell) {
            $text = strtolower(trim($this->extractCellText($cell)));

            if (str_contains($text, 'tracking')) {
                $col['tracking_id'] = $i;
            } elseif (str_contains($text, 'tanggapan')) {
                $col['tanggapan'] = $i;
            } elseif (str_contains($text, 'keterangan')) {
                $col['keterangan'] = $i;
            }
        }

        return $col;
    }

    /**
     * Kolom KETERANGAN di Word diisi manual sama petugas (nilai asli yang
     * kelihatan di dokumen: "selesai", "Sedang diproses"). Ini dijadikan
     * acuan status yang lebih akurat dibanding tebakan dari Excel.
     */
    private function mapStatusFromKeterangan(?string $keterangan): ?string
    {
        if (! $keterangan) {
            return null;
        }

        $k = strtolower(trim($keterangan));

        if (str_contains($k, 'selesai')) {
            return 'Selesai';
        }

        if (str_contains($k, 'proses')) {
            return 'Belum Selesai';
        }

        // Nilai lain (kalau ada) disimpan apa adanya, biar nggak hilang informasinya
        return $keterangan;
    }

    /**
     * Ambil teks polos dari satu cell tabel Word, termasuk kalau isinya
     * berupa hyperlink (contoh: Tracking ID yang ditulis sebagai link di dokumen asli).
     */
    private function extractCellText($cell): string
    {
        if (! $cell instanceof AbstractContainer) {
            return '';
        }

        $text = '';
        foreach ($cell->getElements() as $element) {
            $text .= $this->extractElementText($element);
        }

        return trim($text);
    }

    private function extractElementText($element): string
    {
        if ($element instanceof Text) {
            return $element->getText();
        }

        if ($element instanceof Link) {
            return $element->getText() ?? '';
        }

        if ($element instanceof AbstractContainer) {
            $text = '';
            foreach ($element->getElements() as $child) {
                $text .= $this->extractElementText($child);
            }
            return $text;
        }

        return '';
    }
}