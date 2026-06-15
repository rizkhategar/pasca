<?php

namespace App\Filament\Resources\AboutPascasarjanas\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;

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
                                        FileUpload::make('icon')
                                            ->image()
                                            ->directory('tentang-icons')
                                            ->disk('public')
                                            ->visibility('public')
                                            ->maxSize(2048)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                                            ->fetchFileInformation(false)
                                            ->previewable(false)
                                            ->openable(false)
                                            ->downloadable(false)
                                            ->deletable(true)
                                            ->deleteUploadedFileUsing(fn (?string $file): bool => filled($file) ? Storage::disk('public')->delete($file) : true)
                                            ->panelLayout('compact')
                                            ->loadingIndicatorPosition('right')
                                            ->removeUploadedFileButtonPosition('right')
                                            ->uploadProgressIndicatorPosition('right')
                                            ->label('Upload Ikon')
                                            ->helperText('Gambar lama tidak dimuat preview supaya edit lebih cepat. Upload baru akan mengganti file lama.')
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
                                                'md' => 3,
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
                                FileUpload::make('direktur_image')
                                    ->image()
                                    ->directory('direktur-images')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->maxSize(3072)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->fetchFileInformation(false)
                                    ->previewable(false)
                                    ->openable(false)
                                    ->downloadable(false)
                                    ->deletable(true)
                                    ->deleteUploadedFileUsing(fn (?string $file): bool => filled($file) ? Storage::disk('public')->delete($file) : true)
                                    ->panelLayout('compact')
                                    ->loadingIndicatorPosition('right')
                                    ->removeUploadedFileButtonPosition('right')
                                    ->uploadProgressIndicatorPosition('right')
                                    ->label('Foto Direktur (Potret/Berdiri)')
                                    ->helperText('Gambar lama tidak dimuat preview supaya edit lebih cepat. Upload baru akan mengganti file lama.')
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
                                        'md' => 3,
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
