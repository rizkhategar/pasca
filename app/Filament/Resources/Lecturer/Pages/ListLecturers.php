<?php

namespace App\Filament\Resources\Lecturer\Pages;

use App\Filament\Resources\Lecturer\LecturerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLecturers extends ListRecords
{
    protected static string $resource = LecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openImportPage')
                ->label('Import Lecturers')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->url(LecturerResource::getUrl('import')),
        ];
    }
}
