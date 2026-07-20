<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Tampilkan halaman Laporan Pengaduan (generate laporan Harian/Mingguan/Bulanan/Tahunan).
     * Data rekap di bawah ini bersifat dummy — ganti dengan query agregat sesuai
     * filter yang dipilih user (mis. Pengaduan::whereBetween(...)->get()).
     */
    public function index(Request $request)
    {
        $daftarSkpd = ['Dinas Kesehatan', 'Dinas Pendidikan', 'Dinas Pekerjaan Umum', 'Dinas Sosial', 'Dishub'];

        $bulanList = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $tahunList = range(now()->year, now()->year - 4);

        $instansi = [
            'nama_pemerintah' => 'PEMERINTAH KABUPATEN GARUT',
            'nama_dinas'      => 'Dinas Komunikasi dan Informatika',
            'alamat'          => 'Jalan Pembangunan No. 181, Sukagalih, Kec. Tarogong Kidul, Kab. Garut',
            'kepala_dinas'    => 'H. Margiyanto, S.H., M.Si.',
            'nip'             => '19690101 199003 1 001',
        ];

        $rekap = [
            'total_pengaduan' => 1248,
            'selesai'         => 942,
            'persentase'      => 75.5,
        ];

        $rincianSkpd = [
            ['no' => 1, 'skpd' => 'Dinas Kesehatan',      'masuk' => 210, 'proses' => 45, 'selesai' => 165],
            ['no' => 2, 'skpd' => 'Dinas Pendidikan',     'masuk' => 185, 'proses' => 30, 'selesai' => 155],
            ['no' => 3, 'skpd' => 'Dinas Pekerjaan Umum', 'masuk' => 142, 'proses' => 62, 'selesai' => 80],
        ];

        $totalRincian = [
            'masuk'   => collect($rincianSkpd)->sum('masuk'),
            'proses'  => collect($rincianSkpd)->sum('proses'),
            'selesai' => collect($rincianSkpd)->sum('selesai'),
        ];

        return view('laporan.index', compact(
            'daftarSkpd',
            'bulanList',
            'tahunList',
            'instansi',
            'rekap',
            'rincianSkpd',
            'totalRincian'
        ));
    }
}