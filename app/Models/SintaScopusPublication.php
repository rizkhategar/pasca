<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SintaScopusPublication extends Model
{
    use HasFactory, LogsActivity;

    // Menegaskan nama tabel
    protected $table = 'sinta_scopus_publications';

    // Menyesuaikan fillable dengan kolom bahasa Inggris yang baru
    protected $fillable = [
        'sinta_id',
        'title',
        'year',
        'citation',
        'quartile',
        'journal',
        'author_order',
        'creator',
        'article_url',
        'journal_url',
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
