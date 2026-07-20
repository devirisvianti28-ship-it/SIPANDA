<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    /**
     * Menampilkan halaman profil user yang sedang login.
     */
    public function show()
    {
        $user = Auth::user();

        $user->bergabung_sejak = $user->created_at
            ? $user->created_at->translatedFormat('d F Y')
            : '-';

        $user->login_terakhir = $user->login_terakhir
            ? \Carbon\Carbon::parse($user->login_terakhir)->translatedFormat('d F Y, H:i')
            : ($user->updated_at ? $user->updated_at->translatedFormat('d F Y, H:i') : '-');

        return view('profil', [
            'user'     => $user,
            'userName' => $user->nama_lengkap ?? $user->name,
            'userRole' => $user->peran ?? 'Pengguna',
        ]);
    }

    /**
     * Menyimpan perubahan data profil, termasuk foto profil (kalau diupload).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nip'          => ['nullable', 'string', 'max:50'],
            'email'        => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $dataUpdate = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'nip'          => $validated['nip'],
            'email'        => $validated['email'],
        ];

        // Kalau ada file foto baru yang diupload
        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama supaya tidak menumpuk file di storage
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }

            $path = $request->file('foto_profil')->store('profil', 'public');
            $dataUpdate['foto_profil'] = $path;
        }

        $user->update($dataUpdate);

        return redirect()
            ->route('profil')
            ->with('success', 'Perubahan profil berhasil disimpan.');
    }
}