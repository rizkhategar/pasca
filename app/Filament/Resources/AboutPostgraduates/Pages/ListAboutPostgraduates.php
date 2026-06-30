<?php

namespace App\Filament\Resources\AboutPostgraduates\Pages;

use App\Filament\Resources\AboutPostgraduates\AboutPostgraduateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAboutPostgraduates extends ListRecords
{
    protected static string $resource = AboutPostgraduateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => AboutPostgraduateResource::canCreate()),
        ];
    }
}
