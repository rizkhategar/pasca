<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaScholarYearlyStat extends Model
{
    use HasFactory;

    protected $table = 'sinta_scholar_yearly_stats';

    protected $fillable = [
        'sinta_id',
        'year',
        'publications',
        'citations',
    ];

    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}