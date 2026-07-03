<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrganizationalStructure extends Model
{
    use LogsActivity;

    protected $table = 'organizational_structure';

    protected $fillable = ['title', 'image_path', 'is_active'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleting(function (OrganizationalStructure $record): void {
            if ($record->image_path && Storage::disk('public')->exists($record->image_path)) {
                Storage::disk('public')->delete($record->image_path);
            }
        });
    }
}
