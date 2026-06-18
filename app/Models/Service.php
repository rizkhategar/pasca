<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaService extends Model
{
    use HasFactory;

    protected $table = 'sinta_services';

    protected $fillable = [
        'sinta_id',
        'title',
        'leader',
        'scheme',
        'personnel',
        'year',
        'funding',
        'status',
        'source',
    ];

    /**
     * Relasi balik ke dosen pemilik data pengabdian
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}