<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSintaLecturers extends ListRecords
{
    protected static string $resource = SintaLecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openImportPage')
                ->label('Import / Sync SINTA Lecturers')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->url(SintaLecturerResource::getUrl('import')),
        ];
    }
}
