<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use Illuminate\Http\Request;

class DataPengaduanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'semua');

        $query = Pengaduan::query();

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
            ->whereNotNull('skpd')
            ->distinct()
            ->orderBy('skpd')
            ->pluck('skpd');

        $stats = [
            'total'            => Pengaduan::count(),
            'selesai'          => Pengaduan::where('status', 'Selesai')->count(),
            'belum_selesai'    => Pengaduan::where('status', '!=', 'Selesai')->count(),
            'belum_tanggapan'  => Pengaduan::where('sudah_ditanggapi', false)->count(),
        ];

        $totalData = $stats['total'];

        return view('data-pengaduan.index', compact('pengaduan', 'daftarSkpd', 'totalData', 'stats', 'filter'));
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