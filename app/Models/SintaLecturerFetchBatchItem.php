<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SintaLecturerFetchBatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'sinta_id',
        'lecturer_name',
        'status',
        'import_status',
        'log_output',
        'error_message',
        'warning_message',
        'import_error',
        'retry_count',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(SintaLecturerFetchBatch::class, 'batch_id');
    }

    public function sintaLecturer()
    {
        return $this->belongsTo(SintaLecturer::class, 'sinta_id', 'sinta_id');
    }
}
