<?php

namespace App\Filament\Resources\DetailDosens\Pages;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use App\Models\PascaLecturer;
use App\Models\PostgraduateLecturer;
use App\Models\PostgraduateLecturerDetail;
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
        $postgraduate = PostgraduateLecturer::query()->updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name' => $record->lecturer?->name,
                'institution' => $data['institution'] ?? null,
                'study_program' => $data['study_program'] ?? null,
                'profile_photo' => $record->profile_photo,
            ]
        );

        PostgraduateLecturerDetail::query()->updateOrCreate(
            ['postgraduate_lecturer_id' => $postgraduate->id],
            [
                'sinta_id' => $record->sinta_id,
                'institution' => $data['institution'] ?? null,
                'study_program' => $data['study_program'] ?? null,
                'profile_photo' => $record->profile_photo,
                'research_interests' => $record->research_interests,
                'sinta_score_overall' => $record->sinta_score_overall,
                'sinta_score_3yr' => $record->sinta_score_3yr,
                'affil_score' => $record->affil_score,
                'affil_score_3yr' => $record->affil_score_3yr,
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
