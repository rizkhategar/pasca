<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostgraduateLecturer extends Model
{
    use HasFactory;

    protected $table = 'postgraduate_lecturer';

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
        return $this->hasMany(PostgraduateLecturerStudyProgram::class, 'postgraduate_lecturer_id');
    }

    public function studyPrograms()
    {
        return $this->belongsToMany(
            StudyProgram::class,
            'postgraduate_lecturer_study_program',
            'postgraduate_lecturer_id',
            'id_study_program',
            'id',
            'id_unw_program_studi'
        );
    }

    public function getStudyProgramIdsAttribute(): array
    {
        return $this->studyProgramPivots()->pluck('id_study_program')->toArray();
    }
}
