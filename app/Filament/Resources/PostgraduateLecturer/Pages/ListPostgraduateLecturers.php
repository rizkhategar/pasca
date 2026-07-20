<?php

namespace App\Filament\Resources\PostgraduateLecturer\Pages;

use App\Filament\Resources\PostgraduateLecturer\PostgraduateLecturerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostgraduateLecturers extends ListRecords
{
    protected static string $resource = PostgraduateLecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('openImportPage')
                ->label('Import Lecturers')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->url(PostgraduateLecturerResource::getUrl('import')),
        ];
    }
}
