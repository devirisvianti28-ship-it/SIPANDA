<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        // Ambil banner yang aktif, terbaru duluan, untuk slideshow foto latar.
        $banners = Banner::where('status', true)->orderByDesc('created_at')->get();

        return view('auth.register', compact('banners'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:users,nip',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        User::create([
            'nip' => $validated['nip'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['nip'], // pakai NIP juga, biar kolom username (unique, wajib) tetap terisi
            'password' => $validated['password'],
        ]);

        return redirect()->route('login')->with('status', 'Registrasi berhasil! Silakan masuk.');
    }
}