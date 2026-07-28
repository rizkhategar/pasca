<?php

namespace App\Filament\Resources\Lecturer\Schemas;

use App\Models\PostgraduateLecturer as Lecturer;
use App\Models\StudyProgram;
use App\Support\FilamentImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LecturerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sinta_id')
                    ->label('SINTA ID')
                    ->required()
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(null),

                TextInput::make('institution')
                    ->label('Institusi')
                    ->default(null),

                Select::make('department')
                    ->label('Program Studi')
                    ->options(function () {
                        return Cache::remember('study_programs_select_import', now()->addHours(12), function () {
                            return StudyProgram::query()
                                ->where('unw_fakultas_nama', 'Pascasarjana')
                                ->orderBy('jenjang')
                                ->orderBy('nama')
                                ->get()
                                ->mapWithKeys(fn (StudyProgram $program) => [
                                    $program->id => $program->display_name,
                                ])
                                ->toArray();
                        });
                    })
                    ->searchable()
                    ->multiple()
                    ->afterStateHydrated(function (Select $component, $record) {
                        if (! $record?->sinta_id) {
                            return;
                        }

                        $lecturer = Lecturer::where('sinta_id', $record->sinta_id)->first();

                        if (! $lecturer) {
                            $component->state([]);
                            return;
                        }

                        $associatedPrograms = DB::table('lecturer_study_programs')
                            ->where('postgraduate_lecturer_id', $lecturer->id)
                            ->pluck('study_program_id')
                            ->toArray();

                        $component->state($associatedPrograms);
                    })
                    ->saveRelationshipsUsing(function ($record, $state) {
                        $sintaId = $record->sinta_id;
                        if (! $sintaId) {
                            return;
                        }

                        $lecturer = Lecturer::where('sinta_id', $sintaId)->first();

                        if (! $lecturer) {
                            return;
                        }

                        DB::table('lecturer_study_programs')
                            ->where('postgraduate_lecturer_id', $lecturer->id)
                            ->delete();

                        if (! empty($state)) {
                            $pivotData = collect($state)
                                ->filter(fn ($studyProgramId) => filled($studyProgramId))
                                ->unique()
                                ->map(function ($studyProgramId) use ($lecturer) {
                                    return [
                                        'postgraduate_lecturer_id' => $lecturer->id,
                                        'study_program_id' => $studyProgramId,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                })
                                ->values()
                                ->toArray();

                            if (! empty($pivotData)) {
                                DB::table('lecturer_study_programs')->insert($pivotData);
                            }
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

                        $lecturer = Lecturer::where('sinta_id', $sintaId)->first();
                        $photoHtml = self::profilePhotoHtml($sintaId, $lecturer?->profile_photo);

                        if ($photoHtml !== '-') {
                            return new HtmlString($photoHtml);
                        }

                        return new HtmlString('<span class="text-gray-400 text-sm">Foto dosen belum tersedia.</span>');
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

                        $savedPath = FilamentImageUpload::saveToPublicDisk($file, 'sinta-lecturers', $customFileName);

                        Lecturer::updateOrCreate(
                            ['sinta_id' => $sintaId],
                            ['profile_photo' => $savedPath]
                        );

                        return $savedPath;
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

    private static function profilePhotoHtml(?string $sintaId, ?string $profilePhoto = null): string
    {
        $safeSintaId = Str::of((string) $sintaId)->trim()->replaceMatches('/[^A-Za-z0-9_-]/', '')->toString();
        if (! $safeSintaId) {
            return '-';
        }

        $officialPath = "sinta-lecturers/{$safeSintaId}_PL.jpg";
        $defaultPath = "sinta-lecturers/{$safeSintaId}.jpg";

        if (filled($profilePhoto)) {
            if (filter_var($profilePhoto, FILTER_VALIDATE_URL)) {
                return self::imageHtml((string) $profilePhoto, 'Foto Profil Dosen');
            }

            $normalizedPath = trim(str_replace('\\', '/', (string) $profilePhoto), '/');

            if (Storage::disk('public')->exists($normalizedPath)) {
                $caption = basename($normalizedPath) === "{$safeSintaId}_PL.jpg"
                    ? 'Foto Resmi Admin'
                    : 'Foto Bawaan SINTA';

                return self::imageHtml(Storage::disk('public')->url($normalizedPath) . '?v=' . time(), $caption);
            }
        }

        if (Storage::disk('public')->exists($officialPath)) {
            Lecturer::where('sinta_id', $safeSintaId)
                ->where(function ($query): void {
                    $query->whereNull('profile_photo')
                        ->orWhere('profile_photo', 'not like', '%_PL.jpg');
                })
                ->update(['profile_photo' => $officialPath]);

            return self::imageHtml(Storage::disk('public')->url($officialPath) . '?v=' . time(), 'Foto Resmi Admin');
        }

        if (Storage::disk('public')->exists($defaultPath)) {
            Lecturer::where('sinta_id', $safeSintaId)
                ->whereNull('profile_photo')
                ->update(['profile_photo' => $defaultPath]);

            return self::imageHtml(Storage::disk('public')->url($defaultPath) . '?v=' . time(), 'Foto Bawaan SINTA');
        }

        return '-';
    }

    private static function imageHtml(string $url, string $caption): string
    {
        $safeUrl = e($url);
        $safeCaption = e($caption);

        return "
            <div class='flex items-center gap-4 py-2'>
                <img src='{$safeUrl}'
                     class='w-32 h-32 rounded-xl object-cover shadow-sm border border-gray-300 dark:border-gray-700'
                     alt='Foto Dosen' />
                <span class='text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-50 text-gray-600 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'>{$safeCaption}</span>
            </div>
        ";
    }
}
