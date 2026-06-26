<?php

namespace App\Filament\Resources\DetailDosens\Pages;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use App\Models\PostgraduateLecturer;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
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
