<?php

namespace App\Filament\Resources\AboutPostgraduates\Pages;

use App\Filament\Resources\AboutPostgraduates\AboutPostgraduateResource;
use App\Support\FilamentImageUpload;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditPostgraduateAbout extends EditRecord
{
    protected static string $resource = AboutPostgraduateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Delete')->icon('heroicon-o-trash')->color('danger')->before(function (): void {
                FilamentImageUpload::deleteFromPublicDisk($this->record->direktur_image);
                foreach (($this->record->points ?? []) as $point) FilamentImageUpload::deleteFromPublicDisk($point['icon'] ?? null);
            }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $uploadedDirectorImage = $this->extractUploadedPath($data['direktur_image_upload'] ?? null);
        $oldDirectorImage = $data['direktur_image'] ?? $this->record->direktur_image ?? null;
        if ($uploadedDirectorImage) {
            $this->deleteOldPublicFile($oldDirectorImage, $uploadedDirectorImage);
            $data['direktur_image'] = $uploadedDirectorImage;
        } else {
            $data['direktur_image'] = $oldDirectorImage;
        }
        unset($data['direktur_image_upload']);

        $data['points'] = collect($data['points'] ?? [])->map(function (array $point): array {
            $uploadedIcon = $this->extractUploadedPath($point['icon_upload'] ?? null);
            $oldIcon = $point['icon'] ?? null;
            if ($uploadedIcon) {
                $this->deleteOldPublicFile($oldIcon, $uploadedIcon);
                $point['icon'] = $uploadedIcon;
            } else {
                $point['icon'] = $oldIcon;
            }
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

    private function deleteOldPublicFile(?string $oldPath, ?string $newPath): void
    {
        if (! $oldPath || ! $newPath || $oldPath === $newPath) return;
        if (Storage::disk('public')->exists($oldPath)) Storage::disk('public')->delete($oldPath);
    }
}
