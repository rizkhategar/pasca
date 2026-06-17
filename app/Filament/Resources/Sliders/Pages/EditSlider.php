<?php

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Resources\Sliders\SliderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditSlider extends EditRecord
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $uploadedImage = $this->extractUploadedPath($data['image_path_upload'] ?? null);
        $oldImage = $data['image_path'] ?? $this->record->image_path ?? null;

        if ($uploadedImage) {
            $this->deleteOldPublicFile($oldImage, $uploadedImage);
            $data['image_path'] = $uploadedImage;
        } else {
            $data['image_path'] = $oldImage;
        }

        unset($data['image_path_upload'], $data['current_image_preview']);

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

    private function deleteOldPublicFile(?string $oldPath, ?string $newPath): void
    {
        if (! $oldPath || ! $newPath || $oldPath === $newPath) {
            return;
        }

        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}
