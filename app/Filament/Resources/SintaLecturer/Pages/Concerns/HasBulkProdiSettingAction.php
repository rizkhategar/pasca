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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\HtmlString;

trait HasBulkProdiSettingAction
{
    private const BULK_PRODI_MODAL_DEFAULT_LIMIT = 10;

    private const BULK_PRODI_MODAL_MAX_LIMIT = 100;

    public function settingProdiFetchAllAction(): Actions\Action
    {
        return Actions\Action::make('settingProdiFetchAll')
            ->label('Setting Prodi Fetch All')
            ->icon('heroicon-o-academic-cap')
            ->color('warning')
            ->modalHeading('Setting Prodi Fetch All')
            ->modalDescription('Popup dibuka lebih cepat. Data dimuat setelah modal tampil, deteksi prodi memakai kolom department di tabel sinta_lecturers. Jika department unknown/null atau tidak cocok, pilih manual di popup ini.')
            ->modalWidth('7xl')
            ->fillForm(fn (): array => $this->bulkProdiEmptyModalState())
            ->form([
                Hidden::make('bulk_loaded')->dehydrated(false),
                Hidden::make('bulk_is_loading')->dehydrated(false),
                Placeholder::make('bulk_loader')
                    ->hiddenLabel()
                    ->content(fn ($get): HtmlString => new HtmlString($this->bulkProdiLazyLoaderHtml(
                        loaded: (bool) $get('bulk_loaded'),
                        loading: (bool) $get('bulk_is_loading'),
                    ))),
                Section::make('Filter Data')
                    ->description('Search aktif saat mengetik. Opsi Belum disetting / Null menampilkan dosen yang belum punya setting tersimpan. Simpan dulu perubahan sebelum mengganti filter agar editan tidak hilang.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('filter_search')
                                    ->label('Cari Nama Dosen / SINTA ID')
                                    ->placeholder('Ketik nama atau SINTA ID...')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($set, $get, ?string $state): void {
                                        $this->reloadBulkProdiMountedActionData($set, $get, [
                                            'filter_search' => $state,
                                            'filter_page' => 1,
                                        ]);
                                    }),
                                Select::make('filter_study_program_id')
                                    ->label('Filter Program Studi')
                                    ->options(fn (): array => $this->getStudyProgramFilterOptions())
                                    ->searchable()
                                    ->native(false)
                                    ->live()
                                    ->placeholder('Semua program studi')
                                    ->afterStateUpdated(function ($set, $get, mixed $state): void {
                                        $this->reloadBulkProdiMountedActionData($set, $get, [
                                            'filter_study_program_id' => $state,
                                            'filter_page' => 1,
                                        ]);
                                    }),
                                Select::make('filter_limit')
                                    ->label('Jumlah Data Ditampilkan')
                                    ->options([
                                        10 => '10 data',
                                        20 => '20 data',
                                        30 => '30 data',
                                        50 => '50 data',
                                        100 => '100 data',
                                    ])
                                    ->native(false)
                                    ->live()
                                    ->default(self::BULK_PRODI_MODAL_DEFAULT_LIMIT)
                                    ->afterStateUpdated(function ($set, $get, mixed $state): void {
                                        $this->reloadBulkProdiMountedActionData($set, $get, [
                                            'filter_limit' => $state,
                                            'filter_page' => 1,
                                        ]);
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
                Section::make('Pagination')
                    ->description('Gunakan tombol Previous / Next seperti pagination Filament. Data halaman dimuat saat tombol diklik, bukan saat popup pertama dibuka.')
                    ->schema([
                        Placeholder::make('pagination_controls')
                            ->hiddenLabel()
                            ->content(fn ($get): HtmlString => new HtmlString($this->bulkProdiPaginationControlsHtml(
                                search: $get('filter_search'),
                                studyProgramFilter: $get('filter_study_program_id'),
                                limit: $get('filter_limit'),
                                page: $get('filter_page'),
                                loading: (bool) $get('bulk_is_loading'),
                            ))),
                    ]),
            ])
            ->modalSubmitActionLabel('Simpan Setting Prodi')
            ->action(function (array $data): void {
                $this->saveBulkProdiSettings($data);
            });
    }

    public function loadBulkProdiSettingsForMountedAction(): void
    {
        $data = $this->bulkProdiMountedActionData();

        if ((bool) data_get($data, 'bulk_loaded')) {
            return;
        }

        $this->setBulkProdiMountedActionData($this->bulkProdiBuildLoadedModalState($data));
    }

    public function previousBulkProdiPageForMountedAction(): void
    {
        $data = $this->bulkProdiMountedActionData();
        $page = max(1, ((int) data_get($data, 'filter_page', 1)) - 1);

        $this->setBulkProdiMountedActionData($this->bulkProdiBuildLoadedModalState($data, [
            'filter_page' => $page,
        ]));
    }

    public function nextBulkProdiPageForMountedAction(): void
    {
        $data = $this->bulkProdiMountedActionData();
        $currentPage = max(1, (int) data_get($data, 'filter_page', 1));
        $limit = $this->resolveBulkProdiModalLimit(data_get($data, 'filter_limit'));
        $total = $this->countBulkProdiSettingRows(
            search: data_get($data, 'filter_search'),
            studyProgramFilter: data_get($data, 'filter_study_program_id'),
        );
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($totalPages, $currentPage + 1);

        $this->setBulkProdiMountedActionData($this->bulkProdiBuildLoadedModalState($data, [
            'filter_page' => $page,
        ]));
    }

    protected function bulkProdiEmptyModalState(): array
    {
        return [
            'bulk_loaded' => false,
            'bulk_is_loading' => true,
            'filter_search' => null,
            'filter_study_program_id' => null,
            'filter_limit' => self::BULK_PRODI_MODAL_DEFAULT_LIMIT,
            'filter_page' => 1,
            'lecturers' => [],
        ];
    }

    protected function bulkProdiLazyLoaderHtml(bool $loaded, bool $loading): string
    {
        if ($loaded) {
            return '<div style="padding:0.75rem;border-radius:0.5rem;background-color:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.18);color:#047857;font-weight:500;">Data sudah dimuat. Gunakan filter atau pagination untuk memuat halaman lain.</div>';
        }

        $loadingText = $loading ? 'Memuat data pertama...' : 'Menyiapkan modal...';

        return <<<HTML
        <div x-data x-init="setTimeout(() => { if (typeof \$wire !== 'undefined') { \$wire.call('loadBulkProdiSettingsForMountedAction') } }, 150)" style="padding:0.875rem;border-radius:0.5rem;background-color:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);color:#1d4ed8;font-weight:500;">
            ⏳ {$loadingText} Popup sudah terbuka, data akan muncul setelah server selesai membaca batch dan department SINTA.
        </div>
        HTML;
    }

    protected function bulkProdiPaginationControlsHtml(mixed $search = null, mixed $studyProgramFilter = null, mixed $limit = self::BULK_PRODI_MODAL_DEFAULT_LIMIT, mixed $page = 1, bool $loading = false): string
    {
        $resolvedLimit = $this->resolveBulkProdiModalLimit($limit);
        $resolvedPage = $this->resolveBulkProdiModalPage($page);
        $total = $this->countBulkProdiSettingRows($search, $studyProgramFilter);
        $totalPages = max(1, (int) ceil($total / $resolvedLimit));
        $from = $total > 0 ? (($resolvedPage - 1) * $resolvedLimit) + 1 : 0;
        $to = min($resolvedPage * $resolvedLimit, $total);
        $previousDisabled = $resolvedPage <= 1 || $loading;
        $nextDisabled = $resolvedPage >= $totalPages || $loading;
        $disabledStyle = 'opacity:0.45;cursor:not-allowed;';
        $buttonStyle = 'display:inline-flex;align-items:center;justify-content:center;border-radius:0.5rem;border:1px solid #d1d5db;background:#fff;color:#374151;font-weight:600;padding:0.5rem 0.75rem;min-width:96px;';
        $info = $loading ? 'Sedang memuat data...' : "Menampilkan {$from}-{$to} dari {$total} data. Halaman {$resolvedPage} dari {$totalPages}.";

        return sprintf(
            '<div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;padding:0.75rem;border:1px solid #e5e7eb;border-radius:0.75rem;background:#fafafa;">' .
            '<div style="color:#374151;font-weight:500;">%s</div>' .
            '<div style="display:flex;align-items:center;gap:0.5rem;">' .
            '<button type="button" wire:click="previousBulkProdiPageForMountedAction" %s style="%s%s">Previous</button>' .
            '<span style="color:#6b7280;font-weight:600;min-width:80px;text-align:center;">%s / %s</span>' .
            '<button type="button" wire:click="nextBulkProdiPageForMountedAction" %s style="%s%s">Next</button>' .
            '</div></div>',
            e($info),
            $previousDisabled ? 'disabled' : '',
            $buttonStyle,
            $previousDisabled ? $disabledStyle : '',
            e((string) $resolvedPage),
            e((string) $totalPages),
            $nextDisabled ? 'disabled' : '',
            $buttonStyle,
            $nextDisabled ? $disabledStyle : '',
        );
    }

    protected function bulkProdiMountedActionData(): array
    {
        return data_get($this, 'mountedActionsData.0', []);
    }

    protected function setBulkProdiMountedActionData(array $data): void
    {
        $this->mountedActionsData[0] = $data;
    }

    protected function reloadBulkProdiMountedActionData($set, $get, array $overrides = []): void
    {
        $base = [
            'bulk_loaded' => true,
            'bulk_is_loading' => true,
            'filter_search' => data_get($overrides, 'filter_search', $get('filter_search')),
            'filter_study_program_id' => data_get($overrides, 'filter_study_program_id', $get('filter_study_program_id')),
            'filter_limit' => data_get($overrides, 'filter_limit', $get('filter_limit')),
            'filter_page' => data_get($overrides, 'filter_page', $get('filter_page') ?: 1),
            'lecturers' => [],
        ];

        $set('bulk_is_loading', true);
        $set('lecturers', []);

        $loaded = $this->bulkProdiBuildLoadedModalState($base);

        foreach ($loaded as $key => $value) {
            $set($key, $value);
        }
    }

    protected function bulkProdiBuildLoadedModalState(array $base = [], array $overrides = []): array
    {
        $state = array_merge($this->bulkProdiEmptyModalState(), $base, $overrides);
        $state['bulk_loaded'] = true;
        $state['bulk_is_loading'] = true;
        $state['filter_limit'] = $this->resolveBulkProdiModalLimit(data_get($state, 'filter_limit'));
        $state['filter_page'] = $this->resolveBulkProdiModalPage(data_get($state, 'filter_page'));
        $state['lecturers'] = $this->getBulkProdiSettingRows(
            search: data_get($state, 'filter_search'),
            studyProgramFilter: data_get($state, 'filter_study_program_id'),
            limit: data_get($state, 'filter_limit'),
            page: data_get($state, 'filter_page'),
        );
        $state['bulk_is_loading'] = false;

        return $state;
    }

    protected function getBulkProdiSettingRows(?string $search = null, mixed $studyProgramFilter = null, mixed $limit = self::BULK_PRODI_MODAL_DEFAULT_LIMIT, mixed $page = 1): array
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

        $normalizedSearch = trim(strtolower((string) $search));
        $studyProgramFilter = filled($studyProgramFilter) ? (string) $studyProgramFilter : null;
        $resolvedLimit = $this->resolveBulkProdiModalLimit($limit);
        $resolvedPage = $this->resolveBulkProdiModalPage($page);

        $items = $this->getCachedBulkProdiPageItems(
            batch: $batch,
            normalizedSearch: $normalizedSearch,
            studyProgramFilter: $studyProgramFilter,
            limit: $resolvedLimit,
            page: $resolvedPage,
        );

        if ($items->isEmpty()) {
            return [];
        }

        $settings = SintaLecturerStudyProgramSetting::query()
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
                $query->whereNotIn('sinta_id', SintaLecturerStudyProgramSetting::query()->select('sinta_id'));
            })
            ->when($studyProgramFilter && $studyProgramFilter !== '__null__', function ($query) use ($studyProgramFilter): void {
                $query->whereIn('sinta_id', SintaLecturerStudyProgramSetting::query()
                    ->where('study_program_id', (int) $studyProgramFilter)
                    ->select('sinta_id'));
            });
    }

    protected function getCachedBulkProdiPageItems(SintaLecturerFetchBatch $batch, string $normalizedSearch, ?string $studyProgramFilter, int $limit, int $page): Collection
    {
        $cacheKey = $this->bulkProdiPageCacheKey($batch->id, $normalizedSearch, $studyProgramFilter, $limit, $page);

        $itemIds = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($batch, $normalizedSearch, $studyProgramFilter, $limit, $page): array {
            return $this->bulkProdiItemsQuery($batch, $normalizedSearch, $studyProgramFilter)
                ->orderBy('lecturer_name')
                ->orderBy('sinta_id')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();
        });

        if (empty($itemIds)) {
            return collect();
        }

        $position = array_flip($itemIds);

        return $batch->items()
            ->select(['id', 'batch_id', 'sinta_id', 'lecturer_name', 'status', 'import_status', 'warning_message', 'error_message'])
            ->whereIn('id', $itemIds)
            ->get()
            ->sortBy(fn (SintaLecturerFetchBatchItem $item) => $position[(int) $item->id] ?? 999999)
            ->values();
    }

    protected function countBulkProdiSettingRows(?string $search = null, mixed $studyProgramFilter = null): int
    {
        if (! $this->bulkProdiBatchTablesReady()) {
            return 0;
        }

        $batch = SintaLecturerFetchBatch::query()->latest('id')->first();

        if (! $batch) {
            return 0;
        }

        $normalizedSearch = trim(strtolower((string) $search));
        $studyProgramFilter = filled($studyProgramFilter) ? (string) $studyProgramFilter : null;

        return (int) $this->bulkProdiItemsQuery($batch, $normalizedSearch, $studyProgramFilter)->count();
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

        $this->bumpBulkProdiPageCacheVersion();

        Notification::make()
            ->title('Setting prodi berhasil disimpan')
            ->body('Mapping program studi untuk data yang sedang tampil sudah diperbarui.')
            ->success()
            ->send();
    }

    protected function getStudyProgramFilterOptions(): array
    {
        return ['__null__' => 'Belum disetting / Null'] + $this->getStudyProgramOptions();
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

    protected function resolveBulkProdiModalLimit(mixed $limit): int
    {
        $limit = (int) $limit;

        if ($limit <= 0) {
            return self::BULK_PRODI_MODAL_DEFAULT_LIMIT;
        }

        return min($limit, self::BULK_PRODI_MODAL_MAX_LIMIT);
    }

    protected function resolveBulkProdiModalPage(mixed $page): int
    {
        return max(1, (int) $page);
    }

    protected function bulkProdiPageCacheVersion(): int
    {
        return (int) Cache::get('sinta_import_bulk_prodi_page_cache_version', 1);
    }

    protected function bumpBulkProdiPageCacheVersion(): void
    {
        Cache::forever('sinta_import_bulk_prodi_page_cache_version', $this->bulkProdiPageCacheVersion() + 1);
    }

    protected function bulkProdiPageCacheKey(int $batchId, string $normalizedSearch, ?string $studyProgramFilter, int $limit, int $page): string
    {
        $filterHash = md5(json_encode([
            'search' => $normalizedSearch,
            'study_program_filter' => $studyProgramFilter,
            'limit' => $limit,
            'page' => $page,
            'version' => $this->bulkProdiPageCacheVersion(),
        ]));

        return "sinta_import_bulk_prodi_page_items:{$batchId}:{$filterHash}";
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

    protected function detectStudyProgramFromSintaDepartment(string $sintaId): ?string
    {
        return $this->bulkProdiStudyProgramDetector()->detectRawDepartment($sintaId);
    }

    protected function suggestStudyProgramIdsFromSintaDepartment(string $sintaId, ?Collection $programs = null): Collection
    {
        return $this->bulkProdiStudyProgramDetector()->suggestStudyProgramIdsFromDepartment($sintaId, $programs);
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
