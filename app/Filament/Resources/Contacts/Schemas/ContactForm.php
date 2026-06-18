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
                Section::make('Kontak Admin WhatsApp')
                    ->description('Tambahkan satu atau lebih admin. Semua admin yang disimpan akan tampil pada pilihan WhatsApp di halaman Kontak.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('whatsapp_admins')
                            ->label('Daftar Admin WhatsApp')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Admin')
                                    ->placeholder('Contoh: Admin Pendaftaran')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('number')
                                    ->label('Nomor WhatsApp')
                                    ->placeholder('+62 857-3033-9469')
                                    ->helperText('Gunakan kode negara Indonesia, misalnya +62 857-3033-9469.')
                                    ->required()
                                    ->tel()
                                    ->maxLength(30),
                            ])
                            ->default([
                                ['name' => 'Admin 1', 'number' => '+62 857-3033-9469'],
                                ['name' => 'Admin 2', 'number' => '+62 811-2758-575'],
                            ])
                            ->minItems(1)
                            ->addActionLabel('Tambah Admin')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Admin WhatsApp'),
                    ]),
            ]);
    }
}
