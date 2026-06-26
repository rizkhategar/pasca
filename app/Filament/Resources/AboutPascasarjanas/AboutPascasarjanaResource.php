<?php

namespace App\Filament\Resources\AboutPascasarjanas; 

use App\Filament\Resources\AboutPascasarjanas\Pages;
use App\Filament\Resources\AboutPascasarjanas\Schemas\AboutPascasarjanaForm;
use App\Filament\Resources\AboutPascasarjanas\Tables\AboutPascasarjanasTable;
use App\Models\AboutPostgraduate;
use BackedEnum; 
use UnitEnum;   
use Filament\Resources\Resource;
use Filament\Schemas\Schema; 
use Filament\Tables\Table;

class AboutPascasarjanaResource extends Resource
{
    protected static ?string $model = AboutPostgraduate::class;
    
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';
    
    protected static ?string $navigationLabel = 'About Pascasarjana';
    protected static ?string $pluralModelLabel = 'About Pascasarjana';
    
    protected static string|UnitEnum|null $navigationGroup = 'Profil';

    public static function form(Schema $schema): Schema
    {
        return AboutPascasarjanaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AboutPascasarjanasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutPascasarjanas::route('/'),
            'create' => Pages\CreateAboutPascasarjana::route('/create'),
            'edit' => Pages\EditAboutPascasarjana::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return AboutPostgraduate::count() === 0;
    }
}
