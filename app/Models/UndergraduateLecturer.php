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
        'study_program',
        'profile_photo',
    ];

    public function sintaLecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }

    public function detail()
    {
        return $this->hasOne(UndergraduateLecturerDetail::class);
    }

    public function studyPrograms()
    {
        return $this->belongsToMany(StudyProgram::class, 'undergraduate_lecturer_study_programs')
            ->withTimestamps();
    }
}
