<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role_label')
                    ->label('Role')
                    ->state(fn (User $record): string => $record->getRoleNames()->map(fn (string $role): string => Str::headline($role))->implode(', ') ?: '-')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Impersonate::make()
                    ->redirectTo(url('/admin'))
                    ->visible(fn (User $record): bool => (auth()->user()?->canImpersonate() ?? false) && $record->canBeImpersonated()),
                EditAction::make()
                    ->visible(fn (User $record): bool => UserResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => UserResource::canDelete($record)),
            ]);
    }
}
