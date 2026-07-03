<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SintaLecturer extends Model
{
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function detail()
    {
        return $this->hasOne(SintaLecturerDetail::class, 'sinta_id', 'sinta_id');
    }

    public function postgraduateLecturer()
    {
        return $this->hasOne(PostgraduateLecturer::class, 'sinta_id', 'sinta_id');
    }

    public function scopusPublications()
    {
        return $this->hasMany(SintaScopusPublication::class, 'sinta_id', 'sinta_id');
    }

    public function scholarPublications()
    {
        return $this->hasMany(SintaScholarPublication::class, 'sinta_id', 'sinta_id');
    }

    public function garudaPublications()
    {
        return $this->hasMany(SintaGarudaPublication::class, 'sinta_id', 'sinta_id');
    }

    public function books()
    {
        return $this->hasMany(SintaBookPublication::class, 'sinta_id', 'sinta_id');
    }

    public function researches()
    {
        return $this->hasMany(SintaResearch::class, 'sinta_id', 'sinta_id');
    }

    public function services()
    {
        return $this->hasMany(SintaService::class, 'sinta_id', 'sinta_id');
    }

    public function researchYearlies()
    {
        return $this->hasMany(SintaResearchYearly::class, 'sinta_id', 'sinta_id');
    }

    public function serviceYearlies()
    {
        return $this->hasMany(SintaServiceYearly::class, 'sinta_id', 'sinta_id');
    }

    public function scopusYearlyStats()
    {
        return $this->hasMany(SintaScopusYearlyStat::class, 'sinta_id', 'sinta_id');
    }

    public function scholarYearlyStats()
    {
        return $this->hasMany(SintaScholarYearlyStat::class, 'sinta_id', 'sinta_id');
    }

    public function garudaYearlyStats()
    {
        return $this->hasMany(SintaGarudaYearlyStat::class, 'sinta_id', 'sinta_id');
    }
}
