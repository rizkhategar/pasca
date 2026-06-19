<?php

namespace App\Filament\Resources\VisionMissions\Pages;

use App\Filament\Resources\VisionMissions\VisionMissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVisionMission extends CreateRecord
{
    protected static string $resource = VisionMissionResource::class;

    protected static bool $canCreateAnother = false;
}
