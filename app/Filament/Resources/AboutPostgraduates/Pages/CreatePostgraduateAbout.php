<?php

namespace App\Filament\Resources\AboutPostgraduates\Pages;

use App\Filament\Resources\AboutPostgraduates\AboutPostgraduateResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostgraduateAbout extends CreateRecord
{
    protected static string $resource = AboutPostgraduateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $uploadedDirectorImage = $this->extractUploadedPath($data['direktur_image_upload'] ?? null);
        if ($uploadedDirectorImage) $data['direktur_image'] = $uploadedDirectorImage;
        unset($data['direktur_image_upload']);

        $data['points'] = collect($data['points'] ?? [])->map(function (array $point): array {
            $uploadedIcon = $this->extractUploadedPath($point['icon_upload'] ?? null);
            if ($uploadedIcon) $point['icon'] = $uploadedIcon;
            unset($point['icon_upload'], $point['icon_preview']);
            return $point;
        })->values()->all();

        return $data;
    }

    private function extractUploadedPath(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') return $value;
        if (is_array($value)) foreach (array_reverse($value) as $item) { $path = $this->extractUploadedPath($item); if ($path) return $path; }
        return null;
    }
}
