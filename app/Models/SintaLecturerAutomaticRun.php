<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SintaLecturerAutomaticRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_date',
        'scheduled_time',
        'fetch_batch_id',
        'status',
        'phase',
        'fetch_started_at',
        'fetch_finished_at',
        'import_started_at',
        'import_finished_at',
        'failed_sinta_ids',
        'missing_study_program_sinta_ids',
        'error_message',
        'summary_message',
    ];

    protected $casts = [
        'run_date' => 'date',
        'fetch_started_at' => 'datetime',
        'fetch_finished_at' => 'datetime',
        'import_started_at' => 'datetime',
        'import_finished_at' => 'datetime',
        'failed_sinta_ids' => 'array',
        'missing_study_program_sinta_ids' => 'array',
    ];

    public function fetchBatch(): BelongsTo
    {
        return $this->belongsTo(SintaLecturerFetchBatch::class, 'fetch_batch_id');
    }
}
