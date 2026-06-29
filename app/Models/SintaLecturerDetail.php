<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SintaLecturerDetail extends Model
{
    use HasFactory;

    protected $table = 'sinta_lecturer_details';

    protected $primaryKey = 'sinta_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sinta_id',
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

    protected static function booted(): void
    {
        static::deleted(function (SintaLecturerDetail $lecturerDetail): void {
            $sintaId = $lecturerDetail->sinta_id;

            if (! $sintaId) {
                return;
            }

            $lecturerDetail->postgraduateLecturer()->delete();

            $safeSintaId = Str::of($sintaId)
                ->trim()
                ->replaceMatches('/[^A-Za-z0-9_-]/', '')
                ->toString();

            if (! $safeSintaId) {
                return;
            }

            $photoFilePaths = [
                "sinta-lecturers/{$safeSintaId}.jpg",
                "sinta-lecturers/{$safeSintaId}_PL.jpg",
            ];

            foreach ($photoFilePaths as $photoFilePath) {
                if (Storage::disk('public')->exists($photoFilePath)) {
                    Storage::disk('public')->delete($photoFilePath);
                }
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->lecturer?->name;
    }

    public function setNameAttribute($value): void
    {
        // Kolom name sudah tidak ada di sinta_lecturer_details.
        // Nama dosen disimpan dan dibaca dari relasi utama sinta_lecturers.name.
    }

    public function postgraduateLecturer()
    {
        return $this->hasOne(PostgraduateLecturer::class, 'sinta_id', 'sinta_id');
    }

    public function pascaLecturer()
    {
        return $this->postgraduateLecturer();
    }

    public function studyProgramPivots()
    {
        return $this->hasManyThrough(
            PostgraduateLecturerStudyProgram::class,
            PostgraduateLecturer::class,
            'sinta_id',
            'postgraduate_lecturer_id',
            'sinta_id',
            'id'
        );
    }

    public function departments()
    {
        return $this->studyProgramPivots();
    }

    public function lecturer()
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
