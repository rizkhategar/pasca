<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAboutPascasarjana extends CreateRecord
{
    protected static string $resource = AboutPascasarjanaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['direktur_image'] = $this->extractUploadedPath($data['direktur_image_upload'] ?? null)
            ?? ($data['direktur_image'] ?? null);

        unset($data['direktur_image_upload']);

        $data['points'] = collect($data['points'] ?? [])
            ->map(function (array $point): array {
                $uploadedIcon = $this->extractUploadedPath($point['icon_upload'] ?? null);

                if ($uploadedIcon) {
                    $point['icon'] = $uploadedIcon;
                }

                unset($point['icon_upload'], $point['icon_preview']);

                return $point;
            })
            ->values()
            ->all();

        return $data;
    }

    private function extractUploadedPath(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_array($value)) {
            foreach (array_reverse($value) as $item) {
                $path = $this->extractUploadedPath($item);

                if ($path) {
                    return $path;
                }
            }
        }

        return null;
    }
}
