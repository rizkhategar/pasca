<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaGarudaYearlyStat extends Model
{
    use HasFactory;

    protected $table = 'sinta_garuda_yearly_stats';

    protected $fillable = [
        'sinta_id',
        'year',
        'articles',
    ];

    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}