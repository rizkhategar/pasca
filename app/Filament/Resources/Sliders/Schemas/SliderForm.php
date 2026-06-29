<?php

namespace App\Filament\Resources\Sliders\Schemas;

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
                                            ? route('sliders.image', $record) . '?v=' . optional($record->updated_at)->timestamp
                                            : asset('storage/' . ltrim((string) $path, '/'));

                                        return new HtmlString("<img src=\"{$url}\" alt=\"Gambar slider saat ini\" style=\"width:100%;max-width:520px;aspect-ratio:16/9;max-height:293px;object-fit:cover;border-radius:14px;border:1px solid rgba(148,163,184,.35);\">");
                                    }),

                                FileUpload::make('image_path_upload')
                                    ->label('Upload / Ganti Gambar Slider')
                                    ->image()
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['16:9'])
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeMode('cover')
                                    ->imageResizeTargetWidth('1920')
                                    ->imageResizeTargetHeight('1080')
                                    ->panelAspectRatio('16:9')
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxSize(8192)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->fetchFileInformation(false)
                                    ->previewable(true)
                                    ->openable(false)
                                    ->downloadable(false)
                                    ->panelLayout('compact')
                                    ->imagePreviewHeight('180')
                                    ->loadingIndicatorPosition('right')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->uploadProgressIndicatorPosition('right')
                                    ->saveUploadedFileUsing(
                                        fn (TemporaryUploadedFile $file): string => FilamentImageUpload::saveToPublicDisk($file, 'sliders')
                                    )
                                    ->deleteUploadedFileUsing(
                                        fn (string|array|null $file): null => tap(null, fn () => FilamentImageUpload::deleteFromPublicDisk($file))
                                    )
                                    ->helperText('Upload gambar landscape. Sistem akan menyesuaikan crop ke rasio 16:9 dengan ukuran target 1920 × 1080.'),
                            ]),

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
