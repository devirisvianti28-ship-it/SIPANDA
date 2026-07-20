<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Data contoh mengikuti tampilan desain (opsional).
     * Catatan: field `gambar` di sini hanya nama file placeholder —
     * kalau file aslinya belum ada di storage/app/public/banners/,
     * tampilan di view akan otomatis fallback ke gambar placeholder abu-abu.
     */
    public function run(): void
    {
        Banner::insert([
            [
                'judul'      => 'Sosialisasi SAPA GARUT v2.0',
                'deskripsi'  => 'Informasi pembaruan sistem',
                'gambar'     => 'banners/contoh-1.jpg',
                'status'     => true,
                'utama'      => true,
                'urutan'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul'      => 'Festival Budaya Garut 2023',
                'deskripsi'  => 'Arsip pengumuman acara',
                'gambar'     => 'banners/contoh-2.jpg',
                'status'     => false,
                'utama'      => false,
                'urutan'     => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'judul'      => 'Panduan Penggunaan Aplikasi',
                'deskripsi'  => 'Visual interaktif mengenai fitur',
                'gambar'     => 'banners/contoh-3.jpg',
                'status'     => true,
                'utama'      => false,
                'urutan'     => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}