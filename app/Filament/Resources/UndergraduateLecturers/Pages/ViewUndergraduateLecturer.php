<?php

namespace App\Filament\Resources\UndergraduateLecturers\Pages;

use App\Filament\Resources\DetailDosens\RelationManagers\BooksRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\GarudaPublicationsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ResearchesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScholarPublicationsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScopusPublicationsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScopusYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ServicesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\UndergraduateLecturers\UndergraduateLecturerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewUndergraduateLecturer extends ViewRecord
{
    protected static string $resource = UndergraduateLecturerResource::class;

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
