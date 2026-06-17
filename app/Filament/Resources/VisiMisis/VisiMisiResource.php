<?php

namespace App\Filament\Resources\VisiMisis;

use App\Filament\Resources\VisiMisis\Pages\CreateVisiMisi;
use App\Filament\Resources\VisiMisis\Pages\EditVisiMisi;
use App\Filament\Resources\VisiMisis\Pages\ListVisiMisis;
use App\Filament\Resources\VisiMisis\Schemas\VisiMisiForm;
use App\Filament\Resources\VisiMisis\Tables\VisiMisisTable;
use App\Models\VisiMisi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class VisiMisiResource extends Resource
{
    protected static ?string $model = VisiMisi::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Vision & Mission';

    protected static ?string $modelLabel = 'Vision & Mission';

    protected static ?string $pluralModelLabel = 'Vision & Mission';

    protected static string|UnitEnum|null $navigationGroup = 'Profil';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return VisiMisiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisiMisisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisiMisis::route('/'),
            'create' => CreateVisiMisi::route('/create'),
            'edit' => EditVisiMisi::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return VisiMisi::count() === 0;
    }
}
