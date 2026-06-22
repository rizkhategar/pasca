<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaBookPublication extends Model
{
    use HasFactory;

    // Menegaskan nama tabel baru yang konsisten
    protected $table = 'sinta_book_publications';

    // Menyesuaikan fillable dengan kolom bahasa Inggris
    protected $fillable = [
        'sinta_id',
        'title',
        'year',
        'publisher',
        'isbn',
        'authors',
    ];

    /**
     * Relasi balik ke dosen pemilik buku
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}