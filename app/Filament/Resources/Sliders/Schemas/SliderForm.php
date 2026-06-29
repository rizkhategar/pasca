<?php

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                            ->label('Upload / Ganti Gambar Slider')
                            ->image()
                            ->disk('public')
                            ->directory('sliders')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9'])
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->panelAspectRatio('16:9')
                            ->imagePreviewHeight('220')
                            ->required()
                            ->maxSize(8192)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->previewable(true)
                            ->openable()
                            ->downloadable()
                            ->helperText('Gunakan gambar landscape. Upload akan memakai crop bawaan Filament dengan rasio 16:9 dan target 1920 × 1080.'),

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
