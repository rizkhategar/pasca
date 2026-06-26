<?php

namespace App\Filament\Resources\PostgraduateLecturer;

use App\Filament\Resources\PostgraduateLecturer\Pages\CreatePostgraduateLecturer;
use App\Filament\Resources\PostgraduateLecturer\Pages\EditPostgraduateLecturer;
use App\Filament\Resources\PostgraduateLecturer\Pages\ListPostgraduateLecturers;
use App\Filament\Resources\PostgraduateLecturer\Pages\ImportPostgraduateLecturer;
use App\Filament\Resources\PostgraduateLecturer\Pages\ViewPostgraduateLecturer;
use App\Filament\Resources\PostgraduateLecturer\Schemas\PostgraduateLecturerForm;
use App\Filament\Resources\PostgraduateLecturer\Tables\PostgraduateLecturerTable;
use App\Filament\Resources\PostgraduateLecturer\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\PostgraduateLecturer\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\PostgraduateLecturer\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\PostgraduateLecturer\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\PostgraduateLecturer\RelationManagers\ScopusYearlyStatsRelationManager;
use App\Models\SintaLecturerDetail;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PostgraduateLecturerResource extends Resource
{
    protected static ?string $model = SintaLecturerDetail::class;

    protected static ?string $slug = 'postgraduate-lecturers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Postgraduate Lecturers';
    protected static ?string $modelLabel = 'Postgraduate Lecturer';
    protected static ?string $pluralModelLabel = 'Postgraduate Lecturers';

    protected static string|UnitEnum|null $navigationGroup = 'SINTA Integration';

    public static function form(Schema $schema): Schema
    {
        return PostgraduateLecturerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostgraduateLecturerTable::configure($table);
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
            'index' => Pages\ListPostgraduateLecturers::route('/'),
            'import' => Pages\ImportPostgraduateLecturer::route('/import'),
            'view' => Pages\ViewPostgraduateLecturer::route('/{record}'),
            'edit' => Pages\EditPostgraduateLecturer::route('/{record}/edit'),
        ];
    }
}
