<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoringSkpdController extends Controller
{
    // Target penyelesaian dalam persen. Kalau target beda-beda per SKPD,
    // ganti ini jadi lookup dari tabel master SKPD.
    const TARGET_PERSEN = 90;

    public function index(Request $request)
    {
        $tahun = $request->input('tahun', now()->year);
        $bulan = $request->input('bulan'); // 1-12, boleh kosong = semua bulan
        $tracking = $request->input('tracking');

        $query = Pengaduan::query()
            ->whereYear('tanggal', $tahun)
            ->when($bulan, fn ($q) => $q->whereMonth('tanggal', $bulan))
            ->when($tracking, function ($q) use ($tracking) {
                $q->where(function ($qq) use ($tracking) {
                    $qq->where('tracking_id', 'like', "%{$tracking}%")
                       ->orWhere('pelapor', 'like', "%{$tracking}%");
                });
            })
            ->whereNotNull('skpd');

        // ================= AGREGASI PER SKPD =================
        $rows = (clone $query)->get()->groupBy('skpd');

        $kinerjaSkpd = $rows->map(function ($group, $namaSkpd) {
            $total = $group->count();
            $selesai = $group->where('status', 'Selesai')->count();
            $belumAdaTanggapan = $group->where('sudah_ditanggapi', false)->count();
            $ditindaklanjuti = $group->where('sudah_ditanggapi', true)
                                      ->where('status', '!=', 'Selesai')
                                      ->count();

            $persen = $total > 0 ? round(($selesai / $total) * 100) : 0;
            $target = self::TARGET_PERSEN;

            if ($persen >= 100) {
                $status = 'SEMPURNA';
                $statusColor = 'kuning';
            } elseif ($persen >= $target) {
                $status = 'TINGGI';
                $statusColor = 'hijau';
            } elseif ($persen >= 60) {
                $status = 'NORMAL';
                $statusColor = 'biru';
            } else {
                $status = 'TERLAMBAT';
                $statusColor = 'merah';
            }

            return [
                'nama' => $namaSkpd,
                'total' => $total,
                'selesai' => $selesai,
                'belum_ada_tanggapan' => $belumAdaTanggapan,
                'ditindaklanjuti' => $ditindaklanjuti,
                'persen' => $persen,
                'target' => $target,
                'status' => $status,
                'status_color' => $statusColor,
            ];
        })->sortByDesc('total')->values();

        // ================= STAT CARDS =================
        $totalSkpd = $kinerjaSkpd->count();
        // "aktif" = SKPD yang punya minimal 1 pengaduan pada periode ini
        $skpdAktif = $kinerjaSkpd->where('total', '>', 0)->count();

        $stats = [
            'total_skpd' => $totalSkpd,
            'total_skpd_note' => '+0 dari bln lalu', // ganti ke perbandingan real kalau perlu
            'skpd_aktif' => $skpdAktif,
            'skpd_aktif_note' => $totalSkpd > 0 ? round(($skpdAktif / $totalSkpd) * 100) . '% Aktif' : '0% Aktif',
        ];

        // ================= TOP PERFORMERS (top 3 berdasarkan persen) =================
        $topPerformers = $kinerjaSkpd
            ->sortByDesc('persen')
            ->take(3)
            ->values()
            ->map(function ($skpd, $i) {
                return [
                    'rank' => $i + 1,
                    'nama' => $skpd['nama'],
                    'persen' => $skpd['persen'],
                ];
            });

        // ================= TREN PENYELESAIAN 7 HARI TERAKHIR =================
        $labelHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $trenLabels = [];
        $trenData = [];

        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);
            $trenLabels[] = $labelHari[$tanggal->dayOfWeek];
            $trenData[] = Pengaduan::whereDate('tanggal', $tanggal)
                ->where('status', 'Selesai')
                ->count();
        }

        $trenPenyelesaian = [
            'labels' => $trenLabels,
            'data' => $trenData,
        ];

        // ================= DROPDOWN FILTER =================
        $tahunList = Pengaduan::selectRaw('YEAR(tanggal) as tahun')
            ->whereNotNull('tanggal')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        if ($tahunList->isEmpty()) {
            $tahunList = collect([now()->year]);
        }

        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('monitoring-skpd.index', compact(
            'stats',
            'kinerjaSkpd',
            'topPerformers',
            'trenPenyelesaian',
            'tahunList',
            'bulanList'
        ));
    }
}