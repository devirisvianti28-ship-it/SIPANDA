<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $fillable = [
        'nama',
        'skpd',
        'jenis',
        'periode',
        'tanggal_dibuat',
        'format',
        'path_file',
        'ukuran_bytes',
        'user_id',
    ];

    protected $casts = [
        'tanggal_dibuat' => 'date',
    ];
}