<?php

namespace App\Filament\Resources\DetailDosens;

use App\Filament\Resources\DetailDosens\Pages\CreateDetailDosen;
use App\Filament\Resources\DetailDosens\Pages\EditDetailDosen;
use App\Filament\Resources\DetailDosens\Pages\ImportDetailDosen;
use App\Filament\Resources\DetailDosens\Pages\ListDetailDosens;
use App\Filament\Resources\DetailDosens\Pages\ViewDetailDosen;
use App\Filament\Resources\DetailDosens\RelationManagers\GarudaYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ResearchYearliesRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScholarYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ScopusYearlyStatsRelationManager;
use App\Filament\Resources\DetailDosens\RelationManagers\ServiceYearliesRelationManager;
use App\Filament\Resources\DetailDosens\Schemas\DetailDosenForm;
use App\Filament\Resources\DetailDosens\Tables\DetailDosensTable;
use App\Models\SintaLecturerDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use UnitEnum;

class DetailDosenResource extends Resource
{
    protected static ?string $model = SintaLecturerDetail::class;

    protected static ?string $slug = 'detail-dosens';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Postgraduate Lecturers';
    protected static ?string $modelLabel = 'Postgraduate Lecturer';
    protected static ?string $pluralModelLabel = 'Postgraduate Lecturers';

    protected static string|UnitEnum|null $navigationGroup = 'SINTA Integration';

    public static function getEloquentQuery(): Builder
    {
        if (! SchemaFacade::hasTable('postgraduate_lecturers')) {
            return parent::getEloquentQuery()->whereHas('pascaLecturer');
        }

        return parent::getEloquentQuery()
            ->where(function (Builder $query): void {
                $query->whereHas('postgraduateLecturer')
                    ->orWhereHas('pascaLecturer');
            });
    }

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
