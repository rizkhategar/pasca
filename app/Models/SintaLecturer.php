<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaLecturer extends Model
{
    use HasFactory;

    protected $table = 'sinta_lecturers';

    protected $primaryKey = 'sinta_id';

    public $incrementing = false;
    protected $keyType = 'string';

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

    public function detail()
    {
        return $this->hasOne(SintaLecturerDetail::class, 'sinta_id', 'sinta_id');
    }

    public function postgraduateLecturer()
    {
        return $this->hasOne(PostgraduateLecturer::class, 'sinta_id', 'sinta_id');
    }

    public function undergraduateLecturer()
    {
        return $this->hasOne(UndergraduateLecturer::class, 'sinta_id', 'sinta_id');
    }
}
