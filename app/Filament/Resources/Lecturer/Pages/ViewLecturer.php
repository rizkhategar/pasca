<?php

namespace App\Filament\Resources\Lecturer\Pages;

use App\Filament\Resources\Lecturer\LecturerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLecturer extends ViewRecord
{
    protected static string $resource = LecturerResource::class;

    public function getTitle(): string
    {
        return 'Detail Lecturer';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getRelationManagers(): array
    {
        return [
            \App\Filament\Resources\Lecturer\RelationManagers\ResearchesRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\ServicesRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\BooksRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\ScopusPublicationsRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\ScholarPublicationsRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\GarudaPublicationsRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\ResearchYearliesRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\ServiceYearliesRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\GarudaYearlyStatsRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\ScholarYearlyStatsRelationManager::class,
            \App\Filament\Resources\Lecturer\RelationManagers\ScopusYearlyStatsRelationManager::class,
        ];
    }
}
