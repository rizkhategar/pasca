<?php

namespace App\Filament\Resources\UndergraduateLecturers\Pages;

use App\Filament\Resources\UndergraduateLecturers\UndergraduateLecturerResource;
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

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update([
            'name' => $record->sintaLecturer?->name ?? $record->name,
            'institution' => $data['institution'] ?? null,
            'study_program' => $data['study_program'] ?? null,
            'profile_photo' => $record->profile_photo,
        ]);

        return $record;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
