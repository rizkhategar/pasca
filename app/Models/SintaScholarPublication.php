<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SintaScholarPublication extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    /**
     * Relasi balik ke dosen pemilik publikasi
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}
