<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('WhatsApp Admin Contacts')
                    ->description('Add one or more admin contacts. All saved admins will be shown to visitors on the Contact page.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('whatsapp_admins')
                            ->label('WhatsApp Admins')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Admin Name')
                                    ->placeholder('Example: Admissions Admin')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('number')
                                    ->label('WhatsApp Number')
                                    ->placeholder('+62 857-3033-9469')
                                    ->helperText('Example: +62 857-3033-9469.')
                                    ->required()
                                    ->tel()
                                    ->maxLength(30),
                            ])
                            ->default([
                                [
                                    'name' => 'Admin 1',
                                    'number' => '+62 857-3033-9469',
                                ],
                                [
                                    'name' => 'Admin 2',
                                    'number' => '+62 811-2758-575',
                                ],
                            ])
                            ->minItems(1)
                            ->addActionLabel('Add Admin')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'WhatsApp Admin'),
                    ]),
            ]);
    }
}
