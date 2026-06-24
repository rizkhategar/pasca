<?php

namespace App\Filament\Resources\AboutPascasarjanas\Schemas;

use App\Models\AboutPascasarjana;
use App\Support\FilamentImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AboutPascasarjanaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Teks Utama Halaman')
                    ->description('Atur sub-judul, judul, dan deskripsi utama profil.')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('subheading')
                                    ->required()
                                    ->default('Tentang Kami')
                                    ->label('Sub Judul')
                                    ->placeholder('Cth: Tentang Kami'),
                                TextInput::make('heading')
                                    ->required()
                                    ->label('Judul Utama')
                                    ->placeholder('Cth: Mewujudkan Generasi Unggul'),
                            ]),
                        Textarea::make('description')
                            ->required()
                            ->label('Deskripsi Panjang')
                            ->placeholder('Masukkan penjelasan lengkap tentang pascasarjana di sini...')
                            ->rows(5),
                    ]),

                Section::make('Poin-Poin Fitur & Keunggulan')
                    ->description('Daftar fitur yang akan ditampilkan pada halaman Tentang Pascasarjana.')
                    ->icon('heroicon-o-star')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('points')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Hidden::make('icon')
                                            ->dehydrated(),

                                        Placeholder::make('icon_preview')
                                            ->label('Ikon Saat Ini')
                                            ->content(function ($get) {
                                                $path = self::extractImagePath($get('icon'))
                                                    ?? self::extractImagePath($get('icon_upload'));

                                                if (! $path) {
                                                    return new HtmlString('<span class="text-gray-500 text-sm">Belum ada ikon.</span>');
                                                }

                                                return new HtmlString(self::renderPreviewImage(
                                                    path: $path,
                                                    alt: 'Ikon saat ini',
                                                    width: 64,
                                                    height: 64,
                                                    objectFit: 'contain',
                                                    padding: 10,
                                                ));
                                            })
                                            ->columnSpan([
                                                'default' => 4,
                                                'md' => 1,
                                            ]),

                                        FileUpload::make('icon_upload')
                                            ->label('Upload / Ganti Ikon')
                                            ->image()
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->openable(false)
                                            ->downloadable(false)
                                            ->panelLayout('compact')
                                            ->imagePreviewHeight('72')
                                            ->loadingIndicatorPosition('right')
                                            ->removeUploadedFileButtonPosition('right')
                                            ->uploadProgressIndicatorPosition('right')
                                            ->saveUploadedFileUsing(
                                                fn (TemporaryUploadedFile $file): string => FilamentImageUpload::saveToPublicDisk($file, 'tentang-icons')
                                            )
                                            ->deleteUploadedFileUsing(
                                                fn (string|array|null $file): null => tap(null, fn () => FilamentImageUpload::deleteFromPublicDisk($file))
                                            )
                                            ->columnSpan([
                                                'default' => 4,
                                                'md' => 1,
                                            ]),

                                        Grid::make(1)
                                            ->schema([
                                                TextInput::make('title')
                                                    ->required()
                                                    ->label('Judul Poin')
                                                    ->placeholder('Cth: Fasilitas Lengkap'),
                                                Textarea::make('description')
                                                    ->required()
                                                    ->label('Deskripsi Singkat')
                                                    ->rows(3),
                                            ])
                                            ->columnSpan([
                                                'default' => 4,
                                                'md' => 2,
                                            ]),
                                    ]),
                            ])
                            ->defaultItems(3)
                            ->addActionLabel('Tambah Poin Baru')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(
                                fn (array $state): ?string => $state['title'] ?? 'Poin Baru'
                            ),
                    ]),

                Section::make('Sambutan Direktur Pascasarjana')
                    ->description('Tampilkan foto, sapaan, dan pesan pimpinan di bawah Tentang Kami.')
                    ->icon('heroicon-o-user-circle')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('direktur_heading')
                                    ->label('Label Sambutan (Teks Kecil)')
                                    ->default('Sambutan Direktur')
                                    ->placeholder('Cth: Sambutan Direktur'),
                                TextInput::make('direktur_greeting')
                                    ->label('Kalimat Sapaan (Teks Besar)')
                                    ->default('Selamat Datang di Pascasarjana Universitas Ngudi Waluyo')
                                    ->placeholder('Cth: Selamat Datang di...'),
                            ]),

                        Grid::make(4)
                            ->schema([
                                Hidden::make('direktur_image')
                                    ->dehydrated(),

                                Placeholder::make('direktur_image_preview')
                                    ->label('Foto Direktur Saat Ini')
                                    ->content(function ($record, $get) {
                                        $path = self::extractImagePath($get('direktur_image'))
                                            ?? self::extractImagePath($record?->direktur_image)
                                            ?? self::extractImagePath($get('direktur_image_upload'));

                                        if (! $path) {
                                            return new HtmlString('<span class="text-gray-500 text-sm">Belum ada foto direktur.</span>');
                                        }

                                        $fallbackUrl = $record
                                            ? route('about-pascasarjanas.director-image', $record) . '?v=' . optional($record->updated_at)->timestamp
                                            : self::publicStorageUrl($path);

                                        return new HtmlString(self::renderPreviewImage(
                                            path: $path,
                                            alt: 'Foto direktur saat ini',
                                            width: 120,
                                            height: 150,
                                            objectFit: 'cover',
                                            padding: 0,
                                            fallbackUrl: $fallbackUrl,
                                        ));
                                    })
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                    ]),

                                FileUpload::make('direktur_image_upload')
                                    ->label('Upload / Ganti Foto Direktur')
                                    ->image()
                                    ->maxSize(3072)
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
                                        fn (TemporaryUploadedFile $file): string => FilamentImageUpload::saveToPublicDisk($file, 'direktur-images')
                                    )
                                    ->deleteUploadedFileUsing(
                                        fn (string|array|null $file): null => tap(null, fn () => FilamentImageUpload::deleteFromPublicDisk($file))
                                    )
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 1,
                                    ]),

                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('direktur_name')
                                            ->label('Nama Lengkap (Beserta Gelar)')
                                            ->placeholder('Cth: Dr. H. Fulan, M.Pd.'),
                                        TextInput::make('direktur_title')
                                            ->label('Jabatan')
                                            ->default('Direktur Pascasarjana Universitas Ngudi Waluyo'),
                                        RichEditor::make('direktur_message')
                                            ->label('Isi Pesan / Sambutan')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'bulletList',
                                                'orderedList',
                                                'redo',
                                                'undo',
                                            ]),
                                    ])
                                    ->columnSpan([
                                        'default' => 4,
                                        'md' => 2,
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private static function extractImagePath(mixed $value): ?string
    {
        return AboutPascasarjana::normalizeImagePath($value);
    }

    private static function publicStorageUrl(string $path): string
    {
        return asset('storage/' . ltrim($path, '/')) . '?v=' . time();
    }

    private static function previewImageSource(string $path, ?string $fallbackUrl = null): string
    {
        $path = self::extractImagePath($path) ?? $path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
            $contents = Storage::disk('public')->get($path);

            return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
        }

        return $fallbackUrl ?: self::publicStorageUrl($path);
    }

    private static function renderPreviewImage(
        string $path,
        string $alt,
        int $width,
        int $height,
        string $objectFit,
        int $padding,
        ?string $fallbackUrl = null,
    ): string {
        $src = htmlspecialchars(self::previewImageSource($path, $fallbackUrl), ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
        $paddingStyle = $padding > 0 ? "padding:{$padding}px;" : '';

        return <<<HTML
            <div style="display:flex;align-items:center;gap:12px;min-height:{$height}px;">
                <img src="{$src}" alt="{$alt}" style="width:{$width}px;height:{$height}px;object-fit:{$objectFit};border-radius:16px;background:#ffffff;border:1px solid rgba(148,163,184,.35);{$paddingStyle}box-shadow:0 10px 24px rgba(15,23,42,.08);">
            </div>
        HTML;
    }
}
