<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UndergraduateLecturerStudyProgram extends Model
{
    use HasFactory;

    protected $table = 'undergraduate_lecturer_study_programs';

    protected $fillable = [
        'undergraduate_lecturer_id',
        'study_program_id',
    ];

    protected $casts = [
        'undergraduate_lecturer_id' => 'integer',
        'study_program_id' => 'integer',
    ];

    public function lecturer()
    {
        return $this->belongsTo(UndergraduateLecturer::class, 'undergraduate_lecturer_id');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id', 'id');
    }
}
