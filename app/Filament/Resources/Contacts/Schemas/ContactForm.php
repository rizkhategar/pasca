<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('WhatsApp Admin Contacts')
                    ->description('These numbers are shown on the Contact page and open WhatsApp directly when selected by visitors.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('primary_admin_name')
                            ->label('Primary Admin Name')
                            ->default('Admin 1')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('primary_whatsapp')
                            ->label('Primary WhatsApp Number')
                            ->placeholder('+62 857-3033-9469')
                            ->helperText('Use an Indonesian WhatsApp number. Example: +62 857-3033-9469.')
                            ->required()
                            ->tel()
                            ->maxLength(30),

                        TextInput::make('secondary_admin_name')
                            ->label('Secondary Admin Name')
                            ->default('Admin 2')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('secondary_whatsapp')
                            ->label('Secondary WhatsApp Number')
                            ->placeholder('+62 811-2758-575')
                            ->helperText('Use an Indonesian WhatsApp number. Example: +62 811-2758-575.')
                            ->required()
                            ->tel()
                            ->maxLength(30),
                    ]),
            ]);
    }
}
