<?php

namespace App\Filament\Resources\UndergraduateLecturer\Pages;

use App\Filament\Resources\UndergraduateLecturer\UndergraduateLecturerResource;
use App\Models\UndergraduateLecturer;
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
            Actions\DeleteAction::make()
                ->action(function (): void {
                    UndergraduateLecturer::where('sinta_id', $this->record->sinta_id)->delete();
                    $this->redirect(UndergraduateLecturerResource::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $undergraduateLecturer = UndergraduateLecturer::where('sinta_id', $this->record->sinta_id)->first();

        if ($undergraduateLecturer) {
            $data['name'] = $undergraduateLecturer->name;
            $data['institution'] = $undergraduateLecturer->institution;
        } else {
            $data['name'] = $this->record->name;
            $data['institution'] = $this->record->institution;
            $data['study_program'] = $this->record->study_program;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        UndergraduateLecturer::updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name' => $data['name'] ?? null,
                'institution' => $data['institution'] ?? null,
            ]
        );

        return $record;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
