<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\Lecturer\RelationManagers\BooksRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\GarudaPublicationsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ResearchesRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ScholarPublicationsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ScopusPublicationsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ScopusYearlyStatsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ServicesRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSintaLecturer extends ViewRecord
{
    protected static string $resource = SintaLecturerResource::class;

    public function getTitle(): string
    {
        return 'Detail Lecturers';
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getRelationManagers(): array
    {
        return [
            ResearchesRelationManager::class,
            ServicesRelationManager::class,
            BooksRelationManager::class,
            ScopusPublicationsRelationManager::class,
            ScholarPublicationsRelationManager::class,
            GarudaPublicationsRelationManager::class,
            ResearchYearliesRelationManager::class,
            ServiceYearliesRelationManager::class,
            GarudaYearlyStatsRelationManager::class,
            ScholarYearlyStatsRelationManager::class,
            ScopusYearlyStatsRelationManager::class,
        ];
    }
}
