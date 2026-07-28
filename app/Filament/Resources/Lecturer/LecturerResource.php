<?php

namespace App\Filament\Resources\Lecturer;

use App\Filament\Resources\Lecturer\Infolists\LecturerInfolist;
use App\Filament\Resources\Lecturer\Pages\CreateLecturer;
use App\Filament\Resources\Lecturer\Pages\EditLecturer;
use App\Filament\Resources\Lecturer\Pages\ListLecturers;
use App\Filament\Resources\Lecturer\Pages\ViewLecturer;
use App\Filament\Resources\Lecturer\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ScopusYearlyStatsRelationManager;
use App\Filament\Resources\Lecturer\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\Lecturer\Schemas\LecturerForm;
use App\Filament\Resources\Lecturer\Tables\LecturerTable;
use App\Models\SintaLecturerDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class LecturerResource extends Resource
{
    protected static ?string $model = SintaLecturerDetail::class;

    protected static ?string $slug = 'lecturers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Lecturers';

    protected static ?string $modelLabel = 'Lecturer';

    protected static ?string $pluralModelLabel = 'Lecturers';

    protected static string|UnitEnum|null $navigationGroup = 'SINTA Integration';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('postgraduateLecturer');
    }

    public static function form(Schema $schema): Schema
    {
        return LecturerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LecturerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LecturerTable::configure($table);
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
            'index' => Pages\ListLecturers::route('/'),
            'view' => Pages\ViewLecturer::route('/{record}'),
            'edit' => Pages\EditLecturer::route('/{record}/edit'),
        ];
    }
}
