<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaLecturer extends Model
{
    use HasFactory;

    // Opsional: Laravel otomatis mendeteksi 'sinta_lecturers', tapi baik untuk ditegaskan
    protected $table = 'sinta_lecturers';

    // Mendefinisikan primary key kustom
    protected $primaryKey = 'sinta_id';

    // Karena primary key berformat string (bukan integer auto-increment)
    public $incrementing = false;
    protected $keyType = 'string';

    // Menyesuaikan dengan nama kolom yang baru
    protected $fillable = [
        'sinta_id',
        'name',
        'department',
        'scopus_h_index',
        'google_scholar_h_index',
        'sinta_score_3yr',
        'sinta_score',
        'affiliation_score_3yr',
        'affiliation_score',
        'profile_url',
    ];
}