<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StrukturOrganisasi extends Model
{
    protected $table = 'organizational_structure';

    protected $fillable = [
        'title',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (StrukturOrganisasi $strukturOrganisasi): void {
            if ($strukturOrganisasi->image_path && Storage::disk('public')->exists($strukturOrganisasi->image_path)) {
                Storage::disk('public')->delete($strukturOrganisasi->image_path);
            }
        });
    }
}
