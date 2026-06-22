<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaGarudaPublication extends Model
{
    use HasFactory;

    // Menegaskan nama tabel
    protected $table = 'sinta_garuda_publications';

    // Menyesuaikan fillable dengan kolom bahasa Inggris yang baru
    protected $fillable = [
        'sinta_id',
        'title',
        'article_url',
        'publisher',
        'journal',
        'journal_url',
        'author_order',
        'authors',
        'year',
        'doi',
        'accreditation',
    ];

    /**
     * Relasi balik ke dosen pemilik publikasi
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}