<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Activity;

#[Fillable([
    'log_name',
    'event',
    'description',
    'subject_type',
    'subject_id',
    'causer_type',
    'causer_id',
    'attribute_changes',
    'properties',
])]
class ActivityLog extends Activity
{
    protected $table = 'activity_log';

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
