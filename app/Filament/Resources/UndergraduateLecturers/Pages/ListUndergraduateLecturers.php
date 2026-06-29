<?php

namespace App\Filament\Resources\UndergraduateLecturers\Pages;

use App\Filament\Resources\UndergraduateLecturers\UndergraduateLecturerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUndergraduateLecturers extends ListRecords
{
    protected static string $resource = UndergraduateLecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openImportPage')
                ->label('Import / Scraping SINTA')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->url(UndergraduateLecturerResource::getUrl('import')),
        ];
    }
}
