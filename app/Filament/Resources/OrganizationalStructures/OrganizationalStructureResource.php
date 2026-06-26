<?php

namespace App\Filament\Resources\OrganizationalStructures;

use App\Filament\Resources\OrganizationalStructures\Pages\AddStructure;
use App\Filament\Resources\OrganizationalStructures\Pages\ListOrganizationalStructures;
use App\Filament\Resources\OrganizationalStructures\Pages\ModifyStructure;
use App\Filament\Resources\OrganizationalStructures\Schemas\OrganizationalStructureForm;
use App\Filament\Resources\OrganizationalStructures\Tables\OrganizationalStructuresTable;
use App\Models\OrganizationalStructure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class OrganizationalStructureResource extends Resource
{
    protected static ?string $model = OrganizationalStructure::class;
    protected static ?string $slug = 'organizational-structure';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Organizational Structure';
    protected static ?string $modelLabel = 'Organizational Structure';
    protected static ?string $pluralModelLabel = 'Organizational Structure';
    protected static string|UnitEnum|null $navigationGroup = 'Profil';
    protected static ?int $navigationSort = 3;
    public static function form(Schema $schema): Schema { return OrganizationalStructureForm::configure($schema); }
    public static function table(Table $table): Table { return OrganizationalStructuresTable::configure($table); }
    public static function getPages(): array { return ['index' => ListOrganizationalStructures::route('/'), 'create' => AddStructure::route('/create'), 'edit' => ModifyStructure::route('/{record}/edit')]; }
}
