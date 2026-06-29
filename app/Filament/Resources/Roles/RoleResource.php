<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Roles\Schemas\RoleForm;
use App\Filament\Resources\Roles\Tables\RolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $slug = 'roles-permissions';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Roles & Permissions';
    protected static ?string $modelLabel = 'Role';
    protected static ?string $pluralModelLabel = 'Roles & Permissions';
    protected static string|UnitEnum|null $navigationGroup = 'Users';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
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
