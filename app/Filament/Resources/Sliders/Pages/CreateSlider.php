<?php

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Resources\Sliders\SliderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSlider extends CreateRecord
{
    protected static string $resource = SliderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['image_path'] = $this->extractUploadedPath($data['image_path_upload'] ?? null)
            ?? ($data['image_path'] ?? null);

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
}
