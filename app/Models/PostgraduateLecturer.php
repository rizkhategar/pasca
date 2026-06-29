<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostgraduateLecturer extends Model
{
    use HasFactory;

    protected $table = 'postgraduate_lecturers';

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

    public function sintaDetail()
    {
        return $this->belongsTo(SintaLecturerDetail::class, 'sinta_id', 'sinta_id');
    }

    public function detail()
    {
        return $this->hasOne(PostgraduateLecturerDetail::class);
    }

    public function studyPrograms()
    {
        return $this->belongsToMany(StudyProgram::class, 'postgraduate_lecturer_study_programs')
            ->withTimestamps();
    }
}
