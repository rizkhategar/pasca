<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DosenSintaResource extends JsonResource
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

            'sinta_scores' => [
                'overall' => $this->sinta_score_overall ?? 0,
                '3_year' => $this->sinta_score_3yr ?? 0,
                'affil' => $this->affil_score ?? 0,
                'affil_3_year' => $this->affil_score_3yr ?? 0,
            ],

            'statistik_grafik_tahunan' => array_filter([
                'scopus' => $this->relationLoaded('scopusYearlyStats') ? $this->scopusYearlyStats : null,
                'scholar' => $this->relationLoaded('scholarYearlyStats') ? $this->scholarYearlyStats : null,
                'garuda' => $this->relationLoaded('garudaYearlyStats') ? $this->garudaYearlyStats : null,
                'penelitian' => $this->relationLoaded('researchYearlies') ? $this->researchYearlies : null,
                'pengabdian' => $this->relationLoaded('serviceYearlies') ? $this->serviceYearlies : null,
            ], fn ($value) => ! is_null($value)),

            'detail_publikasi' => array_filter([
                'scopus' => $this->relationLoaded('scopusPublications') ? $this->scopusPublications : null,
                'scholar' => $this->relationLoaded('scholarPublications') ? $this->scholarPublications : null,
                'garuda' => $this->relationLoaded('garudaPublications') ? $this->garudaPublications : null,
            ], fn ($value) => ! is_null($value)),

            'detail_kegiatan_dan_karya' => array_filter([
                'penelitian' => $this->relationLoaded('researches') ? $this->researches : null,
                'pengabdian' => $this->relationLoaded('services') ? $this->services : null,
                'buku' => $this->relationLoaded('books') ? $this->books : null,
            ], fn ($value) => ! is_null($value)),
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
