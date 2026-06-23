<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PascaLecturer extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'pasca_lecturers';

    // Konfigurasi Primary Key karena menggunakan SINTA ID (String & Non-Incrementing)
    protected $primaryKey = 'sinta_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Daftar kolom yang diizinkan untuk Mass Assignment
    protected $fillable = [
        'sinta_id',
        'name',
        'institution',
        'study_program',
        'profile_photo',
    ];

    /**
     * Relasi ke master dosen SINTA.
     */
    public function sintaLecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }

    public function sintaDetail()
    {
        return $this->belongsTo(SintaLecturerDetail::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Relasi ke tabel pivot Departement (Many-to-Many lokal)
     */
    public function departments()
    {
        return $this->hasMany(Departement::class, 'sinta_id', 'sinta_id');
    }

    /**
     * Accessor untuk mempermudah pemanggilan array ID jurusan
     */
    public function getDepartmentIdsAttribute(): array
    {
        return $this->departments()->pluck('id_departement')->toArray();
    }
}