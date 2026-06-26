<?php

namespace App\Filament\Resources\OrganizationalStructures\Schemas;

use App\Support\FilamentImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class OrganizationalStructureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Data Struktur Organisasi')
                ->description('Kelola judul, gambar struktur organisasi, dan status tampil di frontend.')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('title')->label('Title')->default('Organizational Structure')->required()->maxLength(255),
                    FileUpload::make('image_path')
                        ->label('Upload / Ganti Gambar')
                        ->image()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->maxSize(8192)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->fetchFileInformation(false)
                        ->previewable(true)
                        ->openable(false)
                        ->downloadable(false)
                        ->panelLayout('compact')
                        ->imagePreviewHeight('160')
                        ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => FilamentImageUpload::saveToPublicDisk($file, 'organization-structures'))
                        ->deleteUploadedFileUsing(fn (string|array|null $file): null => tap(null, fn () => FilamentImageUpload::deleteFromPublicDisk($file))),
                    Toggle::make('is_active')->label('Active')->default(true),
                ]),
        ]);
    }
}
