<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    use HasFactory;

    protected $table = 'study_programs';

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
        return $this->hasMany(PostgraduateLecturerStudyProgram::class, 'study_program_id', 'id');
    }

    public function postgraduateLecturers()
    {
        return $this->belongsToMany(
            PostgraduateLecturer::class,
            'postgraduate_lecturer_study_programs',
            'study_program_id',
            'postgraduate_lecturer_id',
            'id',
            'id'
        );
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->jenjang ?? '') . ' ' . ($this->nama ?? ''));
    }
}
