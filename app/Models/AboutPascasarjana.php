<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AboutPascasarjana extends Model
{
    protected $table = 'tentang_pascasarjanas';

    protected $fillable = [
        'hero_image',
        'subheading',
        'heading',
        'description',
        'points',
        'direktur_heading',
        'direktur_greeting',
        'direktur_name',
        'direktur_title',
        'direktur_image',
        'direktur_message',
    ];

    protected $casts = [
        'points' => 'array',
    ];

    public static function normalizeImagePath(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $path = self::normalizeImagePath($item);

                if ($path) {
                    return $path;
                }
            }

            return null;
        }

        $path = trim((string) $value);

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['[', '{'])) {
            $decoded = json_decode($path, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return self::normalizeImagePath($decoded);
            }
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^storage/#', '', $path);
        $path = preg_replace('#^public/#', '', $path);
        $path = ltrim($path, '/');

        return $path ?: null;
    }

    public static function publicImageUrl(mixed $value): ?string
    {
        $path = self::normalizeImagePath($value);

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return url('/storage/' . ltrim($path, '/'));
    }

    public static function publicImageExists(mixed $value): bool
    {
        $path = self::normalizeImagePath($value);

        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }

    public function getPointsAttribute($value): array
    {
        $points = is_array($value) ? $value : (json_decode($value ?: '[]', true) ?: []);

        return collect($points)
            ->map(function ($point) {
                if (! is_array($point)) {
                    return null;
                }

                $point['icon'] = self::normalizeImagePath($point['icon'] ?? null);

                return $point;
            })
            ->filter()
            ->values()
            ->all();
    }

    public function setPointsAttribute($value): void
    {
        $points = collect($value ?? [])
            ->map(function ($point) {
                if (! is_array($point)) {
                    return null;
                }

                $point['icon'] = self::normalizeImagePath($point['icon'] ?? null);

                return $point;
            })
            ->filter()
            ->values()
            ->all();

        $this->attributes['points'] = json_encode($points);
    }

    public function getDirekturImageAttribute($value): ?string
    {
        return self::normalizeImagePath($value);
    }

    public function setDirekturImageAttribute($value): void
    {
        $this->attributes['direktur_image'] = self::normalizeImagePath($value);
    }

    public function getHeroImageAttribute($value): ?string
    {
        return self::normalizeImagePath($value);
    }

    public function setHeroImageAttribute($value): void
    {
        $this->attributes['hero_image'] = self::normalizeImagePath($value);
    }

    public function getDirekturImageUrlAttribute(): ?string
    {
        return self::publicImageUrl($this->direktur_image);
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return self::publicImageUrl($this->hero_image);
    }
}
