<?php

namespace App\Filament\Resources\DetailDosens\Pages;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetailDosens extends ListRecords
{
    protected static string $resource = DetailDosenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openImportPage')
                ->label('Import / Scraping SINTA')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->visible(fn (): bool => auth()->user()?->canManageContent() ?? false)
                ->url(DetailDosenResource::getUrl('import')),
        ];
    }
}
