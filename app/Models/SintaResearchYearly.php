<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaResearchYearly extends Model
{
    use HasFactory;

    protected $table = 'sinta_research_yearly';

    protected $fillable = [
        'sinta_id',
        'year',
        'count',
    ];

    /**
     * Relasi balik ke dosen pemilik data statistik penelitian
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}
