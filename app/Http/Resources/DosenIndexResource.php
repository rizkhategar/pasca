<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DosenIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'sinta_id' => $this->sinta_id,
            'nama' => $this->nama,
            'program_studi' => $this->program_studi,
            'bidang_minat' => $this->bidang_minat,
            'profile_photo_url' => $this->resolveProfilePhotoUrl(),
        ];
    }

    private function resolveProfilePhotoUrl(): ?string
    {
        if (! $this->profile_photo) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $this->profile_photo), '/');

        if ($path === '') {
            return null;
        }

        if (! str_contains($path, '/')) {
            $path = 'sinta-lecturers/' . $path;
        }

        return Storage::disk('public')->url($path);
    }
}
