<?php

namespace App\Filament\Resources\UndergraduateLecturer\Pages;

use App\Filament\Resources\UndergraduateLecturer\UndergraduateLecturerResource;
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
