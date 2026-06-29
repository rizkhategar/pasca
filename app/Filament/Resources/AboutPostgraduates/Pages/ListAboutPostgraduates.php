<?php

namespace App\Filament\Resources\AboutPostgraduates\Pages;

use App\Filament\Resources\AboutPostgraduates\AboutPostgraduateResource;
use App\Models\AboutPostgraduate;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAboutPostgraduates extends ListRecords
{
    protected static string $resource = AboutPostgraduateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->hidden(fn (): bool => ! (auth()->user()?->canManageContent() ?? false) || AboutPostgraduate::count() > 0),
        ];
    }
}
