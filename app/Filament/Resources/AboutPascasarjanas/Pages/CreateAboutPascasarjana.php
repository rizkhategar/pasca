<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use App\Models\AboutPascasarjana;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
                $upload = $this->storeUpload($point['icon_upload'] ?? null, 'tentang-icons', 'about-icon');

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

        $directorUpload = $this->storeUpload($data['direktur_image_upload'] ?? null, 'direktur-images', 'direktur');

        if ($directorUpload) {
            $data['direktur_image'] = $directorUpload;
        } else {
            $data['direktur_image'] = AboutPascasarjana::normalizeImagePath($data['direktur_image'] ?? null);
        }

        Arr::forget($data, 'direktur_image_upload');

        return $data;
    }

    private function storeUpload(mixed $upload, string $directory, string $prefix): ?string
    {
        if (is_array($upload)) {
            $upload = reset($upload) ?: null;
        }

        if (! $upload) {
            return null;
        }

        if (is_object($upload) && method_exists($upload, 'storeAs')) {
            $extension = method_exists($upload, 'getClientOriginalExtension')
                ? $upload->getClientOriginalExtension()
                : 'jpg';

            return $upload->storeAs(
                $directory,
                $prefix . '-' . now()->format('YmdHis') . '-' . Str::random(8) . '.' . strtolower($extension ?: 'jpg'),
                'public'
            );
        }

        return AboutPascasarjana::normalizeImagePath($upload);
    }
}
