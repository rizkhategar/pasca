<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    use HasFactory;

    protected $table = 'study_program';

    protected $fillable = [
        'id_unw_program_studi',
        'nama',
        'slug',
        'page_slug',
        'jenjang',
        'jenjang_nama_singkat',
        'unw_fakultas_id',
        'unw_fakultas_nama',
        'unw_fakultas_page_slug',
        'api_created_at',
        'api_updated_at',
        'created',
        'updated',
    ];

    protected $casts = [
        'id_unw_program_studi' => 'integer',
        'unw_fakultas_id' => 'integer',
        'api_created_at' => 'datetime',
        'api_updated_at' => 'datetime',
    ];

    public function lecturerStudyPrograms()
    {
        return $this->hasMany(PostgraduateLecturerStudyProgram::class, 'id_study_program', 'id_unw_program_studi');
    }

    public function postgraduateLecturers()
    {
        return $this->belongsToMany(
            PostgraduateLecturer::class,
            'postgraduate_lecturer_study_program',
            'id_study_program',
            'postgraduate_lecturer_id',
            'id_unw_program_studi',
            'id'
        );
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->jenjang ?? '') . ' ' . ($this->nama ?? ''));
    }
}
