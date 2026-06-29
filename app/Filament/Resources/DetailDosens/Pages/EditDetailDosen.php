<?php

namespace App\Filament\Resources\DetailDosens\Pages;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use App\Models\PascaLecturer;
use App\Models\PostgraduateLecturer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditDetailDosen extends EditRecord
{
    protected static string $resource = DetailDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $postgraduate = PostgraduateLecturer::query()->where('sinta_id', $this->record->sinta_id)->first();
        $legacyPasca = PascaLecturer::query()->where('sinta_id', $this->record->sinta_id)->first();

        $source = $postgraduate ?? $legacyPasca;

        if ($source) {
            $data['institution'] = $source->institution;
            $data['study_program'] = $source->study_program;
        } else {
            $data['institution'] = $this->record->institution;
            $data['study_program'] = $this->record->study_program;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        PostgraduateLecturer::query()->updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name' => $record->lecturer?->name,
                'institution' => $data['institution'] ?? null,
                'study_program' => $data['study_program'] ?? null,
                'profile_photo' => $record->profile_photo,
            ]
        );

        PascaLecturer::query()->updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name' => $record->lecturer?->name,
                'institution' => $data['institution'] ?? null,
                'study_program' => $data['study_program'] ?? null,
                'profile_photo' => $record->profile_photo,
            ]
        );

        return $record;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
