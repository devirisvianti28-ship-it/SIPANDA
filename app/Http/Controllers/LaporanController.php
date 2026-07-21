<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Tampilkan halaman Laporan Pengaduan (generate laporan Harian/Mingguan/Bulanan/Tahunan).
     *
     * Query string yang didukung (sesuai name attribute di form Blade):
     *   jenis            = harian | mingguan | bulanan | tahunan   (default: bulanan)
     *   tab              = ringkasan | visualisasi | rekap | rekomendasi (tab yang mau tetap aktif setelah submit)
     *   harian_tanggal   = Y-m-d
     *   harian_skpd, harian_status
     *   mingguan_mulai, mingguan_selesai = Y-m-d
     *   mingguan_skpd, mingguan_status
     *   bulanan_periode  = nama bulan, mis. "Maret"
     *   bulanan_tahun    = 2024/2025/2026/...
     *   bulanan_skpd, bulanan_status
     *   tahunan_tahun    = 2024/2025/2026/...
     *   tahunan_skpd, tahunan_status
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

        // ================= FILTER =================
        $jenis = $request->input('jenis', 'bulanan');

        $validTabs = ['ringkasan', 'visualisasi', 'rekap', 'rekomendasi'];
        $activeTab = $request->input('tab', 'ringkasan');
        if (! in_array($activeTab, $validTabs, true)) {
            $activeTab = 'ringkasan';
        }

        $query = Pengaduan::query();

        $skpdFilter = null;
        $statusFilter = null;

        switch ($jenis) {
            case 'harian':
                $tanggal = $request->input('harian_tanggal', now()->format('Y-m-d'));
                $query->whereDate('tanggal', $tanggal);
                $skpdFilter = $request->input('harian_skpd');
                $statusFilter = $request->input('harian_status');
                break;

            case 'mingguan':
                $mulai = $request->input('mingguan_mulai', now()->startOfWeek()->format('Y-m-d'));
                $selesai = $request->input('mingguan_selesai', now()->endOfWeek()->format('Y-m-d'));
                $query->whereBetween('tanggal', [$mulai, $selesai]);
                $skpdFilter = $request->input('mingguan_skpd');
                $statusFilter = $request->input('mingguan_status');
                break;

            case 'tahunan':
                $tahun = $request->input('tahunan_tahun', now()->year);
                $query->whereYear('tanggal', $tahun);
                $skpdFilter = $request->input('tahunan_skpd');
                $statusFilter = $request->input('tahunan_status');
                break;

            case 'bulanan':
            default:
                $jenis = 'bulanan';
                $periode = $request->input('bulanan_periode', $bulanList[now()->month - 1]);
                $tahun = $request->input('bulanan_tahun', now()->year);
                $bulanKe = array_search($periode, $bulanList);
                $bulanKe = $bulanKe !== false ? $bulanKe + 1 : now()->month;

                $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulanKe);
                $skpdFilter = $request->input('bulanan_skpd');
                $statusFilter = $request->input('bulanan_status');
                break;
        }

        if ($skpdFilter && ! str_starts_with($skpdFilter, 'Semua')) {
            $query->where('skpd', $skpdFilter);
        }

        if ($statusFilter && ! str_starts_with($statusFilter, 'Semua')) {
            $query->where('status', $statusFilter);
        }

        // ================= REKAP RINGKASAN =================
        // Clone query dulu sebelum di-paginate, biar agregatnya tetap ngikutin filter
        // yang sama tanpa kena limit pagination.
        $totalPengaduan = (clone $query)->count();
        $totalSelesai   = (clone $query)->where('status', 'Selesai')->count();
        $persentase     = $totalPengaduan > 0 ? round(($totalSelesai / $totalPengaduan) * 100, 1) : 0;

        $rekap = [
            'total_pengaduan' => $totalPengaduan,
            'selesai'         => $totalSelesai,
            'persentase'      => $persentase,
        ];

        // ================= RINCIAN PER SKPD =================
        $rincianSkpd = (clone $query)
            ->select('skpd', DB::raw('COUNT(*) as masuk'))
            ->selectRaw("SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai")
            ->selectRaw("SUM(CASE WHEN status != 'Selesai' THEN 1 ELSE 0 END) as proses")
            ->whereNotNull('skpd')
            ->groupBy('skpd')
            ->orderByDesc('masuk')
            ->get()
            ->map(function ($row, $i) {
                return [
                    'no'      => $i + 1,
                    'skpd'    => $row->skpd,
                    'masuk'   => (int) $row->masuk,
                    'proses'  => (int) $row->proses,
                    'selesai' => (int) $row->selesai,
                ];
            })
            ->values()
            ->all();

        $totalRincian = [
            'masuk'   => collect($rincianSkpd)->sum('masuk'),
            'proses'  => collect($rincianSkpd)->sum('proses'),
            'selesai' => collect($rincianSkpd)->sum('selesai'),
        ];

        // ================= DATA REKAP (TABEL DETAIL) =================
        $rekapData = (clone $query)
            ->orderByDesc('tanggal')
            ->paginate(20)
            ->withQueryString();

        return view('laporan.index', compact(
            'daftarSkpd',
            'bulanList',
            'tahunList',
            'instansi',
            'rekap',
            'rincianSkpd',
            'totalRincian',
            'rekapData',
            'jenis',
            'activeTab'
        ));
    }
}