<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaScholarPublication extends Model
{
    use HasFactory;

    // Menegaskan nama tabel
    protected $table = 'sinta_scholar_publications';

    // Menyesuaikan fillable dengan kolom bahasa Inggris yang baru
    protected $fillable = [
        'sinta_id',
        'title',
        'scholar_url',
        'authors',
        'source',
        'year',
        'citation',
    ];

    /**
     * Relasi balik ke dosen pemilik publikasi
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}