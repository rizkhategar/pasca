<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaServiceYearly extends Model
{
    use HasFactory;

    protected $table = 'sinta_service_yearly';

    protected $fillable = [
        'sinta_id',
        'year',
        'count',
    ];

    /**
     * Relasi balik ke dosen pemilik data statistik tahunan pengabdian
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}
