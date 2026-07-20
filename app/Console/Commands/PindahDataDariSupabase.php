<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PindahDataDariSupabase extends Command
{
    protected $signature = 'pindah:data';
    protected $description = 'Salin semua data dari Supabase (Postgres) ke database lokal (MySQL)';

    /**
     * Urutan ini PENTING. Tabel yang "dirujuk" foreign key tabel lain
     * harus disalin duluan. Contoh: pengaduans.skpd_id merujuk ke skpds,
     * jadi skpds harus masuk duluan sebelum pengaduans.
     *
     * Tabel sistem/framework (cache, jobs, sessions, migrations, dll)
     * sengaja TIDAK dimasukkan — itu bukan data aplikasi, biarin
     * ke-generate ulang secara alami di MySQL.
     */
    protected array $tabels = [
        'skpds',
        'users',
        'banners',
        'pengaduans',
    ];

    public function handle()
    {
        foreach ($this->tabels as $tabel) {
            $this->pindahTabel($tabel);
            $this->newLine();
        }

        $this->info('=== Semua tabel selesai diproses. ===');
    }

    protected function pindahTabel(string $tabel): void
    {
        $this->info("=== Tabel: {$tabel} ===");

        if (! DB::connection('pgsql_lama')->getSchemaBuilder()->hasTable($tabel)) {
            $this->warn("Tabel '{$tabel}' tidak ditemukan di Supabase, dilewati.");
            return;
        }

        $dataLama = DB::connection('pgsql_lama')->table($tabel)->get();

        if ($dataLama->isEmpty()) {
            $this->comment("Tabel '{$tabel}' kosong di Supabase, dilewati.");
            return;
        }

        $this->info("Ketemu {$dataLama->count()} baris. Menyalin...");

        $bar = $this->output->createProgressBar($dataLama->count());
        $bar->start();

        $gagal = [];

        foreach ($dataLama as $row) {
            $data = (array) $row;

            try {
                DB::table($tabel)->updateOrInsert(
                    ['id' => $data['id']],
                    $data
                );
            } catch (\Throwable $e) {
                // Dicatat, tapi proses tetap lanjut ke baris berikutnya —
                // gak berhenti kayak sebelumnya, biar 1 baris bermasalah
                // gak nge-block semua data lain.
                $gagal[] = 'id ' . ($data['id'] ?? '?') . ': ' . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (! empty($gagal)) {
            $this->error(count($gagal) . " baris GAGAL disalin di tabel '{$tabel}':");
            foreach ($gagal as $pesan) {
                $this->line('  - ' . $pesan);
            }
        } else {
            $this->info("Tabel '{$tabel}' selesai, semua baris berhasil disalin.");
        }
    }
}