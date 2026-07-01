<?php

namespace App\Filament\Resources\SintaLecturer;

use App\Filament\Resources\PostgraduateLecturer\Infolists\PostgraduateLecturerInfolist;
use App\Filament\Resources\SintaLecturer\Pages;
use App\Filament\Resources\SintaLecturer\Schemas\SintaLecturerForm;
use App\Filament\Resources\SintaLecturer\Tables\SintaLecturerTable;
use App\Models\SintaLecturer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SintaLecturerResource extends Resource
{
    protected static ?string $model = SintaLecturer::class;

    protected static ?string $slug = 'sinta-lecturers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'SINTA Lecturers';
    protected static ?string $modelLabel = 'SINTA Lecturer';
    protected static ?string $pluralModelLabel = 'SINTA Lecturers';

    protected static string|UnitEnum|null $navigationGroup = 'SINTA Integration';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SintaLecturerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostgraduateLecturerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SintaLecturerTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSintaLecturers::route('/'),
            'import' => Pages\ImportSintaLecturers::route('/import'),
            'view' => Pages\ViewSintaLecturer::route('/{record}'),
        ];
    }
}
