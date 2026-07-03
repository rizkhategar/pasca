<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SintaServiceYearly extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'sinta_service_yearly';

    protected $fillable = [
        'sinta_id',
        'year',
        'count',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    /**
     * Relasi balik ke dosen pemilik data statistik tahunan pengabdian
     */
    public function lecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}
