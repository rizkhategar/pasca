<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    use HasFactory;

    protected $table = 'study_programs';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'jenjang',
        'faculty_name',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function getDisplayNameAttribute(): string
    {
        return trim(collect([$this->jenjang, $this->name])->filter()->implode(' '));
    }

    public function postgraduateLecturers()
    {
        return $this->belongsToMany(PostgraduateLecturer::class, 'postgraduate_lecturer_study_programs')
            ->withTimestamps();
    }

    public function undergraduateLecturers()
    {
        return $this->belongsToMany(UndergraduateLecturer::class, 'undergraduate_lecturer_study_programs')
            ->withTimestamps();
    }
}
