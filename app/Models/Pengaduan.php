<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $fillable = [
        'tracking_id',
        'tanggal',
        'pelapor',
        'judul',
        'isi_awal',
        'isi_akhir',
        'skpd',
        'status_laporan_raw',
        'status',
        'tanggapan',
        'keterangan',
        'sudah_ditanggapi',
        'excel_synced_at',
        'word_synced_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sudah_ditanggapi' => 'boolean',
        'excel_synced_at' => 'datetime',
        'word_synced_at' => 'datetime',
    ];
}