<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UndergraduateLecturerDetail extends Model
{
    use HasFactory;

    protected $table = 'undergraduate_lecturer_details';

    protected $fillable = [
        'undergraduate_lecturer_id',
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

    protected static function booted(): void
    {
        static::deleted(function (UndergraduateLecturerDetail $lecturerDetail): void {
            $safeSintaId = Str::of((string) $lecturerDetail->sinta_id)
                ->trim()
                ->replaceMatches('/[^A-Za-z0-9_-]/', '')
                ->toString();

            if (! $safeSintaId) {
                return;
            }

            $customPath = "sinta-lecturers/{$safeSintaId}_UL.jpg";

            if (Storage::disk('public')->exists($customPath)) {
                Storage::disk('public')->delete($customPath);
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->sintaLecturer?->name ?? $this->lecturer?->name;
    }

    public function lecturer()
    {
        return $this->belongsTo(UndergraduateLecturer::class, 'undergraduate_lecturer_id');
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
