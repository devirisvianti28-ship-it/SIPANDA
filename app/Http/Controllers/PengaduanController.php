<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;

class PengaduanController extends Controller
{
    public function index(Request $request)
    {
        $query = Laporan::query();

        if ($request->filled('tanggal_dibuat')) {
            $query->whereDate('tanggal_dibuat', $request->input('tanggal_dibuat'));
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->input('jenis'));
        }

        $totalArsip = (clone $query)->count();

        $laporans = $query->orderByDesc('tanggal_dibuat')
            ->get()
            ->map(fn ($l) => [
                'nama'      => $l->nama,
                'skpd'      => $l->skpd,
                'jenis'     => ucfirst($l->jenis),
                'periode'   => $l->periode,
                'dibuat'    => $l->tanggal_dibuat->format('d M Y'),
                'format'    => $l->format,
                'ukuran'    => number_format($l->ukuran_bytes / 1024, 1) . ' KB',
                'url_lihat' => asset('storage/' . $l->path_file),
                'url_unduh' => asset('storage/' . $l->path_file),
            ]);

        return view('laporan.pengaduan', compact('laporans', 'totalArsip'));
    }
}