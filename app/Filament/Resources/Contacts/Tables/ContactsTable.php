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
                TextColumn::make('primary_admin_name')
                    ->label('Primary Admin')
                    ->searchable(),
                TextColumn::make('primary_whatsapp')
                    ->label('Primary WhatsApp')
                    ->copyable(),
                TextColumn::make('secondary_admin_name')
                    ->label('Secondary Admin')
                    ->searchable(),
                TextColumn::make('secondary_whatsapp')
                    ->label('Secondary WhatsApp')
                    ->copyable(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Contact $record): string => ContactResource::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }
}
