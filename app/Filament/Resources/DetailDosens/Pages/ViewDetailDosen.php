<?php

namespace App\Filament\Resources\DetailDosens\Pages;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use Filament\Resources\Pages\ViewRecord;

// --- IMPORT SEMUA RELATION MANAGERS (LEBIH RAPI & BERSIH) ---
use App\Filament\Resources\DetailDosens\RelationManagers\ResearchesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ServicesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\BooksRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScopusPublicationsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScholarPublicationsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\GarudaPublicationsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScopusYearlyStatsRelationManager;

class ViewDetailDosen extends ViewRecord
{
    protected static string $resource = DetailDosenResource::class;

    /**
     * Menyatukan view detail data utama dengan data relasi di bawahnya menjadi sistem TAB
     */
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    /**
     * Memanggil daftar komponen sub-tabel relasi publikasi dan statistik tahunan
     */
    public function getRelationManagers(): array
    {
        return [
            ResearchesRelationManager::class,
            ServicesRelationManager::class,
            BooksRelationManager::class,
            ScopusPublicationsRelationManager::class,
            ScholarPublicationsRelationManager::class,
            GarudaPublicationsRelationManager::class,

            // Data Statistik Tahunan (Yearly Stats)
            ResearchYearliesRelationManager::class,
            ServiceYearliesRelationManager::class,
            GarudaYearlyStatsRelationManager::class,
            ScholarYearlyStatsRelationManager::class,
            ScopusYearlyStatsRelationManager::class,
        ];
    }
}