<?php

namespace App\Filament\Resources\OrganizationStructures;

use App\Filament\Resources\OrganizationStructures\Pages\CreateOrganizationStructure;
use App\Filament\Resources\OrganizationStructures\Pages\EditOrganizationStructure;
use App\Filament\Resources\OrganizationStructures\Pages\ListOrganizationStructures;
use App\Filament\Resources\OrganizationStructures\Schemas\OrganizationStructureForm;
use App\Filament\Resources\OrganizationStructures\Tables\OrganizationStructuresTable;
use App\Models\OrganizationalStructure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class OrganizationStructureResource extends Resource
{
    protected static ?string $model = OrganizationalStructure::class;
    protected static ?string $slug = 'organization-structures';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Organization Structure';
    protected static ?string $modelLabel = 'Organization Structure';
    protected static ?string $pluralModelLabel = 'Organization Structures';
    protected static string|UnitEnum|null $navigationGroup = 'Profil';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return OrganizationStructureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationStructuresTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationStructures::route('/'),
            'create' => CreateOrganizationStructure::route('/create'),
            'edit' => EditOrganizationStructure::route('/{record}/edit'),
        ];
    }
}
