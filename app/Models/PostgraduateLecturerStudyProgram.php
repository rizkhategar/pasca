<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PostgraduateLecturerStudyProgram extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'lecturer_study_programs';

    protected $fillable = [
        'postgraduate_lecturer_id',
        'study_program_id',
    ];

    protected $casts = [
        'postgraduate_lecturer_id' => 'integer',
        'study_program_id' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function lecturer()
    {
        return $this->belongsTo(PostgraduateLecturer::class, 'postgraduate_lecturer_id');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id', 'id');
    }
}
