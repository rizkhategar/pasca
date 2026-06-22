<?php

namespace App\Filament\Resources\VisionMissions;

use App\Filament\Resources\VisionMissions\Pages\CreateVisionMission;
use App\Filament\Resources\VisionMissions\Pages\EditVisionMission;
use App\Filament\Resources\VisionMissions\Pages\ListVisionMissions;
use App\Filament\Resources\VisionMissions\Schemas\VisionMissionForm;
use App\Filament\Resources\VisionMissions\Tables\VisionMissionsTable;
use App\Models\VisionMission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class VisionMissionResource extends Resource
{
    protected static ?string $model = VisionMission::class;

    protected static ?string $slug = 'vision-missions';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Vision & Mission';

    protected static ?string $modelLabel = 'Vision & Mission';

    protected static ?string $pluralModelLabel = 'Vision & Mission';

    protected static string|UnitEnum|null $navigationGroup = 'Profile';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return VisionMissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisionMissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisionMissions::route('/'),
            'create' => CreateVisionMission::route('/create'),
            'edit' => EditVisionMission::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return VisionMission::count() === 0;
    }
}
