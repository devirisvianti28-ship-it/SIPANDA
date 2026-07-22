<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataPengaduanController;
use App\Http\Controllers\MonitoringSkpdController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PengaduanImportController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KepalaDinasController;

/*
|--------------------------------------------------------------------------
| Web Routes - SAPA GARUT
|--------------------------------------------------------------------------
| 3 role: master_admin, pengelola, kepala_dinas
| - master_admin & pengelola : akses penuh (baca + tulis semua modul)
| - kepala_dinas             : READ-ONLY (dashboard sendiri, lihat data
|                               pengaduan, monitoring SKPD, arsip laporan)
*/

// ---------- Root ----------
Route::get('/', [LoginController::class, 'showLoginForm']);

// ---------- Auth ----------
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// ==============================================================
// HALAMAN YANG BISA DIAKSES SEMUA ROLE YANG SUDAH LOGIN (read-only bersama)
// ==============================================================
Route::middleware(['auth', 'role:master_admin,pengelola,kepala_dinas'])->group(function () {
    Route::get('/data-pengaduan', [DataPengaduanController::class, 'index'])->name('data-pengaduan');
    Route::get('/monitoring-skpd', [MonitoringSkpdController::class, 'index'])->name('monitoring-skpd');
    Route::get('/laporan/pengaduan', [LaporanController::class, 'pengaduan'])->name('laporan.pengaduan');

    // ---------- Profil (semua role boleh lihat & update profil sendiri) ----------
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
});

// ==============================================================
// DASHBOARD PENGELOLA / MASTER ADMIN (dashboard umum, full access)
// ==============================================================
Route::middleware(['auth', 'role:master_admin,pengelola'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---------- Generate Laporan ----------
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

    // ---------- Kelola Banner ----------
    Route::get('/kelola-banner', [BannerController::class, 'index'])->name('kelola-banner');
    Route::post('/kelola-banner', [BannerController::class, 'store'])->name('kelola-banner.store');
    Route::patch('/kelola-banner/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])->name('kelola-banner.toggle-status');
    Route::patch('/kelola-banner/{banner}/set-utama', [BannerController::class, 'setUtama'])->name('kelola-banner.set-utama');
    Route::patch('/kelola-banner/{banner}/urutan', [BannerController::class, 'updateUrutan'])->name('kelola-banner.urutan');
    Route::delete('/kelola-banner/{banner}', [BannerController::class, 'destroy'])->name('kelola-banner.destroy');

    // ---------- Import & Update Data Pengaduan (tulis) ----------
    Route::post('/pengaduan/import', [PengaduanImportController::class, 'import'])
        ->name('pengaduan.import');
    Route::post('/pengaduan/import/excel', [PengaduanImportController::class, 'importExcel'])
        ->name('pengaduan.import.excel');
    Route::post('/pengaduan/import/word', [PengaduanImportController::class, 'importWord'])
        ->name('pengaduan.import.word');
    Route::post('/pengaduan/import/pdf', [PengaduanImportController::class, 'importPdf'])
        ->name('pengaduan.import.pdf');
    Route::patch('/pengaduan/{pengaduan}/update-tanggapan', [DataPengaduanController::class, 'updateTanggapan'])
        ->name('pengaduan.update-tanggapan');

    // ---------- Manajemen Pengguna ----------
    Route::get('/manajemen-pengguna', [UserController::class, 'index'])
        ->name('manajemen-pengguna.index');
    Route::post('/manajemen-pengguna', [UserController::class, 'store'])
        ->name('manajemen-pengguna.store');
    Route::put('/manajemen-pengguna/{user}', [UserController::class, 'update'])
        ->name('manajemen-pengguna.update');
    Route::delete('/manajemen-pengguna/{user}', [UserController::class, 'destroy'])
        ->name('manajemen-pengguna.destroy');
});

// ==============================================================
// DASHBOARD KEPALA DINAS (read-only, khusus role kepala_dinas)
// ==============================================================
Route::middleware(['auth', 'role:kepala_dinas'])->group(function () {
    Route::get('/kepala-dinas/dashboard', [KepalaDinasController::class, 'dashboard'])
        ->name('kepala-dinas.dashboard');
});