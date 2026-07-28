<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SintaLecturerFetchAllScheduleSetting extends Model
{
    protected $table = 'sinta_lecturer_fetch_all_schedule_settings';

    protected $fillable = [
        'is_enabled',
        'scheduled_time',
        'last_run_at',
        'last_skip_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting) {
            return $setting;
        }

        return static::query()->create([
            'is_enabled' => false,
            'scheduled_time' => null,
            'last_run_at' => null,
            'last_skip_reason' => null,
        ]);
    }

    public function formattedTime(): ?string
    {
        if (! $this->scheduled_time) {
            return null;
        }

        return substr((string) $this->scheduled_time, 0, 5);
    }
}
