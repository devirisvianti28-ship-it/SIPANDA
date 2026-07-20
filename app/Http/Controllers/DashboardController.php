<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'          => 1284,
            'selesai'        => 942,
            'selesai_persen' => 73,
            'proses'         => 156,
            'pending'        => 186,
            'skpd'           => 42,
            'import_tanggal' => '14 Okt',
            'import_jam'     => '09:42',
            'total_singkat'  => '1.2K',
        ];

        $tahunAktif = 2026;

        $trenBulanan = [
            'labels' => ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'],
            'data'   => [55, 72, 60, 92, 78, 98, 85, 90, 74, 8, 6, 5],
            'highlightIndex' => 7,
        ];

        $statusLegend = [
            ['label' => 'Selesai',   'persen' => 73, 'color' => '#0B3D91'],
            ['label' => 'Diproses',  'persen' => 12, 'color' => '#3B82F6'],
            ['label' => 'Pending',   'persen' => 15, 'color' => '#EF4444'],
        ];

        $topSkpd = [
            ['nama' => 'Dinas Pekerjaan Umum & Penataan Ruang', 'jumlah' => 245, 'persen' => 100],
            ['nama' => 'Dinas Perhubungan',                     'jumlah' => 188, 'persen' => 77],
            ['nama' => 'Dinas Kesehatan',                       'jumlah' => 142, 'persen' => 58],
            ['nama' => 'Dinas Sosial',                          'jumlah' => 96,  'persen' => 39],
            ['nama' => 'Satpol PP',                              'jumlah' => 84,  'persen' => 34],
        ];

        $lajuPenyelesaian = [
            'labels' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
            'data'   => [20, 35, 78, 88],
        ];

        $lajuKenaikan = 12.4;

        $aktivitas = [
            [
                'tracking_id'       => '#241014001',
                'tanggal'           => '14 Okt 2024',
                'pelapor'           => 'Budi Santoso',
                'judul'             => 'Jalan Berlubang di Area Kadungora',
                'skpd'              => 'Dinas PUPR',
                'sudah_ditanggapi'  => true,
                'status'            => 'Selesai',
            ],
            [
                'tracking_id'       => '#241014002',
                'tanggal'           => '14 Okt 2024',
                'pelapor'           => 'Siti Aminah',
                'judul'             => 'Pelayanan Puskesmas Lambat',
                'skpd'              => 'Dinas Kesehatan',
                'sudah_ditanggapi'  => true,
                'status'            => 'Selesai',
            ],
            [
                'tracking_id'       => '#241014003',
                'tanggal'           => '13 Okt 2024',
                'pelapor'           => 'Anwar Hakim',
                'judul'             => 'Lampu Jalan Mati Total',
                'skpd'              => 'Dishub',
                'sudah_ditanggapi'  => false,
                'status'            => 'Pending',
            ],
        ];

        return view('dashboard.index', compact(
            'stats',
            'tahunAktif',
            'trenBulanan',
            'statusLegend',
            'topSkpd',
            'lajuPenyelesaian',
            'lajuKenaikan',
            'aktivitas'
        ));
    }
}