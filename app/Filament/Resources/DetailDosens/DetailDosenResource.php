<?php

namespace App\Filament\Resources\DetailDosens;

use App\Filament\Resources\DetailDosens\Pages\CreateDetailDosen;
use App\Filament\Resources\DetailDosens\Pages\EditDetailDosen;
use App\Filament\Resources\DetailDosens\Pages\ListDetailDosens;
use App\Filament\Resources\DetailDosens\Pages\ImportDetailDosen;
use App\Filament\Resources\DetailDosens\Pages\ViewDetailDosen;
use App\Filament\Resources\DetailDosens\Schemas\DetailDosenForm; 
use App\Filament\Resources\DetailDosens\Tables\DetailDosensTable; 
use App\Filament\Resources\DetailDosens\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScopusYearlyStatsRelationManager;
use App\Models\SintaLecturerDetail; // <-- Tetap mengarah ke Model Baru
use BackedEnum;
use UnitEnum;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DetailDosenResource extends Resource
{
    // Menggunakan model baru agar sinkron dengan struktur DB baru
    protected static ?string $model = SintaLecturerDetail::class; 

    protected static ?string $slug = 'detail-dosens';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    // PERBAIKAN: Mengubah label navigasi dan model menjadi Bahasa Inggris
    protected static ?string $navigationLabel = 'Manage Lecturers';
    protected static ?string $modelLabel = 'Lecturer Detail';
    protected static ?string $pluralModelLabel = 'Manage Lecturers';

    protected static string|UnitEnum|null $navigationGroup = 'SINTA Integration';
    
    public static function form(Schema $schema): Schema
    {
        return DetailDosenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DetailDosensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ResearchesRelationManager::class,
            RelationManagers\ServicesRelationManager::class,
            RelationManagers\BooksRelationManager::class,
            RelationManagers\ScopusPublicationsRelationManager::class,
            RelationManagers\ScholarPublicationsRelationManager::class,
            RelationManagers\GarudaPublicationsRelationManager::class,

            ResearchYearliesRelationManager::class,
            ServiceYearliesRelationManager::class,
            GarudaYearlyStatsRelationManager::class,
            ScholarYearlyStatsRelationManager::class,
            ScopusYearlyStatsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetailDosens::route('/'),
            'import' => Pages\ImportDetailDosen::route('/import'),
            'view' => Pages\ViewDetailDosen::route('/{record}'), 
            'edit' => Pages\EditDetailDosen::route('/{record}/edit'),
        ];
    }
}