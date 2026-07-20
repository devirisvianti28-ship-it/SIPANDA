<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataPengaduanController;
use App\Http\Controllers\MonitoringSkpdController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PengaduanImportController;

/*
|--------------------------------------------------------------------------
| Web Routes - SAPA GARUT
|--------------------------------------------------------------------------
*/

// ---------- Root ----------
// Kalau belum login, arahkan ke halaman login.
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

// ---------- Halaman yang wajib login ----------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data-pengaduan', [DataPengaduanController::class, 'index'])->name('data-pengaduan');
    Route::get('/monitoring-skpd', [MonitoringSkpdController::class, 'index'])->name('monitoring-skpd');
});

Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');

// ---------- Kelola Banner ----------
Route::get('/kelola-banner', [BannerController::class, 'index'])->name('kelola-banner');
Route::post('/kelola-banner', [BannerController::class, 'store'])->name('kelola-banner.store');
Route::patch('/kelola-banner/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])->name('kelola-banner.toggle-status');
Route::patch('/kelola-banner/{banner}/set-utama', [BannerController::class, 'setUtama'])->name('kelola-banner.set-utama');
Route::patch('/kelola-banner/{banner}/urutan', [BannerController::class, 'updateUrutan'])->name('kelola-banner.urutan');
Route::delete('/kelola-banner/{banner}', [BannerController::class, 'destroy'])->name('kelola-banner.destroy');

// Route gabungan (dipakai modal import yang baru): satu submit untuk
// Excel (wajib) + Word (opsional) + PDF (opsional) sekaligus.
Route::post('/pengaduan/import', [PengaduanImportController::class, 'import'])
    ->name('pengaduan.import');

// Route lama per-jenis file — boleh tetap dibiarkan kalau masih dipakai di tempat lain,
// atau dihapus kalau sudah tidak ada yang manggil.
Route::post('/pengaduan/import/excel', [PengaduanImportController::class, 'importExcel'])
    ->name('pengaduan.import.excel');

Route::post('/pengaduan/import/word', [PengaduanImportController::class, 'importWord'])
    ->name('pengaduan.import.word');

Route::post('/pengaduan/import/pdf', [PengaduanImportController::class, 'importPdf'])
    ->name('pengaduan.import.pdf');

Route::patch('/pengaduan/{pengaduan}/update-tanggapan', [App\Http\Controllers\DataPengaduanController::class, 'updateTanggapan'])
    ->name('pengaduan.update-tanggapan');