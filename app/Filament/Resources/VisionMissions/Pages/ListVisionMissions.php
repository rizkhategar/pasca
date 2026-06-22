<?php

namespace App\Filament\Resources\VisionMissions\Pages;

use App\Filament\Resources\VisionMissions\VisionMissionResource;
use App\Models\VisionMission;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisionMissions extends ListRecords
{
    protected static string $resource = VisionMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create Vision & Mission')
                ->icon('heroicon-o-plus')
                ->hidden(fn (): bool => VisionMission::count() > 0),
        ];
    }
}
