<?php

namespace App\Filament\Resources\OrganizationStructures\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Struktur Organisasi')
                    ->description('Kelola judul, gambar struktur organisasi, dan status tampil di frontend.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->default('Organization Structure')
                            ->required()
                            ->maxLength(255),

                        FileUpload::make('image_path')
                            ->label('Organization Structure Image')
                            ->image()
                            ->disk('public')
                            ->directory('organization-structures')
                            ->visibility('public')
                            ->required()
                            ->maxSize(8192)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->fetchFileInformation(false)
                            ->previewable(false)
                            ->openable(false)
                            ->downloadable(false)
                            ->panelLayout('compact')
                            ->imagePreviewHeight('120')
                            ->loadingIndicatorPosition('right')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadProgressIndicatorPosition('right')
                            ->helperText('Gunakan JPG, PNG, atau WEBP. Maksimal 8MB.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
