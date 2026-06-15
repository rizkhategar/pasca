<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use App\Models\AboutPascasarjana;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class EditAboutPascasarjana extends EditRecord
{
    protected static string $resource = AboutPascasarjanaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mapReplacementUploads($data);
    }

    private function mapReplacementUploads(array $data): array
    {
        $oldPoints = collect($this->record->points ?? []);

        $data['points'] = collect($data['points'] ?? [])
            ->map(function (array $point, int $index) use ($oldPoints): array {
                $oldIcon = AboutPascasarjana::normalizeImagePath(data_get($oldPoints, $index . '.icon'));
                $upload = AboutPascasarjana::normalizeImagePath($point['icon_upload'] ?? null);

                if ($upload) {
                    $this->deletePublicFile($oldIcon);
                    $point['icon'] = $upload;
                } else {
                    $point['icon'] = AboutPascasarjana::normalizeImagePath($point['icon'] ?? $oldIcon);
                }

                Arr::forget($point, 'icon_upload');

                return $point;
            })
            ->values()
            ->all();

        $oldDirectorImage = AboutPascasarjana::normalizeImagePath($this->record->direktur_image);
        $directorUpload = AboutPascasarjana::normalizeImagePath($data['direktur_image_upload'] ?? null);

        if ($directorUpload) {
            $this->deletePublicFile($oldDirectorImage);
            $data['direktur_image'] = $directorUpload;
        } else {
            $data['direktur_image'] = AboutPascasarjana::normalizeImagePath($data['direktur_image'] ?? $oldDirectorImage);
        }

        Arr::forget($data, 'direktur_image_upload');

        return $data;
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
