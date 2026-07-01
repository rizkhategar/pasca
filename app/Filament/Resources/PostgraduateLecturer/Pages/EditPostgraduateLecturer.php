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

        $data['sinta_id'] = $postgraduateLecturer?->sinta_id ?? $this->record->sinta_id;
        $data['name'] = $postgraduateLecturer?->name;
        $data['institution'] = $postgraduateLecturer?->institution;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $postgraduateLecturer = PostgraduateLecturer::where('sinta_id', $record->sinta_id)->first();

        PostgraduateLecturer::updateOrCreate(
            ['sinta_id' => $record->sinta_id],
            [
                'name' => $postgraduateLecturer?->name,
                'institution' => $data['institution'] ?? $postgraduateLecturer?->institution,
            ]
        );

        return $record;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
}
