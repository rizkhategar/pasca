<?php

namespace App\Filament\Resources\Contacts\Tables;

use App\Filament\Resources\Contacts\ContactResource;
use App\Models\Contact;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('whatsapp_admins')
                    ->label('WhatsApp Admins')
                    ->getStateUsing(function (Contact $record): string {
                        return collect($record->resolvedWhatsAppAdmins())
                            ->map(fn (array $admin): string => $admin['name'] . ' — ' . $admin['number'])
                            ->implode("\n");
                    })
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (Contact $record): bool => ContactResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (Contact $record): bool => ContactResource::canDelete($record)),
            ]);
    }
}
