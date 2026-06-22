<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    use HasFactory;

    // Menentukan nama tabel sesuai migration pivot yang Anda buat
    protected $table = 'departement';

    protected $fillable = [
        'sinta_id',
        'id_departement',
    ];

    /**
     * Relasi balik ke model Master Dosen Pasca
     */
    public function lecturer()
    {
        return $this->belongsTo(PascaLecturer::class, 'sinta_id', 'sinta_id');
    }
}