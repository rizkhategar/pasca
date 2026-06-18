<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaLecturerDetail extends Model
{
    use HasFactory;

    // Menegaskan nama tabel baru
    protected $table = 'sinta_lecturer_details';

    // Mendefinisikan primary key kustom
    protected $primaryKey = 'sinta_id';

    // Konfigurasi primary key non-incrementing string
    public $incrementing = false;
    protected $keyType = 'string';

    // Menyesuaikan dengan properti baru yang berbahasa Inggris
    protected $fillable = [
        'sinta_id',
        'name',
        'institution',
        'study_program',
        'profile_photo',
        'research_interests',
        'sinta_score_overall',
        'sinta_score_3yr',
        'affil_score',
        'affil_score_3yr',
        'department',
    ];

    /**
     * Relasi ke tabel SintaLecturer (jika diperlukan)
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}