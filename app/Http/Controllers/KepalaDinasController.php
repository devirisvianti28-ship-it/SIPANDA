<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KepalaDinasController extends Controller
{
    public function dashboard(Request $request)
    {
        // ================= FILTER TAHUN =================
        // Tombol "Tahun Ini" / "Tahun Lalu" di view ngirim ?tahun=YYYY.
        // Default: tahun berjalan.
        $tahunAktif = $request->filled('tahun')
            ? (int) $request->input('tahun')
            : (int) now()->year;

        $baseQuery = Pengaduan::query()->whereYear('tanggal', $tahunAktif);

        // ================= STAT CARDS =================
        $total           = (clone $baseQuery)->count();
        $selesai         = (clone $baseQuery)->where('status', 'Selesai')->count();
        $belumTanggapan  = (clone $baseQuery)->where('sudah_ditanggapi', false)->count();

        // "Proses" BUKAN nilai kolom `status` (kolom status cuma "Selesai"/"Belum Selesai").
        // Ini metrik turunan: sudah ada tanggapan (sudah_ditanggapi=true) tapi statusnya
        // masih "Belum Selesai" -> artinya lagi dikerjakan, bukan diabaikan.
        $proses          = (clone $baseQuery)
            ->where('sudah_ditanggapi', true)
            ->where('status', '!=', 'Selesai')
            ->count();

        $selesaiPersen = $total > 0 ? round(($selesai / $total) * 100) : 0;

        $skpdAktif = (clone $baseQuery)
            ->whereNotNull('skpd')
            ->distinct()
            ->count('skpd');

        // Waktu import terakhir: diambil dari record yang paling baru dibuat/diupdate.
        // Tidak discope ke tahun aktif karena ini soal "kapan terakhir ada aktivitas import",
        // bukan soal data tahun berapa yang diimpor.
        $lastImportAt = Pengaduan::max('updated_at');

        $stats = [
            'total'           => $total,
            'selesai'         => $selesai,
            'selesai_persen'  => $selesaiPersen,
            'proses'          => $proses,
            'pending'         => $belumTanggapan,
            'skpd'            => $skpdAktif,
            'import_tanggal'  => $lastImportAt ? Carbon::parse($lastImportAt)->translatedFormat('d M') : '-',
            'import_jam'      => $lastImportAt ? Carbon::parse($lastImportAt)->format('H:i') : '-',
            'total_singkat'   => $this->formatSingkat($total),
        ];

        // ================= TREN PENGADUAN BULANAN =================
        $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];

        $jumlahPerBulan = (clone $baseQuery)
            ->selectRaw('MONTH(tanggal) as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan'); // [1 => 12, 2 => 8, ...]

        $trenData = [];
        for ($b = 1; $b <= 12; $b++) {
            $trenData[] = (int) ($jumlahPerBulan[$b] ?? 0);
        }

        // Sorot bulan berjalan kalau tahun aktif = tahun sekarang,
        // kalau tahun lampau, sorot bulan dengan pengaduan terbanyak.
        if ($tahunAktif === (int) now()->year) {
            $highlightIndex = now()->month - 1;
        } else {
            $highlightIndex = collect($trenData)->search(max($trenData));
            $highlightIndex = $highlightIndex === false ? 0 : $highlightIndex;
        }

        $trenBulanan = [
            'labels'         => $namaBulan,
            'data'           => $trenData,
            'highlightIndex' => $highlightIndex,
        ];

        // ================= STATUS PENGADUAN (DONUT) =================
        // PENTING: kolom `status` di tabel pengaduan cuma punya 2 nilai asli:
        // "Selesai" dan "Belum Selesai" (sama persis kayak di halaman Data Pengaduan
        // dan Monitoring SKPD). Jangan bikin kategori karangan kayak "Diproses"/"Pending"
        // di sini karena itu bukan nilai yang beneran ada di database — bikin datanya
        // kelihatan nggak nyambung antar halaman.
        $persenSelesai      = $total > 0 ? round(($selesai / $total) * 100) : 0;
        $persenBelumSelesai = 100 - $persenSelesai; // dipaksa pas 100% (hindari rounding drift)

        $statusLegend = [
            ['label' => 'Selesai',       'persen' => $persenSelesai,      'color' => '#0B3D91'],
            ['label' => 'Belum Selesai', 'persen' => $persenBelumSelesai, 'color' => '#EF4444'],
        ];

        // ================= TOP 5 SKPD TERBANYAK =================
        $skpdGrouped = (clone $baseQuery)
            ->whereNotNull('skpd')
            ->selectRaw('skpd, COUNT(*) as jumlah')
            ->groupBy('skpd')
            ->orderByDesc('jumlah')
            ->take(5)
            ->get();

        $jumlahTertinggi = $skpdGrouped->max('jumlah') ?: 1;

        $topSkpd = $skpdGrouped->map(function ($row) use ($jumlahTertinggi) {
            return [
                'nama'   => $row->skpd,
                'jumlah' => $row->jumlah,
                'persen' => round(($row->jumlah / $jumlahTertinggi) * 100),
            ];
        })->values();

        // ================= LAJU PENYELESAIAN (4 minggu terakhir) =================
        // % pengaduan yang statusnya "Selesai" dari yang masuk di tiap minggu,
        // dihitung mundur dari hari ini (Minggu 4 = minggu berjalan).
        $lajuLabels = [];
        $lajuData = [];

        for ($w = 3; $w >= 0; $w--) {
            $mulaiMinggu = Carbon::now()->subWeeks($w)->startOfWeek();
            $akhirMinggu = Carbon::now()->subWeeks($w)->endOfWeek();

            $totalMinggu = Pengaduan::whereBetween('tanggal', [$mulaiMinggu, $akhirMinggu])->count();
            $selesaiMinggu = Pengaduan::whereBetween('tanggal', [$mulaiMinggu, $akhirMinggu])
                ->where('status', 'Selesai')
                ->count();

            $lajuLabels[] = 'Minggu ' . (4 - $w);
            $lajuData[] = $totalMinggu > 0 ? round(($selesaiMinggu / $totalMinggu) * 100) : 0;
        }

        $lajuPenyelesaian = [
            'labels' => $lajuLabels,
            'data'   => $lajuData,
        ];

        // Kenaikan = selisih persen minggu terakhir vs minggu sebelumnya.
        $lajuKenaikan = count($lajuData) >= 2
            ? round($lajuData[count($lajuData) - 1] - $lajuData[count($lajuData) - 2], 1)
            : 0;

        // ================= AKTIVITAS TERBARU =================
        $aktivitas = Pengaduan::query()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->take(5)
            ->get()
            ->map(function ($row) {
                return [
                    'tracking_id'      => $row->tracking_id,
                    'tanggal'          => $row->tanggal ? $row->tanggal->translatedFormat('d M Y') : '-',
                    'pelapor'          => $row->pelapor ?? '-',
                    'judul'            => $row->judul ?? '-',
                    'skpd'             => $row->skpd ?? '-',
                    'sudah_ditanggapi' => (bool) $row->sudah_ditanggapi,
                    'status'           => $row->status ?? 'Belum Selesai',
                ];
            });

        // Bedanya cuma di sini: nama view-nya 'kepala-dinas.dashboard'
        // (file resources/views/kepala-dinas/dashboard.blade.php),
        // bukan 'dashboard.index' punya Pengelola.
        return view('kepala-dinas.dashboard', compact(
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

    /**
     * Format angka besar jadi singkat: 1284 -> "1.3K", 950 -> "950", 12500 -> "12.5K".
     */
    private function formatSingkat(int $angka): string
    {
        if ($angka >= 1000) {
            return rtrim(rtrim(number_format($angka / 1000, 1), '0'), '.') . 'K';
        }

        return (string) $angka;
    }
}