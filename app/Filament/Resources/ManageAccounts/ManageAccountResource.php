<?php

namespace App\Filament\Resources\ManageAccounts;

use App\Filament\Resources\ManageAccounts\Pages\CreateManageAccount;
use App\Filament\Resources\ManageAccounts\Pages\EditManageAccount;
use App\Filament\Resources\ManageAccounts\Pages\ListManageAccounts;
use App\Filament\Resources\ManageAccounts\Schemas\ManageAccountForm;
use App\Filament\Resources\ManageAccounts\Tables\ManageAccountsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ManageAccountResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'users';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'Users';
    protected static string|UnitEnum|null $navigationGroup = 'Users';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema { return ManageAccountForm::configure($schema); }
    public static function table(Table $table): Table { return ManageAccountsTable::configure($table); }
    public static function getPages(): array { return ['index' => ListManageAccounts::route('/'), 'create' => CreateManageAccount::route('/create'), 'edit' => EditManageAccount::route('/{record}/edit')]; }
    public static function canViewAny(): bool { return auth()->user()?->canManageAccounts() ?? false; }
    public static function canCreate(): bool { return auth()->user()?->canManageAccounts() ?? false; }
    public static function canEdit(Model $record): bool { return auth()->user()?->canManageAccounts() ?? false; }
    public static function canDelete(Model $record): bool { return (auth()->user()?->canManageAccounts() ?? false) && auth()->id() !== $record->getKey(); }
}
