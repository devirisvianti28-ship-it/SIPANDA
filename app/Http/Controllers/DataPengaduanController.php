<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;

class DataPengaduanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'semua');

        // ================= FILTER TAHUN =================
        // Default: tahun berjalan (tahun sekarang). User bisa pilih tahun lain lewat dropdown.
        $tahun = $request->filled('tahun')
            ? (int) $request->input('tahun')
            : (int) now()->year;

        // Daftar tahun yang tersedia di data (buat ngisi dropdown), urut dari terbaru.
        $availableYears = Pengaduan::query()
            ->whereNotNull('tanggal')
            ->selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // Kalau tahun sekarang belum ada datanya sama sekali, tetap tampilkan
        // sebagai opsi paling atas supaya dropdown nggak kosong / aneh.
        if (! $availableYears->contains($tahun)) {
            $availableYears = $availableYears->push($tahun)->sortByDesc(fn ($y) => $y)->values();
        }

        // Query dasar: semua kondisi di bawah ini SELALU discope ke tahun terpilih.
        $baseQuery = Pengaduan::query()->whereYear('tanggal', $tahun);

        $query = clone $baseQuery;

        if ($filter === 'selesai') {
            $query->where('status', 'Selesai');
        } elseif ($filter === 'belum_selesai') {
            $query->where('status', '!=', 'Selesai');
        } elseif ($filter === 'belum_tanggapan') {
            $query->where('sudah_ditanggapi', false);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        if ($request->filled('skpd')) {
            $query->where('skpd', $request->input('skpd'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tracking_id')) {
            $keyword = $request->input('tracking_id');
            $query->where(function ($q) use ($keyword) {
                $q->where('tracking_id', 'like', '%' . $keyword . '%')
                  ->orWhere('pelapor', 'like', '%' . $keyword . '%');
            });
        }

        $pengaduan = $query->orderByDesc('tanggal')->paginate(20)->withQueryString();

        $daftarSkpd = Pengaduan::query()
            ->whereYear('tanggal', $tahun)
            ->whereNotNull('skpd')
            ->distinct()
            ->orderBy('skpd')
            ->pluck('skpd');

        // Statistik kartu di atas juga discope ke tahun yang sedang dipilih.
        $stats = [
            'total'            => (clone $baseQuery)->count(),
            'selesai'          => (clone $baseQuery)->where('status', 'Selesai')->count(),
            'belum_selesai'    => (clone $baseQuery)->where('status', '!=', 'Selesai')->count(),
            'belum_tanggapan'  => (clone $baseQuery)->where('sudah_ditanggapi', false)->count(),
        ];

        $totalData = $stats['total'];

        return view('data-pengaduan.index', compact(
            'pengaduan',
            'daftarSkpd',
            'totalData',
            'stats',
            'filter',
            'tahun',
            'availableYears'
        ));
    }

    public function updateTanggapan(Request $request, Pengaduan $pengaduan)
    {
        $validated = $request->validate([
            'sudah_ditanggapi' => 'required|boolean',
            'status'           => 'required|in:Selesai,Belum Selesai',
            'keterangan'       => 'nullable|string|max:255',
        ]);

        $pengaduan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui.',
        ]);
    }
}