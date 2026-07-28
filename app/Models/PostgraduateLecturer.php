<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PostgraduateLecturer extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'lecturers';

    protected $fillable = [
        'sinta_id',
        'name',
        'institution',
        'profile_photo',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function sintaLecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }

    public function sintaDetail()
    {
        return $this->belongsTo(SintaLecturerDetail::class, 'sinta_id', 'sinta_id');
    }

    public function studyProgramPivots()
    {
        return $this->hasMany(PostgraduateLecturerStudyProgram::class, 'postgraduate_lecturer_id');
    }

    public function studyPrograms()
    {
        return $this->belongsToMany(
            StudyProgram::class,
            'lecturer_study_programs',
            'postgraduate_lecturer_id',
            'study_program_id',
            'id',
            'id'
        );
    }

    public function getStudyProgramIdsAttribute(): array
    {
        return $this->studyProgramPivots()->pluck('study_program_id')->toArray();
    }
}
