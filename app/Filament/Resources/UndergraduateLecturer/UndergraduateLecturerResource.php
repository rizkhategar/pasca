<?php

namespace App\Filament\Resources\UndergraduateLecturer;

use App\Filament\Resources\PostgraduateLecturer\RelationManagers;
use App\Filament\Resources\UndergraduateLecturer\Pages;
use App\Filament\Resources\UndergraduateLecturer\Pages\EditUndergraduateLecturer;
use App\Filament\Resources\UndergraduateLecturer\Pages\ImportUndergraduateLecturer;
use App\Filament\Resources\UndergraduateLecturer\Pages\ListUndergraduateLecturers;
use App\Filament\Resources\UndergraduateLecturer\Pages\ViewUndergraduateLecturer;
use App\Filament\Resources\UndergraduateLecturer\Schemas\UndergraduateLecturerForm;
use App\Filament\Resources\UndergraduateLecturer\Tables\UndergraduateLecturerTable;
use App\Models\SintaLecturerDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UndergraduateLecturerResource extends Resource
{
    protected static ?string $model = SintaLecturerDetail::class;

    protected static ?string $slug = 'undergraduate-lecturers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Undergraduate Lecturers';
    protected static ?string $modelLabel = 'Undergraduate Lecturer';
    protected static ?string $pluralModelLabel = 'Undergraduate Lecturers';

    protected static string|UnitEnum|null $navigationGroup = 'SINTA Integration';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('undergraduateLecturer');
    }

    public static function form(Schema $schema): Schema
    {
        return UndergraduateLecturerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UndergraduateLecturerTable::configure($table);
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
            RelationManagers\ResearchYearliesRelationManager::class,
            RelationManagers\ServiceYearliesRelationManager::class,
            RelationManagers\GarudaYearlyStatsRelationManager::class,
            RelationManagers\ScholarYearlyStatsRelationManager::class,
            RelationManagers\ScopusYearlyStatsRelationManager::class,
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
