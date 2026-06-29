<?php

namespace App\Filament\Resources\UndergraduateLecturers;

use App\Filament\Resources\DetailDosens\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScopusYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\UndergraduateLecturers\Schemas\UndergraduateLecturerForm;
use App\Filament\Resources\UndergraduateLecturers\Tables\UndergraduateLecturersTable;
use App\Models\UndergraduateLecturer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class UndergraduateLecturerResource extends Resource
{
    protected static ?string $model = UndergraduateLecturer::class;

    protected static ?string $slug = 'undergraduate-lecturers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Undergraduate Lecturers';
    protected static ?string $modelLabel = 'Undergraduate Lecturer';
    protected static ?string $pluralModelLabel = 'Undergraduate Lecturers';

    protected static string|UnitEnum|null $navigationGroup = 'SINTA Integration';

    public static function form(Schema $schema): Schema
    {
        return UndergraduateLecturerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UndergraduateLecturersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\DetailDosens\RelationManagers\ResearchesRelationManager::class,
            \App\Filament\Resources\DetailDosens\RelationManagers\ServicesRelationManager::class,
            \App\Filament\Resources\DetailDosens\RelationManagers\BooksRelationManager::class,
            \App\Filament\Resources\DetailDosens\RelationManagers\ScopusPublicationsRelationManager::class,
            \App\Filament\Resources\DetailDosens\RelationManagers\ScholarPublicationsRelationManager::class,
            \App\Filament\Resources\DetailDosens\RelationManagers\GarudaPublicationsRelationManager::class,
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
            'index' => Pages\ListUndergraduateLecturers::route('/'),
            'import' => Pages\ImportUndergraduateLecturer::route('/import'),
            'view' => Pages\ViewUndergraduateLecturer::route('/{record}'),
            'edit' => Pages\EditUndergraduateLecturer::route('/{record}/edit'),
        ];
    }
}
