<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Tampilkan daftar banner + form Tambah Banner.
     * Mendukung search (?cari=), filter status (?status=aktif|nonaktif),
     * dan sort (?urut=terbaru|terlama|urutan).
     */
    public function index(Request $request)
    {
        $query = Banner::query();

        // ---- Search judul/deskripsi ----
        if ($request->filled('cari')) {
            $keyword = $request->input('cari');
            $query->where(function ($q) use ($keyword) {
                $q->where('judul', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi', 'like', "%{$keyword}%");
            });
        }

        // ---- Filter status ----
        if ($request->input('status') === 'aktif') {
            $query->where('status', true);
        } elseif ($request->input('status') === 'nonaktif') {
            $query->where('status', false);
        }

        // ---- Sort ----
        switch ($request->input('urut', 'terbaru')) {
            case 'terlama':
                $query->orderBy('created_at');
                break;
            default: // terbaru
                $query->orderByDesc('created_at');
                break;
        }

        $banners = $query->get();

        return view('kelola-banner.index', compact('banners'));
    }

    /**
     * Simpan banner baru (upload gambar ke storage/app/public/banners).
     * Judul & deskripsi tidak diminta dari user — judul diisi otomatis
     * agar tetap kompatibel dengan kolom "judul" yang NOT NULL di database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'gambar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $path = $request->file('gambar')->store('banners', 'public');

        Banner::create([
            'judul'     => 'Banner ' . now()->format('d-m-Y H:i:s'),
            'deskripsi' => null,
            'gambar'    => $path,
            'status'    => $request->boolean('status', true),
            'utama'     => false,
        ]);

        return redirect()->route('kelola-banner')->with('success', 'Banner baru berhasil ditambahkan.');
    }

    /**
     * Toggle status Aktif / Nonaktif (dipanggil via AJAX fetch dari toggle switch).
     */
    public function toggleStatus(Banner $banner)
    {
        $banner->update(['status' => ! $banner->status]);

        return response()->json(['success' => true, 'status' => $banner->status]);
    }

    /**
     * Hapus banner beserta file gambarnya.
     */
    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->gambar);
        $banner->delete();

        return response()->json(['success' => true]);
    }
}