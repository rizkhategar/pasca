<?php

namespace App\Filament\Resources\Sliders\Schemas;

use App\Support\FilamentImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Slider')
                    ->description('Kelola teks, gambar, urutan, durasi, dan status slider homepage.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required(),

                        TextInput::make('subtitle')
                            ->label('Subtitle')
                            ->required(),

                        FileUpload::make('image_path')
                            ->label('Slider Image')
                            ->image()
                            ->disk('public')
                            ->directory('sliders')
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
                            ->saveUploadedFileUsing(
                                fn (TemporaryUploadedFile $file): string => FilamentImageUpload::saveToPublicDisk($file, 'sliders')
                            )
                            ->deleteUploadedFileUsing(
                                fn (string $file): bool => Storage::disk('public')->delete($file)
                            )
                            ->helperText('Gunakan gambar JPG, PNG, atau WEBP. Maksimal 8MB.'),

                        TextInput::make('sort_order')
                            ->label('Sort Order (Urutan)')
                            ->numeric()
                            ->default(0),

                        TextInput::make('duration_ms')
                            ->label('Duration / Durasi (Milidetik)')
                            ->numeric()
                            ->default(3000)
                            ->minValue(1000)
                            ->maxValue(30000)
                            ->helperText('Contoh: 3000 ms = 3 detik transisi.'),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
