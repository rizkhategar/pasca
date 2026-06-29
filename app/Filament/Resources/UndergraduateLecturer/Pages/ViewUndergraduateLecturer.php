<?php

namespace App\Filament\Resources\UndergraduateLecturer\Pages;

use App\Filament\Resources\UndergraduateLecturer\UndergraduateLecturerResource;
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
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ResearchesRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ServicesRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\BooksRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ScopusPublicationsRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ScholarPublicationsRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\GarudaPublicationsRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ResearchYearliesRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ServiceYearliesRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\GarudaYearlyStatsRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ScholarYearlyStatsRelationManager::class,
            \App\Filament\Resources\PostgraduateLecturer\RelationManagers\ScopusYearlyStatsRelationManager::class,
        ];
    }
}
