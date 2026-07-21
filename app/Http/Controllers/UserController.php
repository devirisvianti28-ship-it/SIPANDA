<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan halaman Manajemen Pengguna.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('peran') && $request->peran !== 'semua') {
            $query->where('peran', $request->peran);
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total'        => User::count(),
            'aktif'        => User::where('status', 'aktif')->count(),
            'nonaktif'     => User::where('status', 'nonaktif')->count(),
            'kepala_dinas' => User::where('peran', 'kepala_dinas')->count(),
        ];

        return view('manajemen-pengguna.index', compact('users', 'stats'));
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip'      => ['required', 'string', 'max:30', 'unique:users,nip'],
            'name'     => ['required', 'string', 'max:255'],
            'peran'    => ['required', Rule::in(['pengelola', 'kepala_dinas'])],
            'email'    => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'nip'          => $validated['nip'],
            'username'     => $validated['nip'],
            'name'         => $validated['name'],
            'nama_lengkap' => $validated['name'],
            'peran'        => $validated['peran'],
            'email'        => $validated['email'] ?? null,
            'password'     => Hash::make($validated['password']),
            'status'       => $request->boolean('status') ? 'aktif' : 'nonaktif',
        ]);

        return redirect()
            ->route('manajemen-pengguna.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nip'      => ['required', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($user->id)],
            'name'     => ['required', 'string', 'max:255'],
            'peran'    => ['required', Rule::in(['pengelola', 'kepala_dinas'])],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->nip          = $validated['nip'];
        $user->username     = $validated['nip'];
        $user->name         = $validated['name'];
        $user->nama_lengkap = $validated['name'];
        $user->peran        = $validated['peran'];
        $user->email        = $validated['email'] ?? null;
        $user->status       = $request->boolean('status') ? 'aktif' : 'nonaktif';

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('manajemen-pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('manajemen-pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}