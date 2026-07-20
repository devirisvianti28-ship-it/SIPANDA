<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Ambil banner yang aktif, terbaru duluan, untuk slideshow foto latar.
        $banners = Banner::where('status', true)->orderByDesc('created_at')->get();

        return view('auth.login', compact('banners'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'nip' => 'NIP atau password salah.',
        ])->onlyInput('nip');
    }
}