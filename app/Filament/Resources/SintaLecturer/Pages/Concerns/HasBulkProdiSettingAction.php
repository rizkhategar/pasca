<?php

namespace App\Filament\Resources\SintaLecturer\Pages\Concerns;

use App\Filament\Resources\SintaLecturer\Services\SintaLecturerStudyProgramCacheWarmer;
use App\Filament\Resources\SintaLecturer\Services\SintaLecturerStudyProgramDetector;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as SchemaFacade;

trait HasBulkProdiSettingAction
{
    public function settingProdiFetchAllAction(): Actions\Action
    {
        return Actions\Action::make('settingProdiFetchAll')
            ->label('Setting Prodi Fetch All')
            ->icon('heroicon-o-academic-cap')
            ->color('warning')
            ->modalHeading('Setting Prodi Fetch All')
            ->modalDescription('Semua data dari batch Fetch All terakhir dimuat langsung saat popup dibuka. Tidak ada pagination. Deteksi prodi memakai kolom department di tabel sinta_lecturers. Jika department unknown/null atau tidak cocok, pilih manual di popup ini.')
            ->modalWidth('7xl')
            ->fillForm(fn (): array => [
                'filter_search' => null,
                'filter_study_program_id' => null,
                'lecturers' => $this->getBulkProdiSettingRows(),
            ])
            ->form([
                Section::make('Filter Data')
                    ->description('Filter akan memuat ulang semua baris yang cocok. Simpan dulu perubahan sebelum mengganti filter agar editan yang belum disimpan tidak hilang.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('filter_search')
                                    ->label('Cari Nama Dosen / SINTA ID')
                                    ->placeholder('Ketik nama atau SINTA ID...')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($set, $get, ?string $state): void {
                                        $set('lecturers', $this->getBulkProdiSettingRows(
                                            search: $state,
                                            studyProgramFilter: $get('filter_study_program_id'),
                                        ));
                                    }),
                                Select::make('filter_study_program_id')
                                    ->label('Filter Program Studi')
                                    ->options(fn (): array => $this->getStudyProgramFilterOptions())
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->placeholder('Semua program studi')
                                    ->afterStateUpdated(function ($set, $get, mixed $state): void {
                                        $set('lecturers', $this->getBulkProdiSettingRows(
                                            search: $get('filter_search'),
                                            studyProgramFilter: $state,
                                        ));
                                    }),
                            ]),
                    ]),
                Repeater::make('lecturers')
                    ->label('Daftar Dosen Fetch All')
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                TextInput::make('sinta_id')
                                    ->label('SINTA ID')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 2,
                                    ]),
                                TextInput::make('lecturer_name')
                                    ->label('Nama Dosen')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),
                                TextInput::make('fetch_status')
                                    ->label('Status Fetch')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 2,
                                    ]),
                                TextInput::make('detected_study_program')
                                    ->label('Department SINTA')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->placeholder('Tidak terdeteksi / unknown')
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 5,
                                    ]),
                                Select::make('study_program_ids')
                                    ->label('Program Studi')
                                    ->options(fn (): array => $this->getStudyProgramOptions())
                                    ->multiple()
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('Pilih satu atau beberapa program studi')
                                    ->columnSpan(12),
                                TextInput::make('setting_status')
                                    ->label('Status Setting')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->columnSpan([
                                        'default' => 12,
                                        'md' => 3,
                                    ]),
                            ]),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->itemLabel(fn (array $state): ?string => trim(($state['lecturer_name'] ?? 'Dosen') . ' - ' . ($state['sinta_id'] ?? '')))
                    ->columns(1),
            ])
            ->modalSubmitActionLabel('Simpan Setting Prodi')
            ->action(function (array $data): void {
                $this->saveBulkProdiSettings($data);
            });
    }

    protected function getBulkProdiSettingRows(?string $search = null, mixed $studyProgramFilter = null): array
    {
        if (! $this->bulkProdiBatchTablesReady()) {
            Notification::make()
                ->title('Setting Prodi belum bisa dibuka')
                ->body('Jalankan php artisan migrate terlebih dahulu agar tabel batch tersedia.')
                ->danger()
                ->send();

            return [];
        }

        $batch = SintaLecturerFetchBatch::query()->latest('id')->first();

        if (! $batch) {
            return [];
        }

        $items = $this->bulkProdiItemsQuery(
            batch: $batch,
            normalizedSearch: trim(strtolower((string) $search)),
            studyProgramFilter: filled($studyProgramFilter) ? (string) $studyProgramFilter : null,
        )
            ->orderBy('lecturer_name')
            ->orderBy('sinta_id')
            ->get();

        if ($items->isEmpty()) {
            return [];
        }

        $settings = $this->validStudyProgramSettingsQuery()
            ->whereIn('sinta_id', $items->pluck('sinta_id')->filter()->values())
            ->get()
            ->groupBy('sinta_id');

        $departments = SintaLecturer::query()
            ->whereIn('sinta_id', $items->pluck('sinta_id')->filter()->values())
            ->pluck('department', 'sinta_id');

        $detector = $this->bulkProdiStudyProgramDetector();
        $programModels = $detector->getStudyProgramModels();

        return $items
            ->map(function (SintaLecturerFetchBatchItem $item) use ($settings, $departments, $detector, $programModels): array {
                $sintaId = (string) $item->sinta_id;
                $existing = $settings->get($sintaId, collect())
                    ->pluck('study_program_id')
                    ->map(fn ($id) => (int) $id)
                    ->values();

                if ($existing->isNotEmpty()) {
                    $selected = $existing;
                    $detectedStudyProgram = 'Sudah tersimpan di database';
                } else {
                    $department = $departments->get($sintaId);
                    $department = is_string($department) ? trim($department) : null;
                    $detectedStudyProgram = ($department && ! $detector->isUnknownDepartment($department)) ? $department : null;
                    $selected = $detectedStudyProgram
                        ? $detector->suggestStudyProgramIds($detectedStudyProgram, $programModels)
                        : collect();
                }

                $canSet = in_array($item->status, ['success', 'success_with_warning'], true);

                return [
                    'sinta_id' => $sintaId,
                    'lecturer_name' => (string) ($item->lecturer_name ?: '-'),
                    'fetch_status' => (string) $item->status,
                    'detected_study_program' => (string) ($detectedStudyProgram ?? ''),
                    'study_program_ids' => $selected->map(fn ($id) => (int) $id)->values()->toArray(),
                    'setting_status' => $canSet
                        ? ($existing->isNotEmpty() ? 'complete' : ($selected->isNotEmpty() ? 'auto_suggested' : 'not_set'))
                        : 'blocked',
                ];
            })
            ->values()
            ->toArray();
    }

    protected function bulkProdiItemsQuery(SintaLecturerFetchBatch $batch, string $normalizedSearch = '', ?string $studyProgramFilter = null)
    {
        return $batch->items()
            ->select(['id', 'batch_id', 'sinta_id', 'lecturer_name', 'status', 'import_status', 'warning_message', 'error_message'])
            ->when($normalizedSearch !== '', function ($query) use ($normalizedSearch): void {
                $query->where(function ($subQuery) use ($normalizedSearch): void {
                    $subQuery->whereRaw('LOWER(lecturer_name) LIKE ?', ["%{$normalizedSearch}%"])
                        ->orWhere('sinta_id', 'like', "%{$normalizedSearch}%");
                });
            })
            ->when($studyProgramFilter === '__null__', function ($query): void {
                $query->whereNotIn('sinta_id', $this->validStudyProgramSettingsQuery()->select('sinta_id'));
            })
            ->when($studyProgramFilter && $studyProgramFilter !== '__null__', function ($query) use ($studyProgramFilter): void {
                $query->whereIn('sinta_id', $this->validStudyProgramSettingsQuery()
                    ->where('study_program_id', (int) $studyProgramFilter)
                    ->select('sinta_id'));
            });
    }

    protected function validStudyProgramSettingsQuery()
    {
        return SintaLecturerStudyProgramSetting::query()
            ->whereNotNull('study_program_id')
            ->whereIn('study_program_id', StudyProgram::query()->select('id'));
    }

    protected function saveBulkProdiSettings(array $data): void
    {
        $rows = collect(data_get($data, 'lecturers', []));
        $userId = Auth::id();

        DB::transaction(function () use ($rows, $userId): void {
            foreach ($rows as $row) {
                $sintaId = preg_replace('/[^0-9]/', '', (string) data_get($row, 'sinta_id'));

                if (! $sintaId) {
                    continue;
                }

                $selectedStudyProgramIds = collect(data_get($row, 'study_program_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values();

                $validStudyProgramIds = StudyProgram::query()
                    ->whereIn('id', $selectedStudyProgramIds)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values();

                SintaLecturerStudyProgramSetting::where('sinta_id', $sintaId)->delete();

                foreach ($validStudyProgramIds as $studyProgramId) {
                    SintaLecturerStudyProgramSetting::create([
                        'sinta_id' => $sintaId,
                        'study_program_id' => $studyProgramId,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                }
            }
        });

        Notification::make()
            ->title('Setting prodi berhasil disimpan')
            ->body('Mapping program studi untuk data yang sedang tampil sudah diperbarui.')
            ->success()
            ->send();
    }

    protected function getStudyProgramFilterOptions(): array
    {
        return ['__null__' => 'Belum dipilih / Null'] + $this->getStudyProgramOptions();
    }

    protected function getStudyProgramOptions(): array
    {
        return Cache::remember('sinta_import_study_program_options_v3', now()->addMinutes(10), function (): array {
            return StudyProgram::query()
                ->orderBy('jenjang')
                ->orderBy('nama')
                ->get()
                ->mapWithKeys(fn (StudyProgram $program): array => [
                    $program->id => $program->display_name,
                ])
                ->toArray();
        });
    }

    protected function bulkProdiBatchTablesReady(): bool
    {
        return SchemaFacade::hasTable('sinta_lecturer_fetch_batches')
            && SchemaFacade::hasTable('sinta_lecturer_fetch_batch_items')
            && SchemaFacade::hasTable('sinta_lecturer_study_program_settings');
    }

    protected function bulkProdiStudyProgramDetector(): SintaLecturerStudyProgramDetector
    {
        return app(SintaLecturerStudyProgramDetector::class);
    }

    protected function bulkProdiStudyProgramCacheWarmer(): SintaLecturerStudyProgramCacheWarmer
    {
        return app(SintaLecturerStudyProgramCacheWarmer::class);
    }

    protected function queueDepartmentProdiCacheWarmForLatestBatch(int $limit = 100): void
    {
        $this->bulkProdiStudyProgramCacheWarmer()->queueForLatestBatch($limit);
    }

    protected function queueDepartmentProdiCacheWarm(string $sintaId): void
    {
        $this->bulkProdiStudyProgramCacheWarmer()->queueForSintaId($sintaId);
    }
}
