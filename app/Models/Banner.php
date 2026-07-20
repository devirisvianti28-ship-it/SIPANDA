<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'deskripsi',
        'gambar',
        'status',
        'utama',
        'urutan',
    ];

    protected $casts = [
        'status' => 'boolean',
        'utama'  => 'boolean',
    ];

    /**
     * URL publik gambar banner (butuh `php artisan storage:link`).
     */
    public function getGambarUrlAttribute(): string
    {
        return asset('storage/' . $this->gambar);
    }
}