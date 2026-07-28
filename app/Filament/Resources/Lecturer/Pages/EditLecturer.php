<?php

namespace App\Filament\Resources\Lecturer\Pages;

use App\Filament\Resources\Lecturer\LecturerResource;
use App\Models\PostgraduateLecturer as Lecturer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditLecturer extends EditRecord
{
    protected static string $resource = LecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->label('Delete Lecturer')
                ->action(function (): void {
                    Lecturer::where('sinta_id', $this->record->sinta_id)->delete();
                    $this->redirect(LecturerResource::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $lecturer = Lecturer::where('sinta_id', $this->record->sinta_id)->first();

        $data['sinta_id'] = $lecturer?->sinta_id ?? $this->record->sinta_id;
        $data['name'] = $lecturer?->name;
        $data['institution'] = $lecturer?->institution;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $lecturer = Lecturer::where('sinta_id', $record->sinta_id)->first();

        Lecturer::updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name' => $lecturer?->name,
                'institution' => $data['institution'] ?? $lecturer?->institution,
            ]
        );

        return $record;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
