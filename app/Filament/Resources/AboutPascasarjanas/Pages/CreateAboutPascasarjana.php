<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use App\Models\AboutPascasarjana;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateAboutPascasarjana extends CreateRecord
{
    protected static string $resource = AboutPascasarjanaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->mapReplacementUploads($data);
    }

    private function mapReplacementUploads(array $data): array
    {
        $data['points'] = collect($data['points'] ?? [])
            ->map(function (array $point): array {
                $upload = AboutPascasarjana::normalizeImagePath($point['icon_upload'] ?? null);

                if ($upload) {
                    $point['icon'] = $upload;
                } else {
                    $point['icon'] = AboutPascasarjana::normalizeImagePath($point['icon'] ?? null);
                }

                Arr::forget($point, 'icon_upload');

                return $point;
            })
            ->values()
            ->all();

        $directorUpload = AboutPascasarjana::normalizeImagePath($data['direktur_image_upload'] ?? null);

        if ($directorUpload) {
            $data['direktur_image'] = $directorUpload;
        } else {
            $data['direktur_image'] = AboutPascasarjana::normalizeImagePath($data['direktur_image'] ?? null);
        }

        Arr::forget($data, 'direktur_image_upload');

        return $data;
    }
}
