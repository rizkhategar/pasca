<?php

namespace App\Filament\Resources\AboutPostgraduates;

use App\Filament\Resources\AboutPostgraduates\Pages;
use App\Filament\Resources\AboutPostgraduates\Schemas\AboutPostgraduateForm;
use App\Filament\Resources\AboutPostgraduates\Tables\AboutPostgraduatesTable;
use App\Models\AboutPostgraduate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AboutPostgraduateResource extends Resource
{
    protected static ?string $model = AboutPostgraduate::class;
    protected static ?string $slug = 'about-postgraduate';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'About Postgraduate';
    protected static ?string $modelLabel = 'About Postgraduate';
    protected static ?string $pluralModelLabel = 'About Postgraduate';
    protected static string|UnitEnum|null $navigationGroup = 'Profil';

    public static function form(Schema $schema): Schema
    {
        return AboutPostgraduateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AboutPostgraduatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutPostgraduates::route('/'),
            'create' => Pages\CreateAboutPostgraduate::route('/create'),
            'edit' => Pages\EditAboutPostgraduate::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return AboutPostgraduate::count() === 0;
    }
}
