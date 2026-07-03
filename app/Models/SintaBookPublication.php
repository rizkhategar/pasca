<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SintaBookPublication extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    /**
     * Relasi balik ke dosen pemilik buku
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}
