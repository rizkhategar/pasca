<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostgraduateLecturerDetail extends Model
{
    use HasFactory;

    protected $table = 'postgraduate_lecturer_details';

    protected $fillable = [
        'postgraduate_lecturer_id',
        'sinta_id',
        'institution',
        'study_program',
        'profile_photo',
        'research_interests',
        'sinta_score_overall',
        'sinta_score_3yr',
        'affil_score',
        'affil_score_3yr',
    ];

    public function lecturer()
    {
        return $this->belongsTo(PostgraduateLecturer::class, 'postgraduate_lecturer_id');
    }

    public function sintaLecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
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
