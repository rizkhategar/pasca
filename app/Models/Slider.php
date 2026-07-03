<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Slider extends Model
{
    use LogsActivity;

    protected $table = 'sliders';

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'sort_order',
        'duration_ms',
        'is_active',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'duration_ms' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function normalizeImagePath(mixed $value): ?string
    {
        if (is_array($value)) {
            foreach (array_reverse($value) as $item) {
                $path = self::normalizeImagePath($item);

                if ($path) {
                    return $path;
                }
            }

            return null;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return self::normalizeImagePath($decoded);
            }
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $pathFromUrl = parse_url($value, PHP_URL_PATH);
            $value = is_string($pathFromUrl) ? $pathFromUrl : $value;
        }

        $path = trim(str_replace('\\', '/', $value));
        $path = preg_replace('#^/?storage/app/public/#', '', $path) ?: $path;
        $path = preg_replace('#^/?app/public/#', '', $path) ?: $path;
        $path = preg_replace('#^/?storage/#', '', $path) ?: $path;
        $path = preg_replace('#^/?public/#', '', $path) ?: $path;

        return ltrim($path, '/');
    }

    public static function resolveImageFilePath(mixed $value): ?string
    {
        $path = self::normalizeImagePath($value);

        if (! $path) {
            return null;
        }

        $candidates = array_values(array_unique([
            $path,
            'sliders/' . basename($path),
        ]));

        foreach ($candidates as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->path($candidate);
            }

            $storagePath = storage_path('app/public/' . ltrim($candidate, '/'));
            if (is_file($storagePath)) {
                return $storagePath;
            }

            $publicStoragePath = public_path('storage/' . ltrim($candidate, '/'));
            if (is_file($publicStoragePath)) {
                return $publicStoragePath;
            }

            $publicPath = public_path(ltrim($candidate, '/'));
            if (is_file($publicPath)) {
                return $publicPath;
            }
        }

        return null;
    }

    public function getNormalizedImagePathAttribute(): ?string
    {
        return self::normalizeImagePath($this->image_path);
    }

    public function getResolvedImageFilePathAttribute(): ?string
    {
        return self::resolveImageFilePath($this->image_path);
    }

    protected static function booted(): void
    {
        static::deleting(function (Slider $slider): void {
            $path = self::normalizeImagePath($slider->image_path);

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        });
    }
}
