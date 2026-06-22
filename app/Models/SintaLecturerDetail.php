<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaLecturerDetail extends Model
{
    use HasFactory;

    protected $table = 'sinta_lecturer_details';

    protected $primaryKey = 'sinta_id';
    public $incrementing = false;
    protected $keyType = 'string';

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


    public function pascaLecturer()
    {
        return $this->hasOne(PascaLecturer::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Relasi balik ke Master Dosen (SintaLecturer)
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }

    // =========================================================================
    // --- RELASI ELOQUENT UNTUK FILAMENT RELATION MANAGERS (BAHASA INGGRIS) ---
    // =========================================================================

    /**
     * Relasi ke Publikasi Scopus (Memperbaiki error Call to undefined method)
     */
    public function scopusPublications()
    {
        return $this->hasMany(SintaScopusPublication::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Relasi ke Publikasi Google Scholar
     */
    public function scholarPublications()
    {
        return $this->hasMany(SintaScholarPublication::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Relasi ke Publikasi Garuda
     */
    public function garudaPublications()
    {
        return $this->hasMany(SintaGarudaPublication::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Relasi ke Buku
     */
    public function books()
    {
        return $this->hasMany(SintaBookPublication::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Relasi ke Penelitian (Researches)
     */
    public function researches()
    {
        return $this->hasMany(SintaResearch::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Relasi ke Pengabdian Masyarakat (Services)
     */
    public function services()
    {
        return $this->hasMany(SintaService::class, 'sinta_id', 'sinta_id');
    }

    // =========================================================================
    // --- RELASI DATA STATISTIK TAHUNAN (YEARLY STATS) ---
    // =========================================================================

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