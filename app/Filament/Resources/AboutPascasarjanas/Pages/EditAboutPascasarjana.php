<?php

namespace App\Filament\Resources\AboutPascasarjanas\Pages;

use App\Filament\Resources\AboutPascasarjanas\AboutPascasarjanaResource;
use App\Models\AboutPascasarjana;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                $upload = $this->storeUploadFile($point['icon_upload'] ?? null, 'tentang-icons', 'about-icon');

                if ($upload) {
                    $this->removePublicFile($oldIcon);
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
        $directorUpload = $this->storeUploadFile($data['direktur_image_upload'] ?? null, 'direktur-images', 'direktur');

        if ($directorUpload) {
            $this->removePublicFile($oldDirectorImage);
            $data['direktur_image'] = $directorUpload;
        } else {
            $data['direktur_image'] = AboutPascasarjana::normalizeImagePath($data['direktur_image'] ?? $oldDirectorImage);
        }

        Arr::forget($data, 'direktur_image_upload');

        return $data;
    }

    private function storeUploadFile(mixed $upload, string $directory, string $prefix): ?string
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

    private function removePublicFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
