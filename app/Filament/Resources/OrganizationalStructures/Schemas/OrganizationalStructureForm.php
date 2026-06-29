<?php

namespace App\Filament\Resources\OrganizationalStructures\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationalStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Organizational Structure')
                    ->description('Kelola judul, gambar struktur organisasi, dan status tampil di frontend.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->default('Organizational Structure')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('image_path')
                            ->label('Upload / Ganti Gambar')
                            ->image()
                            ->disk('public')
                            ->directory('organization-structures')
                            ->visibility('public')
                            ->multiple(false)
                            ->maxFiles(1)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxSize(8192)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->previewable(true)
                            ->openable()
                            ->downloadable()
                            ->imagePreviewHeight('180')
                            ->panelLayout('compact')
                            ->helperText('Upload satu gambar struktur organisasi. Untuk mengganti gambar, buka aksi Edit lalu pilih gambar baru.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
