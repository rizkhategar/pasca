<?php

namespace App\Filament\Resources\ShieldRoles;

use App\Filament\Resources\ShieldRoles\Pages\CreateShieldRole;
use App\Filament\Resources\ShieldRoles\Pages\ListShieldRoles;
use App\Filament\Resources\ShieldRoles\Pages\UpdateShieldRole;
use App\Filament\Resources\ShieldRoles\Schemas\ShieldRoleForm;
use App\Filament\Resources\ShieldRoles\Tables\ShieldRolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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
        return ShieldRoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShieldRolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShieldRoles::route('/'),
            'create' => CreateShieldRole::route('/create'),
            'edit' => UpdateShieldRole::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageAccounts() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageAccounts() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->canManageAccounts() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return (auth()->user()?->canManageAccounts() ?? false) && $record->name !== 'super_admin';
    }
}
