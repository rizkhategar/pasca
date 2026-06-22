<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaScopusYearlyStat extends Model
{
    use HasFactory;

    protected $table = 'sinta_scopus_yearly_stats';

    protected $fillable = [
        'sinta_id',
        'year',
        'count',
    ];

    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}