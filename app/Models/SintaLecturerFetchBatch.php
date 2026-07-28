<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaLecturerFetchBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'total_items',
        'processed_items',
        'success_items',
        'warning_items',
        'failed_items',
        'current_sinta_id',
        'error_message',
        'started_at',
        'paused_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SintaLecturerFetchBatchItem::class, 'batch_id');
    }
}
