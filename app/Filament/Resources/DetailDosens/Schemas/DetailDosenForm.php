<?php

namespace App\Filament\Resources\DetailDosens\Schemas;

use App\Support\FilamentImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DetailDosenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sinta_id')
                    ->label('SINTA ID')
                    ->required()
                    ->disabled(fn ($context) => $context === 'edit'),

                Placeholder::make('lecturer_name')
                    ->label('Nama Lengkap')
                    ->content(fn ($record) => $record?->lecturer?->name ?? '-'),

                TextInput::make('institution')
                    ->label('Institusi')
                    ->default(null),

                TextInput::make('study_program')
                    ->label('Program Studi')
                    ->default(null),

                Select::make('department')
                    ->label('Jurusan')
                    ->options(function () {
                        return \Illuminate\Support\Facades\Cache::remember('academic_programs_select_import', now()->addHours(12), function () {
                            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://panel-web.unw.ac.id/api/unw-program-studi');
                            if (! $response->successful()) {
                                return [];
                            }

                            return collect($response->json('data', []))
                                ->filter(fn ($item) => isset($item['id'], $item['nama'], $item['unwFakultas']['nama']) && trim($item['unwFakultas']['nama']) === 'Pascasarjana')
                                ->mapWithKeys(fn ($item) => [
                                    $item['id'] => trim(($item['jenjang'] ?? '') . ' ' . ($item['nama'] ?? '')),
                                ])
                                ->sortBy(fn ($value) => $value)
                                ->toArray();
                        });
                    })
                    ->searchable()
                    ->multiple()
                    ->afterStateHydrated(function (Select $component, $record) {
                        if (! $record) {
                            return;
                        }

                        $associatedDepartments = \Illuminate\Support\Facades\DB::table('departement')
                            ->where('sinta_id', $record->sinta_id)
                            ->pluck('id_departement')
                            ->toArray();

                        $component->state($associatedDepartments);
                    })
                    ->saveRelationshipsUsing(function ($record, $state) {
                        $sintaId = $record->sinta_id;
                        if (! $sintaId) {
                            return;
                        }

                        \Illuminate\Support\Facades\DB::table('departement')
                            ->where('sinta_id', $sintaId)
                            ->delete();

                        if (! empty($state)) {
                            $pivotData = collect($state)->map(function ($idDepartement) use ($sintaId) {
                                return [
                                    'sinta_id' => $sintaId,
                                    'id_departement' => $idDepartement,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            })->toArray();

                            \Illuminate\Support\Facades\DB::table('departement')->insert($pivotData);
                        }
                    })
                    ->dehydrated(false)
                    ->default(null),

                Placeholder::make('local_image_preview')
                    ->label('Foto Profil Saat Ini')
                    ->content(function ($record, $get) {
                        $sintaId = $record?->sinta_id ?? $get('sinta_id');
                        if (! $sintaId) {
                            return new HtmlString('<span class="text-gray-500 text-sm">SINTA ID belum diisi</span>');
                        }

                        $safeSintaId = Str::of($sintaId)->trim()->replaceMatches('/[^A-Za-z0-9_-]/', '')->toString();

                        $customPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";
                        $scrapedPath = "sinta-lecturers/{$safeSintaId}.jpg";

                        if (Storage::disk('public')->exists($customPath)) {
                            $customUrl = Storage::disk('public')->url($customPath) . '?v=' . time();

                            return new HtmlString("
                                <div class='flex items-center gap-4 py-2'>
                                    <img src='{$customUrl}'
                                         class='w-32 h-32 rounded-xl object-cover shadow-sm border border-success-300 dark:border-success-700'
                                         alt='Foto Kustom Dosen' />
                                    <span class='text-xs font-semibold px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-800/50'>Foto Resmi Admin (_PL)</span>
                                </div>
                            ");
                        }

                        if (Storage::disk('public')->exists($scrapedPath)) {
                            $scrapedUrl = Storage::disk('public')->url($scrapedPath) . '?v=' . time();

                            return new HtmlString("
                                <div class='flex items-center gap-4 py-2'>
                                    <img src='{$scrapedUrl}'
                                         class='w-32 h-32 rounded-xl object-cover shadow-sm border border-gray-300 dark:border-gray-700'
                                         alt='Foto Bawaan SINTA' />
                                    <span class='text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-50 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'>Foto Bawaan Scraping SINTA</span>
                                </div>
                            ");
                        }

                        return new HtmlString('<span class="text-gray-400 text-sm">Foto tidak ditemukan di storage/app/public/sinta-lecturers/</span>');
                    })
                    ->columnSpanFull(),

                FileUpload::make('image_upload')
                    ->label('Upload / Ganti Foto Profil Baru (.jpg)')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg'])
                    ->maxSize(2048)
                    ->fetchFileInformation(false)
                    ->previewable(false)
                    ->openable(false)
                    ->downloadable(false)
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->visible(fn ($context) => $context !== 'view')
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $record, $get) {
                        $sintaId = $record?->sinta_id ?? $get('sinta_id');
                        if (! $sintaId) {
                            return null;
                        }

                        $safeSintaId = Str::of($sintaId)->trim()->replaceMatches('/[^A-Za-z0-9_-]/', '')->toString();
                        if (! $safeSintaId) {
                            return null;
                        }

                        $customFileName = "{$safeSintaId}_PL.jpg";
                        $customFilePath = "sinta-lecturers/{$customFileName}";

                        if (Storage::disk('public')->exists($customFilePath)) {
                            Storage::disk('public')->delete($customFilePath);
                        }

                        return FilamentImageUpload::saveToPublicDisk($file, 'sinta-lecturers', $customFileName);
                    })
                    ->deleteUploadedFileUsing(function (string|array|null $file): void {
                        $path = is_array($file) ? collect($file)->filter()->last() : $file;

                        if (is_string($path) && trim($path) !== '') {
                            $filename = basename(str_replace('\\', '/', $path));
                            FilamentImageUpload::deleteFromPublicDisk('sinta-lecturers/' . $filename);
                        }

                        FilamentImageUpload::pruneLivewireTemporaryUploads();
                    }),
            ]);
    }
}
