<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostgraduateLecturerStudyProgram extends Model
{
    use HasFactory;

    protected $table = 'postgraduate_lecturer_study_programs';

    protected $fillable = [
        'postgraduate_lecturer_id',
        'study_program_id',
    ];

    protected $casts = [
        'postgraduate_lecturer_id' => 'integer',
        'study_program_id' => 'integer',
    ];

    public function lecturer()
    {
        return $this->belongsTo(PostgraduateLecturer::class, 'postgraduate_lecturer_id');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id', 'id');
    }
}
