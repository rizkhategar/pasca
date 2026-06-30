<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'log_name',
    'event',
    'description',
    'subject_type',
    'subject_id',
    'causer_type',
    'causer_id',
    'properties',
    'ip_address',
    'user_agent',
])]
#[Hidden(['user_agent'])]
class ActivityLog extends Model
{
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
