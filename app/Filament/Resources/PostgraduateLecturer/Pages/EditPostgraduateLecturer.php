<?php

namespace App\Filament\Resources\PostgraduateLecturer\Pages;

use App\Filament\Resources\PostgraduateLecturer\PostgraduateLecturerResource;
use App\Models\PostgraduateLecturer as Lecturer;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPostgraduateLecturer extends EditRecord
{
    protected static string $resource = PostgraduateLecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->label('Delete Lecturer')
                ->action(function (): void {
                    Lecturer::where('sinta_id', $this->record->sinta_id)->delete();
                    $this->redirect(PostgraduateLecturerResource::getUrl('index'));
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
