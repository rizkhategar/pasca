<?php

namespace App\Filament\Resources\UndergraduateLecturers\Pages;

use App\Filament\Resources\UndergraduateLecturers\UndergraduateLecturerResource;
use App\Models\UndergraduateLecturer;
use App\Models\UndergraduateLecturerDetail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUndergraduateLecturer extends EditRecord
{
    protected static string $resource = UndergraduateLecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $lecturer = $this->record->lecturer;

        if ($lecturer) {
            $data['institution'] = $lecturer->institution;
            $data['study_program'] = $lecturer->study_program;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $lecturer = UndergraduateLecturer::query()->updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name' => $record->sintaLecturer?->name,
                'institution' => $data['institution'] ?? null,
                'study_program' => $data['study_program'] ?? null,
                'profile_photo' => $record->profile_photo,
            ]
        );

        UndergraduateLecturerDetail::query()->updateOrCreate(
            ['undergraduate_lecturer_id' => $lecturer->id],
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

        return $record;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
