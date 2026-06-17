<?php

namespace App\Filament\Resources\OrganizationStructures\Schemas;

use App\Support\FilamentImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

                        Hidden::make('image_path'),

                        Grid::make(2)
                            ->schema([
                                Placeholder::make('current_image_preview')
                                    ->label('Gambar Saat Ini')
                                    ->content(function ($record, $get) {
                                        $path = $get('image_path');

                                        if (! $path) {
                                            return new HtmlString('<span class="text-gray-500 text-sm">Belum ada gambar.</span>');
                                        }

                                        $url = $record
                                            ? route('organization-structures.image', $record) . '?v=' . optional($record->updated_at)->timestamp
                                            : asset('storage/' . ltrim((string) $path, '/'));

                                        return new HtmlString("<img src=\"{$url}\" alt=\"Gambar struktur organisasi saat ini\" style=\"width:100%;max-width:360px;max-height:220px;object-fit:contain;border-radius:14px;background:#fff;border:1px solid rgba(148,163,184,.35);padding:8px;\">");
                                    }),

                                FileUpload::make('image_path_upload')
                                    ->label('Upload / Ganti Gambar')
                                    ->image()
                                    ->required(fn (string $operation): bool => $operation === 'create')
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
                                    ->saveUploadedFileUsing(
                                        fn (TemporaryUploadedFile $file): string => FilamentImageUpload::saveToPublicDisk($file, 'organization-structures')
                                    )
                                    ->helperText('Gunakan JPG, PNG, atau WEBP. Maksimal 8MB.'),
                            ]),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }
}
