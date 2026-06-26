<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostgraduateLecturerStudyProgram extends Model
{
    use HasFactory;

    protected $table = 'postgraduate_lecturer_study_program';

    protected $fillable = [
        'postgraduate_lecturer_id',
        'id_study_program',
    ];

    protected $casts = [
        'postgraduate_lecturer_id' => 'integer',
        'id_study_program' => 'integer',
    ];

    public function lecturer()
    {
        return $this->belongsTo(PostgraduateLecturer::class, 'postgraduate_lecturer_id');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'id_study_program', 'id_unw_program_studi');
    }
}
