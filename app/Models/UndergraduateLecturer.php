<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UndergraduateLecturer extends Model
{
    use HasFactory;

    protected $table = 'undergraduate_lecturers';

    protected $fillable = [
        'sinta_id',
        'name',
        'institution',
        'profile_photo',
    ];

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
        return $this->hasMany(UndergraduateLecturerStudyProgram::class, 'undergraduate_lecturer_id');
    }

    public function studyPrograms()
    {
        return $this->belongsToMany(
            StudyProgram::class,
            'undergraduate_lecturer_study_programs',
            'undergraduate_lecturer_id',
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
