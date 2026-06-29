<?php

namespace App\Filament\Resources\PostgraduateLecturer\Pages;

use App\Filament\Resources\PostgraduateLecturer\PostgraduateLecturerResource;
use App\Models\PostgraduateLecturer;
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
                ->action(function (): void {
                    PostgraduateLecturer::where('sinta_id', $this->record->sinta_id)->delete();
                    $this->redirect(PostgraduateLecturerResource::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $postgraduateLecturer = PostgraduateLecturer::where('sinta_id', $this->record->sinta_id)->first();

        if ($postgraduateLecturer) {
            $data['name']        = $postgraduateLecturer->name;
            $data['institution'] = $postgraduateLecturer->institution;
        } else {
            $data['name']          = $this->record->name;
            $data['institution']   = $this->record->institution;
            $data['study_program'] = $this->record->study_program;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        PostgraduateLecturer::updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name'        => $data['name'] ?? null,
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
