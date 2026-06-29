<?php

namespace App\Filament\Resources\ShieldRoles;

use App\Filament\Resources\ShieldRoles\Pages\IndexShieldRoles;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use UnitEnum;

class ShieldRoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $slug = 'filament-shield/roles';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static string|UnitEnum|null $navigationGroup = 'Filament Shield';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => IndexShieldRoles::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageAccounts() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
