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

        return route('public-storage.file', ['path' => $path]);
    }

    public static function publicImageExists(mixed $value): bool
    {
        $path = self::normalizeImagePath($value);

        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return false;
        }

        return Storage::disk('public')->exists($path);
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
