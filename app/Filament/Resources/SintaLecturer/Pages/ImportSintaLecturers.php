<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Jobs\WarmSintaLecturerExcelProdiCacheJob;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchAllScheduleSetting;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class ImportSintaLecturers extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    private const BULK_PRODI_MODAL_DEFAULT_LIMIT = 10;

    private const BULK_PRODI_MODAL_MAX_LIMIT = 100;

    protected static string $resource = SintaLecturerResource::class;

    protected string $view = 'filament.resources.sinta-lecturer.pages.import-sinta-lecturers';

    protected static ?string $title = 'Import Lecturers';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->queueExcelProdiCacheWarmForLatestBatch();
    }

    public function setTimerFetchAllAction(): Actions\Action
    {
        return Actions\Action::make('setTimerFetchAll')
            ->label('Set Timer Fetch All')
            ->icon('heroicon-o-clock')
            ->color('info')
            ->modalHeading('Set Timer Fetch All')
            ->modalDescription('Configure automatic Fetch All to run once per day at the selected time. Make sure the Laravel scheduler and queue worker are running.')
            ->fillForm(function (): array {
                $setting = SintaLecturerFetchAllScheduleSetting::current();

                return [
                    'is_enabled' => (bool) $setting->is_enabled,
                    'scheduled_time' => $setting->formattedTime(),
                ];
            })
            ->form([
                Toggle::make('is_enabled')
                    ->label('Enable automatic fetch all')
                    ->inline(false)
                    ->live(),
                TimePicker::make('scheduled_time')
                    ->label('Fetch All Time')
                    ->seconds(false)
                    ->native(false)
                    ->required(fn ($get): bool => (bool) $get('is_enabled'))
                    ->helperText('Runs once per day using the application timezone: ' . config('app.timezone')),
                Placeholder::make('timer_note')
                    ->hiddenLabel()
                    ->content(new HtmlString('<div style="padding:0.75rem;border-radius:0.5rem;background-color:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);color:#1d4ed8;font-size:0.875rem;">The timer only dispatches Fetch All to the queue. Keep <b>php artisan schedule:work</b> and <b>php artisan queue:work</b> running for local development.</div>')),
            ])
            ->modalSubmitActionLabel('Save Timer')
            ->action(function (array $data): void {
                $enabled = (bool) data_get($data, 'is_enabled', false);
                $time = data_get($data, 'scheduled_time');
                $time = is_string($time) ? substr(trim($time), 0, 5) : null;

                if ($enabled && blank($time)) {
                    Notification::make()
                        ->title('Fetch All timer was not saved')
                        ->body('Please choose a time before enabling automatic Fetch All.')
                        ->danger()
                        ->send();

                    return;
                }

                SintaLecturerFetchAllScheduleSetting::current()->update([
                    'is_enabled' => $enabled,
                    'scheduled_time' => $enabled ? $time : null,
                    'last_skip_reason' => null,
                ]);

                Notification::make()
                    ->title('Fetch All timer saved')
                    ->body($enabled ? "Automatic Fetch All is enabled at {$time}." : 'Automatic Fetch All is disabled.')
                    ->success()
                    ->send();
            });
    }

    public function settingProdiFetchAllAction(): Actions\Action
    {
        return Actions\Action::make('settingProdiFetchAll')
            ->label('Setting Prodi Fetch All')
            ->icon('heroicon-o-academic-cap')
            ->color('warning')
            ->modalHeading('Setting Prodi Fetch All')
            ->modalDescription('Data awal hanya 10 dosen agar popup cepat. Data yang sudah punya setting prodi di database langsung dipakai tanpa baca Excel. Data yang belum punya setting akan membaca cache Excel untuk halaman aktif, sementara halaman berikutnya dipanaskan di background.')
            ->modalWidth('7xl')
            ->fillForm(fn (): array => [
                'filter_search' => null,
                'filter_study_program_id' => null,
                'filter_limit' => self::BULK_PRODI_MODAL_DEFAULT_LIMIT,
                'filter_page' => 1,
                'lecturers' => $this->getBulkProdiSettingRows(limit: self::BULK_PRODI_MODAL_DEFAULT_LIMIT, page: 1),
            ])
            ->form([
                Section::make('Filter Data')
                    ->description('Search langsung aktif saat mengetik. Opsi Belum disetting / Null menampilkan dosen yang belum punya setting tersimpan. Simpan dulu perubahan sebelum mengganti filter agar editan tidak hilang.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('filter_search')
                                    ->label('Cari Nama Dosen / SINTA ID')
                                    ->placeholder('Ketik nama atau SINTA ID...')
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(function ($set, $get, ?string $state): void {
                                        $set('lecturers', $this->getBulkProdiSettingRows(
                                            search: $state,
                                            studyProgramFilter: $get('filter_study_program_id'),
                                            limit: $get('filter_limit'),
                                            page: 1,
                                        ));
                                        $set('filter_page', 1);
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
                                            limit: $get('filter_limit'),
                                            page: 1,
                                        ));
                                        $set('filter_page', 1);
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
                                        $set('lecturers', $this->getBulkProdiSettingRows(
                                            search: $get('filter_search'),
                                            studyProgramFilter: $get('filter_study_program_id'),
                                            limit: $state,
                                            page: 1,
                                        ));
                                        $set('filter_page', 1);
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
                                    ->label('Prodi Terdeteksi dari Excel')
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->placeholder('Tidak terdeteksi')
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
                    ->description('Pindah halaman untuk memuat 10 data berikutnya. Halaman yang sudah pernah dibuka memakai cache deteksi prodi, dan halaman berikutnya disiapkan otomatis di background.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('filter_page')
                                    ->label('Halaman')
                                    ->options(fn ($get): array => $this->getBulkProdiPageOptions(
                                        search: $get('filter_search'),
                                        studyProgramFilter: $get('filter_study_program_id'),
                                        limit: $get('filter_limit'),
                                    ))
                                    ->default(1)
                                    ->native(false)
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get, mixed $state): void {
                                        $set('lecturers', $this->getBulkProdiSettingRows(
                                            search: $get('filter_search'),
                                            studyProgramFilter: $get('filter_study_program_id'),
                                            limit: $get('filter_limit'),
                                            page: $state,
                                        ));
                                    }),
                                Placeholder::make('pagination_info')
                                    ->label('Info Halaman')
                                    ->content(fn ($get): HtmlString => new HtmlString($this->getBulkProdiPageInfo(
                                        search: $get('filter_search'),
                                        studyProgramFilter: $get('filter_study_program_id'),
                                        limit: $get('filter_limit'),
                                        page: $get('filter_page'),
                                    ))),
                            ]),
                    ]),
            ])
            ->modalSubmitActionLabel('Simpan Setting Prodi')
            ->action(function (array $data): void {
                $this->saveBulkProdiSettings($data);
            });
    }

    public function notifyFromBrowser(string $status, string $title, ?string $body = null): void
    {
        $notification = Notification::make()->title($title);

        if (filled($body)) {
            $notification->body($body);
        }

        match ($status) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            'danger', 'error' => $notification->danger(),
            default => $notification->info(),
        };

        $notification->send();
    }

    public function form(Schema $schema): Schema
    {
        $totalLecturers = SintaLecturer::query()->count();
        $statusSintaLecturersHtml = "<div style='padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #059669; font-weight: 500;'>✅ Total SINTA lecturer records in database: <b>{$totalLecturers}</b></div>";
        $fetchAllTimerStatusHtml = $this->getFetchAllTimerStatusHtml();
        $programStudis = $this->getStudyProgramOptions();

        $routes = [
            'syncLecturers' => route('scrap.perbaruiDosen'),
            'fetchSelected' => route('scrap.ambilDetail', ':id'),
            'importSelected' => route('scrap.importData', ':id'),
            'syncPrograms' => route('scrap.syncStudyPrograms'),
            'fetchAll' => route('scrap.sintaFetchBatches.fetchAll'),
            'status' => route('scrap.sintaFetchBatches.status'),
            'studyProgramSettings' => route('scrap.sintaFetchBatches.studyProgramSettings'),
            'resume' => route('scrap.sintaFetchBatches.resume'),
            'retryFailed' => route('scrap.sintaFetchBatches.retryFailed'),
            'reset' => route('scrap.sintaFetchBatches.reset'),
            'importAll' => route('scrap.sintaFetchBatches.importAll'),
        ];

        $buttonBaseStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer; margin-top: 0.375rem;';
        $buttonSecondaryStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-weight: 600; color: #111827; border: 1px solid #d1d5db; cursor: pointer; background-color: #ffffff; margin-top: 0.375rem;';

        $buttons = [
            'syncSinta' => '<button type="button" id="btn-perbarui" style="' . $buttonBaseStyle . ' background-color: #525252;">Sync SINTA Lecturers</button>',
            'fetchSelected' => '<button type="button" id="btn-ambil-detail" style="' . $buttonBaseStyle . ' background-color: #2563eb;">Fetch Selected Lecturer</button>',
            'fetchAll' => '<button type="button" id="btn-fetch-all-details" style="' . $buttonBaseStyle . ' background-color: #0f766e;">Fetch All / Lanjutkan Otomatis</button>',
            'timer' => '<button type="button" wire:click="mountAction(\'setTimerFetchAll\')" style="' . $buttonBaseStyle . ' background-color: #0369a1;">Set Timer Fetch All</button>',
            'syncPrograms' => '<button type="button" id="btn-sync-program-studi" style="' . $buttonBaseStyle . ' background-color: #7c3aed;">Sync Study Programs</button>',
            'settings' => '<button type="button" wire:click="mountAction(\'settingProdiFetchAll\')" style="' . $buttonBaseStyle . ' background-color: #ea580c;">Setting Prodi Fetch All</button>',
            'importSelected' => '<button type="button" id="btn-import" style="' . $buttonBaseStyle . ' background-color: #16a34a;">Import Selected</button>',
            'importAll' => '<button type="button" id="btn-import-all" style="' . $buttonBaseStyle . ' background-color: #15803d;">Import All to Database</button>',
        ];

        $routesJson = json_encode($routes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $terminalHtml = <<<'HTML'
        <div wire:ignore x-data x-init="new Function('livewire', $refs.runner.value)($wire)">
            <textarea x-ref="runner" hidden>
                if (window.__bulkSintaLecturerImportCleanup) {
                    window.__bulkSintaLecturerImportCleanup();
                }

                const routes = __ROUTES_JSON__;
                const NL = String.fromCharCode(10);
                const outputBox = document.getElementById('output-box');
                const terminalContainer = document.getElementById('terminal-container');
                const fatalKeywords = ['traceback', 'gagal membuka halaman', 'httperror', 'status: 403', 'status: 404', 'status: 500', 'fatal scraper pattern detected', 'import all is blocked', 'excel file was not found', 'merged detail excel was not downloaded'];
                const warningKeywords = ['tidak ada publikasi', 'kosong/tidak ditemukan', 'membuat sheet berisi', 'sheet contains', 'empty sheet', 'grafik garuda tidak ditemukan', 'gagal menemukan xaxis', 'gagal menemukan series', 'success_with_warning', 'empty-data warning'];

                const appendTerminal = (text) => {
                    if (!outputBox || !terminalContainer) return;
                    outputBox.innerHTML += text;
                    terminalContainer.scrollTop = terminalContainer.scrollHeight;
                };

                const resetTerminal = (text) => {
                    if (!outputBox) return;
                    outputBox.innerHTML = text;
                    if (terminalContainer) terminalContainer.scrollTop = terminalContainer.scrollHeight;
                };

                const notify = (status, title, body = '') => {
                    if (livewire && typeof livewire.call === 'function') {
                        livewire.call('notifyFromBrowser', status, title, body);
                    }
                };

                const stripHtml = (value) => String(value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

                const getState = (path) => {
                    try {
                        if (livewire && typeof livewire.get === 'function') {
                            return livewire.get(path);
                        }
                    } catch (error) {
                        return null;
                    }

                    return null;
                };

                const normalizeStateValue = (value) => {
                    if (Array.isArray(value)) {
                        return value.filter((item) => item !== null && item !== undefined && String(item).trim() !== '');
                    }

                    if (value === null || value === undefined) {
                        return '';
                    }

                    return String(value).trim();
                };

                const getSelectedSintaId = () => normalizeStateValue(getState('data.sinta_id'));
                const getSelectedStudyPrograms = () => normalizeStateValue(getState('data.program_studi'));

                const toggleLoading = (button, isLoading, originalText) => {
                    if (!button) return;
                    button.disabled = isLoading;
                    button.innerText = isLoading ? '⏳ Processing...' : originalText;
                    button.style.opacity = isLoading ? '0.5' : '1';
                };

                const appendPlainTerminal = (text) => {
                    if (!outputBox || !terminalContainer) return;
                    outputBox.appendChild(document.createTextNode(text));
                    terminalContainer.scrollTop = terminalContainer.scrollHeight;
                };

                const fetchAllPollState = {
                    intervalId: null,
                    lastBatchId: null,
                    lastRunningKey: null,
                    waitForNewBatchAfterId: null,
                    waitingForNewBatchNoticePrinted: false,
                    emittedDoneKeys: new Set(),
                };

                const normalizeText = (value) => String(value || '').trim();

                const stopFetchAllPolling = () => {
                    if (fetchAllPollState.intervalId) {
                        window.clearInterval(fetchAllPollState.intervalId);
                        fetchAllPollState.intervalId = null;
                    }

                    fetchAllPollState.waitForNewBatchAfterId = null;
                    fetchAllPollState.waitingForNewBatchNoticePrinted = false;
                };

                const readFetchAllStatus = async () => {
                    const response = await fetch(routes.status, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    return response.json();
                };

                const isFetchAllActive = (payload) => {
                    const batch = payload.batch || {};
                    const fetchCounts = payload.fetch_counts || {};
                    const status = normalizeText(batch.status);
                    const pending = Number(fetchCounts.pending || 0);
                    const processing = Number(fetchCounts.processing || 0);

                    if (payload.is_fetch_active || batch.is_fetch_active) {
                        return true;
                    }

                    if (['completed', 'paused', 'failed', 'cancelled'].includes(status)) {
                        return false;
                    }

                    return pending > 0 || processing > 0 || ['queued', 'running'].includes(status);
                };

                const fetchAllProcessingAgeMinutes = (payload) => {
                    const item = payload?.current_fetch_item || payload?.latest_fetch_item || null;
                    const startedAt = item?.started_at || payload?.batch?.started_at || null;

                    if (!startedAt) {
                        return 0;
                    }

                    const startedTime = Date.parse(startedAt);

                    if (Number.isNaN(startedTime)) {
                        return 0;
                    }

                    return Math.floor((Date.now() - startedTime) / 60000);
                };

                const isFetchAllStale = (payload) => {
                    if (!payload || !isFetchAllActive(payload)) {
                        return false;
                    }

                    const item = payload.current_fetch_item || payload.latest_fetch_item || null;
                    const status = normalizeText(payload.batch?.status);
                    const itemStatus = normalizeText(item?.status);
                    const ageMinutes = fetchAllProcessingAgeMinutes(payload);

                    return status === 'running'
                        && itemStatus === 'processing'
                        && ageMinutes >= 10;
                };

                const resetFetchAllBatchMonitor = (batchId, shouldPrint = true) => {
                    if (fetchAllPollState.lastBatchId === batchId) {
                        return;
                    }

                    fetchAllPollState.lastBatchId = batchId;
                    fetchAllPollState.lastRunningKey = null;
                    fetchAllPollState.emittedDoneKeys.clear();

                    if (shouldPrint) {
                        appendPlainTerminal('[QUEUE] Monitoring Fetch All batch #' + batchId + NL);
                    }
                };

                const appendFetchAllRunLine = (item) => {
                    if (!item || item.status !== 'processing') {
                        return;
                    }

                    const key = `${item.id}:${item.sinta_id}:${item.started_at || ''}`;

                    if (key === fetchAllPollState.lastRunningKey) {
                        return;
                    }

                    fetchAllPollState.lastRunningKey = key;
                    appendPlainTerminal('[RUN] SINTA ID ' + item.sinta_id + ' - ' + (normalizeText(item.lecturer_name) || '-') + ' run' + NL);
                };

                const appendFetchAllDoneLine = (item) => {
                    if (!item || !['success', 'success_with_warning', 'failed'].includes(item.status)) {
                        return;
                    }

                    const key = `${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`;

                    if (fetchAllPollState.emittedDoneKeys.has(key)) {
                        return;
                    }

                    fetchAllPollState.emittedDoneKeys.add(key);

                    const name = normalizeText(item.lecturer_name) || '-';

                    if (item.status === 'failed') {
                        appendPlainTerminal('[FAILED] SINTA ID ' + item.sinta_id + ' - ' + name + '. ' + normalizeText(item.error_message) + NL);
                        return;
                    }

                    const outputFile = item.output_file || ('merged_data_' + item.sinta_id + '.xlsx');
                    const label = item.status === 'success_with_warning' ? 'DONE WITH WARNING' : 'DONE';
                    appendPlainTerminal('[' + label + '] SINTA ID ' + item.sinta_id + ' - ' + name + '. File made: ' + outputFile + NL);
                };

                const appendFetchAllRecentDoneLines = (payload) => {
                    const recentItems = Array.isArray(payload.recent_fetch_items) ? payload.recent_fetch_items : [];

                    if (recentItems.length > 0) {
                        recentItems.forEach(appendFetchAllDoneLine);
                        return;
                    }

                    appendFetchAllDoneLine(payload.latest_fetch_item);
                };

                const primeFetchAllRecentDoneAsSeen = (payload) => {
                    const recentItems = Array.isArray(payload.recent_fetch_items) ? payload.recent_fetch_items : [];

                    recentItems.forEach((item) => {
                        if (!item || !['success', 'success_with_warning', 'failed'].includes(item.status)) {
                            return;
                        }

                        fetchAllPollState.emittedDoneKeys.add(`${item.id}:${item.sinta_id}:${item.status}:${item.finished_at || ''}`);
                    });
                };

                const handleFetchAllStatusPayload = (payload, options = {}) => {
                    if (!payload.batch) {
                        return false;
                    }

                    resetFetchAllBatchMonitor(payload.batch.id, options.printBatch !== false);

                    if (options.primeOnly) {
                        primeFetchAllRecentDoneAsSeen(payload);
                    } else {
                        appendFetchAllRecentDoneLines(payload);
                    }

                    appendFetchAllRunLine(payload.current_fetch_item);

                    if (isFetchAllActive(payload)) {
                        return true;
                    }

                    const status = normalizeText(payload.batch.status);

                    if (status === 'completed') {
                        appendPlainTerminal('[DONE] Fetch All queue selesai. Semua data selesai diproses.' + NL);
                        notify('success', 'Fetch All selesai', 'Semua data selesai diproses.');
                    } else if (status === 'paused') {
                        appendPlainTerminal('[PAUSED] Fetch All berhenti karena ada item gagal. Gunakan Resume atau Retry Failed.' + NL);
                        notify('warning', 'Fetch All paused', 'Ada item gagal. Gunakan Resume atau Retry Failed.');
                    } else if (status === 'cancelled') {
                        appendPlainTerminal('[CANCELLED] Fetch All batch dibatalkan.' + NL);
                        notify('warning', 'Fetch All dibatalkan', 'Batch Fetch All dibatalkan.');
                    }

                    return false;
                };

                const pollFetchAllStatus = async (button = null) => {
                    try {
                        const payload = await readFetchAllStatus();

                        if (fetchAllPollState.waitForNewBatchAfterId !== null) {
                            const currentBatchId = Number(payload?.batch?.id || 0);
                            const expectedAfterId = Number(fetchAllPollState.waitForNewBatchAfterId || 0);
                            const stillWaitingForWorkerBatch = !payload?.batch || currentBatchId <= expectedAfterId;

                            if (stillWaitingForWorkerBatch) {
                                if (!fetchAllPollState.waitingForNewBatchNoticePrinted) {
                                    appendPlainTerminal('[WAIT] Menunggu queue worker mengambil job dan membuat batch Fetch All baru...' + NL);
                                    fetchAllPollState.waitingForNewBatchNoticePrinted = true;
                                }

                                if (button) toggleLoading(button, true, 'Fetch All / Lanjutkan Otomatis');
                                return;
                            }

                            fetchAllPollState.waitForNewBatchAfterId = null;
                            fetchAllPollState.waitingForNewBatchNoticePrinted = false;
                        }

                        const keepPolling = handleFetchAllStatusPayload(payload);

                        if (!keepPolling) {
                            stopFetchAllPolling();
                            if (button) toggleLoading(button, false, 'Fetch All / Lanjutkan Otomatis');
                        }
                    } catch (error) {
                        appendPlainTerminal('[POLLING ERROR] ' + error.message + NL);
                        stopFetchAllPolling();
                        if (button) toggleLoading(button, false, 'Fetch All / Lanjutkan Otomatis');
                    }
                };

                const startFetchAllPolling = (button = null, label = '[QUEUE] Fetch All berjalan di background. Terminal hanya menampilkan RUN dan DONE.' + NL, options = {}) => {
                    stopFetchAllPolling();
                    fetchAllPollState.waitForNewBatchAfterId = options.waitForNewBatchAfterId ?? null;
                    fetchAllPollState.waitingForNewBatchNoticePrinted = false;
                    appendPlainTerminal(label);
                    pollFetchAllStatus(button);
                    fetchAllPollState.intervalId = window.setInterval(() => pollFetchAllStatus(button), 3000);
                };

                const dispatchFetchControlStream = (url, acceptedKeywords = ['[QUEUED]', '[QUEUE]', '[RESET]', '[WARN]', '[ERROR]']) => {
                    return new Promise((resolve, reject) => {
                        const eventSource = new EventSource(url);
                        let completed = false;
                        let compactMessage = null;
                        let errorMessage = null;

                        eventSource.onmessage = (event) => {
                            try {
                                const data = JSON.parse(event.data);

                                if (data.output) {
                                    const plain = stripHtml(data.output);

                                    if (acceptedKeywords.some((keyword) => plain.includes(keyword))) {
                                        compactMessage = plain;
                                    }

                                    if (plain.includes('[ERROR]')) {
                                        errorMessage = plain;
                                    }
                                }

                                if (data.done) {
                                    completed = true;
                                    eventSource.close();

                                    if (errorMessage) {
                                        reject(new Error(errorMessage));
                                        return;
                                    }

                                    resolve(compactMessage || '[OK] Perintah berhasil dikirim.');
                                }
                            } catch (error) {
                                completed = true;
                                eventSource.close();
                                reject(error);
                            }
                        };

                        eventSource.onerror = () => {
                            eventSource.close();

                            if (!completed) {
                                reject(new Error('Stream connection was interrupted.'));
                            }
                        };
                    });
                };

                const fetchCountsFromPayload = (payload) => {
                    const batch = payload.batch || {};
                    const fetchCounts = payload.fetch_counts || {};

                    return {
                        pending: Number(fetchCounts.pending || batch.pending_items || 0),
                        processing: Number(fetchCounts.processing || batch.processing_items || 0),
                        failed: Number(fetchCounts.failed || batch.failed_items || 0),
                    };
                };

                const dispatchQueuedFetchAll = () => dispatchFetchControlStream(routes.fetchAll, ['[QUEUED]', '[QUEUE]', '[WARN]', '[ERROR]']);
                const resetFetchAllBatch = () => dispatchFetchControlStream(routes.reset, ['[RESET]', '[INFO]', '[WARN]', '[ERROR]']);

                const runQueuedFetchAll = (event) => {
                    const button = event.target.closest('#btn-fetch-all-details');

                    if (!button) {
                        return false;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    resetTerminal('>>> Mengecek status Fetch All...' + NL + NL);
                    toggleLoading(button, true, 'Fetch All / Lanjutkan Otomatis');

                    (async () => {
                        const payload = await readFetchAllStatus().catch(() => null);

                        if (payload && isFetchAllActive(payload)) {
                            if (isFetchAllStale(payload)) {
                                appendPlainTerminal('[AUTO] Batch aktif terlihat macet lebih dari 10 menit. Reset otomatis lalu jalankan Fetch All ulang.' + NL);
                                await resetFetchAllBatch();
                            } else {
                                appendPlainTerminal('[RESUME] Fetch All masih berjalan di queue. Monitoring RUN/DONE dilanjutkan.' + NL);
                                handleFetchAllStatusPayload(payload, {
                                    primeOnly: true,
                                    printBatch: true,
                                });
                                startFetchAllPolling(button, '');
                                return;
                            }
                        }

                        const batchStatus = normalizeText(payload?.batch?.status);
                        const counts = payload ? fetchCountsFromPayload(payload) : { pending: 0, processing: 0, failed: 0 };
                        const shouldResetBeforeFetch = ['paused', 'failed', 'cancelled'].includes(batchStatus) || counts.failed > 0;

                        if (shouldResetBeforeFetch) {
                            appendPlainTerminal('[AUTO] Batch lama terdeteksi (' + (batchStatus || 'unknown') + '). Reset otomatis lalu lanjut Fetch All.' + NL);
                            await resetFetchAllBatch();
                        } else if (batchStatus === 'completed') {
                            appendPlainTerminal('[AUTO] Batch sebelumnya sudah completed. Membuat Fetch All baru untuk mengecek data yang belum punya file.' + NL);
                        } else if (!payload || !payload.batch) {
                            appendPlainTerminal('[AUTO] Belum ada batch. Membuat Fetch All baru.' + NL);
                        }

                        const previousBatchId = Number(payload?.batch?.id || 0);
                        const message = await dispatchQueuedFetchAll();
                        appendPlainTerminal(message + NL);
                        startFetchAllPolling(button, '[QUEUE] Fetch All berjalan di background. Terminal hanya menampilkan RUN dan DONE.' + NL, {
                            waitForNewBatchAfterId: previousBatchId,
                        });
                    })().catch((error) => {
                        appendPlainTerminal('[ERROR] ' + error.message + NL);
                        toggleLoading(button, false, 'Fetch All / Lanjutkan Otomatis');
                        notify('danger', 'Fetch All gagal dijalankan', error.message);
                    });

                    return true;
                };

                const importAllPollState = {
                    intervalId: null,
                    emittedRunKeys: new Set(),
                    emittedDoneKeys: new Set(),
                    knownStatuses: new Map(),
                    stablePolls: 0,
                    maxStablePolls: 60,
                    hasAnyProgress: false,
                };

                const stopImportAllPolling = () => {
                    if (importAllPollState.intervalId) {
                        window.clearInterval(importAllPollState.intervalId);
                        importAllPollState.intervalId = null;
                    }

                    importAllPollState.stablePolls = 0;
                };

                const readImportAllItems = async () => {
                    const response = await fetch(routes.studyProgramSettings, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    return response.json();
                };

                const importStatusFromItem = (item) => normalizeText(item?.import_status || item?.importStatus || '');
                const importItemKey = (item) => String(item?.sinta_id || item?.id || '');
                const isImportRunningStatus = (status) => ['processing', 'importing', 'running'].includes(normalizeText(status));
                const isImportDoneStatus = (status) => ['imported', 'success'].includes(normalizeText(status));
                const isImportFailedStatus = (status) => ['import_failed', 'failed', 'error'].includes(normalizeText(status));
                const isImportTerminalStatus = (status) => isImportDoneStatus(status) || isImportFailedStatus(status);

                const isImportableItem = (item) => {
                    const fetchStatus = normalizeText(item?.fetch_status || item?.status);
                    const settingStatus = normalizeText(item?.setting_status);
                    const importStatus = importStatusFromItem(item);

                    if (isImportTerminalStatus(importStatus) || isImportRunningStatus(importStatus)) {
                        return true;
                    }

                    return ['success', 'success_with_warning'].includes(fetchStatus)
                        && ['complete', 'auto_suggested', 'ready', ''].includes(settingStatus);
                };

                const appendImportRunLine = (item) => {
                    if (!item) return;

                    const key = importItemKey(item);
                    if (!key || importAllPollState.emittedRunKeys.has(key)) {
                        return;
                    }

                    importAllPollState.emittedRunKeys.add(key);
                    appendPlainTerminal('[RUN] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + (normalizeText(item.lecturer_name) || '-') + ' run' + NL);
                };

                const appendImportDoneLine = (item) => {
                    if (!item) return;

                    const status = importStatusFromItem(item);
                    const key = importItemKey(item) + ':' + status;

                    if (!key || importAllPollState.emittedDoneKeys.has(key)) {
                        return;
                    }

                    importAllPollState.emittedDoneKeys.add(key);
                    importAllPollState.hasAnyProgress = true;
                    appendImportRunLine(item);

                    const name = normalizeText(item.lecturer_name) || '-';

                    if (isImportFailedStatus(status)) {
                        appendPlainTerminal('[FAILED] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + '. ' + normalizeText(item.import_error || item.error_message) + NL);
                        return;
                    }

                    appendPlainTerminal('[DONE] Import SINTA ID ' + normalizeText(item.sinta_id) + ' - ' + name + ' imported to database' + NL);
                };

                const primeImportAllStatuses = async () => {
                    importAllPollState.emittedRunKeys.clear();
                    importAllPollState.emittedDoneKeys.clear();
                    importAllPollState.knownStatuses.clear();
                    importAllPollState.stablePolls = 0;
                    importAllPollState.hasAnyProgress = false;

                    const payload = await readImportAllItems().catch(() => null);
                    const items = Array.isArray(payload?.items) ? payload.items : [];

                    items.forEach((item) => {
                        const key = importItemKey(item);
                        const status = importStatusFromItem(item);

                        if (!key) {
                            return;
                        }

                        importAllPollState.knownStatuses.set(key, status);

                        if (isImportTerminalStatus(status)) {
                            importAllPollState.emittedDoneKeys.add(key + ':' + status);
                        }
                    });

                    return payload;
                };

                const isImportAllActiveFromStatus = (payload) => {
                    const batch = payload?.batch || {};
                    const importCounts = payload?.import_counts || {};
                    const currentImportItem = payload?.current_import_item || payload?.latest_import_processing_item;

                    if (payload?.is_import_active || batch?.is_import_active || currentImportItem) {
                        return true;
                    }

                    return Number(importCounts.processing || importCounts.importing || importCounts.running || 0) > 0;
                };

                const pollImportAllStatus = async (button = null) => {
                    try {
                        const [settingsPayload, statusPayload] = await Promise.all([
                            readImportAllItems(),
                            readFetchAllStatus().catch(() => null),
                        ]);

                        const items = Array.isArray(settingsPayload?.items) ? settingsPayload.items : [];
                        const importableItems = items.filter(isImportableItem);
                        let changed = false;

                        importableItems.forEach((item) => {
                            const key = importItemKey(item);
                            const status = importStatusFromItem(item);

                            if (!key) {
                                return;
                            }

                            const previousStatus = importAllPollState.knownStatuses.get(key);

                            if (previousStatus !== status) {
                                changed = true;
                                importAllPollState.knownStatuses.set(key, status);
                            }

                            if (isImportRunningStatus(status)) {
                                appendImportRunLine(item);
                            }

                            if (isImportTerminalStatus(status) && previousStatus !== status) {
                                appendImportDoneLine(item);
                            }
                        });

                        if (changed) {
                            importAllPollState.stablePolls = 0;
                        } else {
                            importAllPollState.stablePolls += 1;
                        }

                        const isActive = isImportAllActiveFromStatus(statusPayload);
                        const allImportableTerminal = importableItems.length > 0
                            && importableItems.every((item) => isImportTerminalStatus(importStatusFromItem(item)));

                        if (isActive || (!allImportableTerminal && importAllPollState.stablePolls < importAllPollState.maxStablePolls)) {
                            if (button) toggleLoading(button, true, 'Import All to Database');
                            return;
                        }

                        stopImportAllPolling();
                        if (button) toggleLoading(button, false, 'Import All to Database');

                        if (importAllPollState.hasAnyProgress || allImportableTerminal) {
                            appendPlainTerminal('[DONE] Import All queue selesai. Data siap/import sudah diproses.' + NL);
                            notify('success', 'Import All selesai', 'Data dosen yang siap sudah diproses ke database.');
                        } else {
                            appendPlainTerminal('[DONE] Import All selesai. Tidak ada perubahan import baru yang terdeteksi.' + NL);
                            notify('warning', 'Import All selesai', 'Tidak ada perubahan import baru yang terdeteksi. Cek Setting Prodi Fetch All jika ada data yang belum siap.');
                        }
                    } catch (error) {
                        appendPlainTerminal('[POLLING ERROR] Import All: ' + error.message + NL);
                        stopImportAllPolling();
                        if (button) toggleLoading(button, false, 'Import All to Database');
                    }
                };

                const startImportAllPolling = (button = null, label = '[QUEUE] Import All berjalan di background. Terminal hanya menampilkan RUN dan DONE.' + NL) => {
                    stopImportAllPolling();
                    importAllPollState.stablePolls = 0;
                    appendPlainTerminal(label);
                    pollImportAllStatus(button);
                    importAllPollState.intervalId = window.setInterval(() => pollImportAllStatus(button), 3000);
                };

                const runQueuedImportAll = (event) => {
                    const button = event.target.closest('#btn-import-all');

                    if (!button) {
                        return false;
                    }

                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    resetTerminal('>>> Starting Import All in background queue...' + NL + NL);
                    toggleLoading(button, true, 'Import All to Database');

                    (async () => {
                        await primeImportAllStatuses();
                        const message = await dispatchFetchControlStream(routes.importAll, ['[QUEUED]', '[QUEUE]', '[WARN]', '[ERROR]', '[BLOCKED]']);
                        appendPlainTerminal(message + NL);
                        startImportAllPolling(button);
                    })().catch((error) => {
                        appendPlainTerminal('[ERROR] ' + error.message + NL);
                        toggleLoading(button, false, 'Import All to Database');
                        notify('danger', 'Import All gagal dijalankan', error.message);
                    });

                    return true;
                };

                const resumeQueuedFetchAllIfActive = async () => {
                    try {
                        const payload = await readFetchAllStatus();

                        if (!isFetchAllActive(payload)) {
                            return;
                        }

                        if (isFetchAllStale(payload)) {
                            appendPlainTerminal('[STALE] Batch Fetch All masih berstatus running, tetapi item processing sudah lebih dari 10 menit. Klik Fetch All / Lanjutkan Otomatis untuk reset dan menjalankan ulang.' + NL);
                            return;
                        }

                        handleFetchAllStatusPayload(payload, {
                            primeOnly: true,
                            printBatch: true,
                        });

                        appendPlainTerminal('[RESUME] Queue Fetch All masih berjalan. Menampilkan RUN/DONE baru mulai sekarang.' + NL);
                        const button = document.getElementById('btn-fetch-all-details');
                        if (button) toggleLoading(button, true, 'Fetch All / Lanjutkan Otomatis');
                        startFetchAllPolling(button, '');
                    } catch (error) {
                        // Ignore status polling errors on initial page load.
                    }
                };

                const notifyFromOutput = (streamOutput, successTitle, successBody, errorTitle) => {
                    const plainText = stripHtml(streamOutput);
                    const normalized = plainText.toLowerCase();
                    if (fatalKeywords.some((keyword) => normalized.includes(keyword))) {
                        notify('danger', errorTitle, plainText.slice(0, 240) || 'The process failed.');
                        return;
                    }
                    if (warningKeywords.some((keyword) => normalized.includes(keyword))) {
                        notify('warning', successTitle + ' with warnings', 'The process finished, but some modules were empty. This is allowed.');
                        return;
                    }
                    notify('success', successTitle, successBody);
                };

                const openStream = (url, onDone, errorText, successTitle, successBody, errorTitle, onError = null) => {
                    let streamOutput = '';
                    appendTerminal('[SSE] Opening connection: ' + url + NL);
                    const eventSource = new EventSource(url);
                    eventSource.onmessage = (event) => {
                        try {
                            const data = JSON.parse(event.data);
                            if (data.output) {
                                streamOutput += data.output + NL;
                                appendTerminal(data.output);
                            }
                            if (data.done) {
                                eventSource.close();
                                notifyFromOutput(streamOutput, successTitle, successBody, errorTitle);
                                if (onDone) onDone();
                            }
                        } catch (error) {
                            appendTerminal(NL + '[ERROR] Failed to parse stream response: ' + error.message + NL);
                            notify('danger', 'Failed to parse stream response', error.message);
                        }
                    };
                    eventSource.onerror = () => {
                        eventSource.close();
                        appendTerminal(errorText + NL);
                        notify('danger', errorTitle, stripHtml(errorText));
                        if (onError) onError();
                    };
                };

                const runButtonStream = (event, selector, url, terminalText, originalText, successTitle, successBody, errorTitle, reloadAfter = false) => {
                    const button = event.target.closest(selector);
                    if (!button) return false;
                    event.preventDefault();
                    resetTerminal(terminalText + NL + NL);
                    toggleLoading(button, true, originalText);
                    openStream(url, () => {
                        toggleLoading(button, false, originalText);
                        if (reloadAfter) setTimeout(() => window.location.reload(), 1500);
                    }, NL + '[ERROR] Stream connection was interrupted. Check Laravel logs.', successTitle, successBody, errorTitle, () => toggleLoading(button, false, originalText));
                    return true;
                };

                const clickHandler = (event) => {
                    if (event.target.closest('#btn-clear-terminal')) { resetTerminal('Waiting for command...' + NL); return; }
                    if (runButtonStream(event, '#btn-perbarui', routes.syncLecturers, '>>> Starting SINTA lecturer master sync...', 'Sync SINTA Lecturers', 'SINTA lecturers synced', 'SINTA lecturer data has been updated successfully.', 'SINTA lecturer sync failed', true)) return;
                    if (runQueuedFetchAll(event)) return;
                    if (runQueuedImportAll(event)) return;
                    if (runButtonStream(event, '#btn-sync-program-studi', routes.syncPrograms, '>>> Starting study program sync from UNW API...', 'Sync Study Programs', 'Study programs synced', 'All study programs have been synced successfully.', 'Study program sync failed', true)) return;

                    const btnAmbilDetail = event.target.closest('#btn-ambil-detail');
                    if (btnAmbilDetail) {
                        event.preventDefault();
                        const sintaId = getSelectedSintaId();
                        if (!sintaId) { notify('warning', 'Lecturer not selected', 'Please select a lecturer first.'); return; }
                        resetTerminal('>>> Fetching SINTA detail modules for ID: ' + sintaId + '...' + NL + NL);
                        toggleLoading(btnAmbilDetail, true, 'Fetch Selected Lecturer');
                        openStream(routes.fetchSelected.replace(':id', sintaId), () => toggleLoading(btnAmbilDetail, false, 'Fetch Selected Lecturer'), NL + '[ERROR] Detail extraction was interrupted.', 'SINTA detail fetched', 'The lecturer detail Excel file has been generated successfully.', 'Failed to fetch SINTA detail', () => toggleLoading(btnAmbilDetail, false, 'Fetch Selected Lecturer'));
                        return;
                    }

                    const btnImport = event.target.closest('#btn-import');
                    if (btnImport) {
                        event.preventDefault();
                        const sintaId = getSelectedSintaId();
                        const programStudi = getSelectedStudyPrograms();
                        if (!sintaId) { notify('warning', 'SINTA ID was not found', 'Please select a lecturer in Step 2.'); return; }
                        if (!programStudi || (Array.isArray(programStudi) && programStudi.length === 0)) { notify('warning', 'Study program is required', 'Please select at least one Study Program.'); return; }
                        const programStudiString = Array.isArray(programStudi) ? programStudi.join(',') : programStudi;
                        resetTerminal('>>> Importing lecturer into lecturers for SINTA ID: ' + sintaId + '...' + NL);
                        toggleLoading(btnImport, true, 'Import Selected');
                        openStream(routes.importSelected.replace(':id', sintaId) + '?jurusan=' + encodeURIComponent(programStudiString), () => toggleLoading(btnImport, false, 'Import Selected'), NL + '[ERROR] Database import stream was interrupted.', 'Lecturer imported', 'The lecturer has been imported into lecturers successfully.', 'Lecturer import failed', () => toggleLoading(btnImport, false, 'Import Selected'));
                    }
                };

                document.addEventListener('click', clickHandler);
                window.setTimeout(resumeQueuedFetchAllIfActive, 1000);
                window.__bulkSintaLecturerImportCleanup = () => {
                    stopFetchAllPolling();
                    stopImportAllPolling();
                    document.removeEventListener('click', clickHandler);
                };
                appendTerminal('\n[INIT] Lecturer import controls are ready.' + NL);
            </textarea>

            <div style="background-color:#0a0a0a;border-radius:0.75rem;border:1px solid #262626;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);display:flex;flex-direction:column;height:450px;overflow:hidden;margin-top:1.5rem;">
                <div style="background-color:#171717;padding:0.75rem 1rem;border-bottom:1px solid #262626;display:flex;justify-content:space-between;align-items:center;">
                    <span style="color:#a3a3a3;font-family:ui-monospace,monospace;font-size:0.75rem;letter-spacing:0.05em;">Real-time Lecturer Import Output</span>
                    <button type="button" id="btn-clear-terminal" style="color:#a3a3a3;font-family:ui-monospace,monospace;font-size:0.75rem;background:none;border:none;cursor:pointer;">Clear Log</button>
                </div>
                <div id="terminal-container" style="padding:1rem;overflow-y:auto;flex-grow:1;background-color:#0a0a0a;">
                    <pre id="output-box" style="color:#4ade80;margin:0;white-space:pre-wrap;word-break:break-all;font-family:ui-monospace,monospace;font-size:0.875rem;line-height:1.5;">Waiting for command...</pre>
                </div>
            </div>
        </div>
        HTML;

        $terminalHtml = str_replace('__ROUTES_JSON__', $routesJson, $terminalHtml);

        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Step 1: Sync SINTA Lecturers')
                            ->description('Fetch master lecturer data from SINTA and store it in the sinta_lecturers table.')
                            ->icon('heroicon-o-arrow-path')
                            ->schema([
                                Placeholder::make('status_sinta_lecturers')->label('SINTA Lecturer Data Status')->content(new HtmlString($statusSintaLecturersHtml)),
                                Placeholder::make('button_sync_sinta_lecturers')->hiddenLabel()->content(new HtmlString($buttons['syncSinta'])),
                            ])
                            ->columnSpan(1),

                        Section::make('Step 2: Fetch SINTA Lecturer Detail')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->description('Fetch one selected lecturer or run a batch fetch for every registered SINTA lecturer.')
                            ->schema([
                                Placeholder::make('status_fetch_all_timer')->label('Automatic Fetch All Timer')->content(new HtmlString($fetchAllTimerStatusHtml)),
                                Select::make('sinta_id')
                                    ->label('Select Lecturer from SINTA Lecturers')
                                    ->options($this->getSintaLecturerOptions())
                                    ->getSearchResultsUsing(fn (string $search): array => $this->getSintaLecturerOptions($search))
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $lecturer = SintaLecturer::where('sinta_id', $value)->first();

                                        return $lecturer ? trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')') : null;
                                    })
                                    ->searchable()
                                    ->placeholder('-- Select Lecturer from SINTA Master --')
                                    ->required(),
                                Placeholder::make('button_ambil_detail')->hiddenLabel()->content(new HtmlString($buttons['fetchSelected'])),
                                Placeholder::make('button_fetch_all_detail')->hiddenLabel()->content(new HtmlString($buttons['fetchAll'])),
                                Placeholder::make('button_set_timer_fetch_all')->hiddenLabel()->content(new HtmlString($buttons['timer'])),
                            ])
                            ->columnSpan(1),

                        Section::make('Step 3: Setting Prodi & Import')
                            ->icon('heroicon-o-server')
                            ->description('Set study program mappings for batch results, then import selected or all ready lecturers.')
                            ->schema([
                                Placeholder::make('button_sync_program_studi')->hiddenLabel()->content(new HtmlString($buttons['syncPrograms'])),
                                Placeholder::make('button_setting_prodi_fetch_all')->hiddenLabel()->content(new HtmlString($buttons['settings'])),
                                Select::make('program_studi')
                                    ->label('Select Study Programs')
                                    ->options($programStudis)
                                    ->searchable()
                                    ->multiple()
                                    ->placeholder('-- Select Study Programs --')
                                    ->required()
                                    ->native(false),
                                Placeholder::make('button_import_database')->hiddenLabel()->content(new HtmlString($buttons['importSelected'])),
                                Placeholder::make('button_import_all_database')->hiddenLabel()->content(new HtmlString($buttons['importAll'])),
                            ])
                            ->columnSpan(1),
                    ]),

                Placeholder::make('terminal_sync')->hiddenLabel()->content(new HtmlString($terminalHtml)),
            ])
            ->statePath('data');
    }

    private function getFetchAllTimerStatusHtml(): string
    {
        if (! SchemaFacade::hasTable('sinta_lecturer_fetch_all_schedule_settings')) {
            return '<div style="padding:0.75rem;border-radius:0.5rem;background-color:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);color:#92400e;font-weight:500;">⚠️ Timer table is not ready. Run <b>php artisan migrate</b>.</div>';
        }

        $setting = SintaLecturerFetchAllScheduleSetting::current();
        $timezone = e((string) config('app.timezone'));
        $time = e((string) ($setting->formattedTime() ?: '-'));
        $lastRun = $setting->last_run_at ? e($setting->last_run_at->format('Y-m-d H:i')) : '-';
        $lastSkipReason = filled($setting->last_skip_reason) ? '<br><span style="font-size:0.8125rem;opacity:0.85;">Last skip: ' . e((string) $setting->last_skip_reason) . '</span>' : '';

        if ($setting->is_enabled && $setting->scheduled_time) {
            return "<div style='padding:0.75rem;border-radius:0.5rem;background-color:rgba(14,165,233,0.1);border:1px solid rgba(14,165,233,0.2);color:#0369a1;font-weight:500;'>⏰ Automatic Fetch All: <b>Enabled</b> at <b>{$time}</b> ({$timezone})<br><span style='font-size:0.8125rem;opacity:0.85;'>Last run: {$lastRun}</span>{$lastSkipReason}</div>";
        }

        return "<div style='padding:0.75rem;border-radius:0.5rem;background-color:rgba(107,114,128,0.1);border:1px solid rgba(107,114,128,0.2);color:#4b5563;font-weight:500;'>⏱ Automatic Fetch All: <b>Disabled</b></div>";
    }

    private function getBulkProdiSettingRows(?string $search = null, mixed $studyProgramFilter = null, mixed $limit = self::BULK_PRODI_MODAL_DEFAULT_LIMIT, mixed $page = 1): array
    {
        if (! $this->batchTablesReady()) {
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

        $this->queueNextBulkProdiPageCacheWarm(
            batch: $batch,
            normalizedSearch: $normalizedSearch,
            studyProgramFilter: $studyProgramFilter,
            limit: $resolvedLimit,
            currentPage: $resolvedPage,
        );

        $programModels = $this->getStudyProgramModels();

        return $items
            ->map(function ($item) use ($settings, $programModels): array {
                $sintaId = (string) $item->sinta_id;
                $existing = $settings->get($sintaId, collect())
                    ->pluck('study_program_id')
                    ->map(fn ($id) => (int) $id)
                    ->values();

                $detectedStudyProgram = null;
                $suggested = collect();

                if ($existing->isNotEmpty()) {
                    $selected = $existing;
                    $detectedStudyProgram = 'Sudah tersimpan di database';
                } else {
                    $detectedStudyProgram = $this->readStudyProgramFromMergedExcel($sintaId, warmSynchronously: true);
                    $suggested = $this->suggestStudyProgramIds($detectedStudyProgram, $programModels);
                    $selected = $suggested;
                }

                $canSet = in_array($item->status, ['success', 'success_with_warning'], true);

                return [
                    'sinta_id' => $sintaId,
                    'lecturer_name' => (string) ($item->lecturer_name ?: '-'),
                    'fetch_status' => (string) $item->status,
                    'detected_study_program' => (string) ($detectedStudyProgram ?? ''),
                    'study_program_ids' => $selected->map(fn ($id) => (int) $id)->values()->toArray(),
                    'setting_status' => $canSet
                        ? ($existing->isNotEmpty() ? 'complete' : ($suggested->isNotEmpty() ? 'auto_suggested' : 'not_set'))
                        : 'blocked',
                ];
            })
            ->values()
            ->toArray();
    }

    private function bulkProdiItemsQuery(SintaLecturerFetchBatch $batch, string $normalizedSearch = '', ?string $studyProgramFilter = null)
    {
        return $batch->items()
            ->select(['id', 'batch_id', 'sinta_id', 'lecturer_name', 'status', 'import_status', 'warning_message', 'error_message'])
            ->when($normalizedSearch !== '', function ($query) use ($normalizedSearch) {
                $query->where(function ($subQuery) use ($normalizedSearch) {
                    $subQuery->whereRaw('LOWER(lecturer_name) LIKE ?', ["%{$normalizedSearch}%"])
                        ->orWhere('sinta_id', 'like', "%{$normalizedSearch}%");
                });
            })
            ->when($studyProgramFilter === '__null__', function ($query) {
                $query->whereNotIn('sinta_id', SintaLecturerStudyProgramSetting::query()->select('sinta_id'));
            })
            ->when($studyProgramFilter && $studyProgramFilter !== '__null__', function ($query) use ($studyProgramFilter) {
                $query->whereIn('sinta_id', SintaLecturerStudyProgramSetting::query()
                    ->where('study_program_id', (int) $studyProgramFilter)
                    ->select('sinta_id'));
            });
    }

    private function getCachedBulkProdiPageItems(SintaLecturerFetchBatch $batch, string $normalizedSearch, ?string $studyProgramFilter, int $limit, int $page): Collection
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
            ->sortBy(fn ($item) => $position[(int) $item->id] ?? 999999)
            ->values();
    }

    private function queueNextBulkProdiPageCacheWarm(SintaLecturerFetchBatch $batch, string $normalizedSearch, ?string $studyProgramFilter, int $limit, int $currentPage): void
    {
        $nextPage = $currentPage + 1;
        $nextItems = $this->getCachedBulkProdiPageItems($batch, $normalizedSearch, $studyProgramFilter, $limit, $nextPage);

        if ($nextItems->isEmpty()) {
            return;
        }

        $settingsSintaIds = SintaLecturerStudyProgramSetting::query()
            ->whereIn('sinta_id', $nextItems->pluck('sinta_id')->filter()->values())
            ->pluck('sinta_id')
            ->map(fn ($value) => (string) $value)
            ->flip();

        $nextItems
            ->filter(fn ($item) => ! $settingsSintaIds->has((string) $item->sinta_id))
            ->each(fn ($item) => $this->queueStudyProgramExcelCacheWarm((string) $item->sinta_id));
    }

    private function getBulkProdiPageOptions(?string $search = null, mixed $studyProgramFilter = null, mixed $limit = self::BULK_PRODI_MODAL_DEFAULT_LIMIT): array
    {
        $total = $this->countBulkProdiSettingRows($search, $studyProgramFilter);
        $resolvedLimit = $this->resolveBulkProdiModalLimit($limit);
        $totalPages = max(1, (int) ceil($total / $resolvedLimit));

        return collect(range(1, $totalPages))
            ->mapWithKeys(fn (int $page): array => [$page => "Halaman {$page}"])
            ->toArray();
    }

    private function getBulkProdiPageInfo(?string $search = null, mixed $studyProgramFilter = null, mixed $limit = self::BULK_PRODI_MODAL_DEFAULT_LIMIT, mixed $page = 1): string
    {
        $total = $this->countBulkProdiSettingRows($search, $studyProgramFilter);
        $resolvedLimit = $this->resolveBulkProdiModalLimit($limit);
        $resolvedPage = $this->resolveBulkProdiModalPage($page);
        $totalPages = max(1, (int) ceil($total / $resolvedLimit));
        $from = $total > 0 ? (($resolvedPage - 1) * $resolvedLimit) + 1 : 0;
        $to = min($resolvedPage * $resolvedLimit, $total);

        return "<div style='padding:0.75rem;border-radius:0.5rem;background-color:rgba(234,88,12,0.08);border:1px solid rgba(234,88,12,0.18);color:#9a3412;font-weight:500;'>Menampilkan {$from}-{$to} dari {$total} data. Halaman {$resolvedPage} dari {$totalPages}. Halaman berikutnya disiapkan otomatis di cache.</div>";
    }

    private function countBulkProdiSettingRows(?string $search = null, mixed $studyProgramFilter = null): int
    {
        if (! $this->batchTablesReady()) {
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

    private function resolveBulkProdiModalPage(mixed $page): int
    {
        $page = (int) $page;

        return max(1, $page);
    }

    private function bulkProdiPageCacheVersion(): int
    {
        return (int) Cache::get('sinta_import_bulk_prodi_page_cache_version', 1);
    }

    private function bumpBulkProdiPageCacheVersion(): void
    {
        Cache::forever('sinta_import_bulk_prodi_page_cache_version', $this->bulkProdiPageCacheVersion() + 1);
    }

    private function bulkProdiPageCacheKey(int $batchId, string $normalizedSearch, ?string $studyProgramFilter, int $limit, int $page): string
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

    private function saveBulkProdiSettings(array $data): void
    {
        $rows = collect(data_get($data, 'lecturers', []));
        $userId = auth()->user()?->getAuthIdentifier();

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

    private function getStudyProgramFilterOptions(): array
    {
        return ['__null__' => 'Belum disetting / Null'] + $this->getStudyProgramOptions();
    }

    private function getStudyProgramOptions(): array
    {
        return Cache::remember('sinta_import_study_program_options_v2', now()->addMinutes(10), function (): array {
            return StudyProgram::query()
                ->orderBy('jenjang')
                ->orderBy('nama')
                ->get()
                ->mapWithKeys(fn (StudyProgram $program) => [
                    $program->id => $program->display_name,
                ])
                ->toArray();
        });
    }

    private function getStudyProgramModels(): Collection
    {
        return StudyProgram::query()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get();
    }

    private function getSintaLecturerOptions(?string $search = null): array
    {
        return SintaLecturer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sinta_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (SintaLecturer $lecturer) => [
                $lecturer->sinta_id => trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')'),
            ])
            ->toArray();
    }

    private function resolveBulkProdiModalLimit(mixed $limit): int
    {
        $limit = (int) $limit;

        if ($limit <= 0) {
            return self::BULK_PRODI_MODAL_DEFAULT_LIMIT;
        }

        return min($limit, self::BULK_PRODI_MODAL_MAX_LIMIT);
    }

    private function batchTablesReady(): bool
    {
        return SchemaFacade::hasTable('sinta_lecturer_fetch_batches')
            && SchemaFacade::hasTable('sinta_lecturer_fetch_batch_items')
            && SchemaFacade::hasTable('sinta_lecturer_study_program_settings');
    }

    private function mergedDetailFilePath(string $sintaId): string
    {
        return base_path("scripts/output/merged_data_{$sintaId}.xlsx");
    }

    private function mergedDetailFileExists(string $sintaId): bool
    {
        return file_exists($this->mergedDetailFilePath($sintaId));
    }

    private function readStudyProgramFromMergedExcel(string $sintaId, bool $warmSynchronously = false): ?string
    {
        $filePath = $this->mergedDetailFilePath($sintaId);

        if (! file_exists($filePath)) {
            return null;
        }

        $fileTime = (string) filemtime($filePath);
        $cacheKey = $this->detectedStudyProgramCacheKey($sintaId, $fileTime);

        if (! Cache::has($cacheKey)) {
            if ($warmSynchronously) {
                $value = $this->readStudyProgramFromMergedExcelFile($filePath);
                Cache::put($cacheKey, (string) ($value ?? ''), now()->addDays(7));

                return filled($value) ? $value : null;
            }

            $this->queueStudyProgramExcelCacheWarm($sintaId);

            return null;
        }

        $value = Cache::get($cacheKey);
        $value = is_string($value) ? trim($value) : null;

        return $value !== '' ? $value : null;
    }

    private function queueExcelProdiCacheWarmForLatestBatch(int $limit = 100): void
    {
        if (! $this->batchTablesReady()) {
            return;
        }

        $batch = SintaLecturerFetchBatch::query()->latest('id')->first();

        if (! $batch) {
            return;
        }

        $batchLockKey = "sinta_import_excel_prodi_cache_batch_warm:{$batch->id}";

        if (! Cache::add($batchLockKey, true, now()->addMinutes(30))) {
            return;
        }

        $batch->items()
            ->whereIn('status', ['success', 'success_with_warning'])
            ->whereNotIn('sinta_id', SintaLecturerStudyProgramSetting::query()->select('sinta_id'))
            ->orderByDesc('finished_at')
            ->orderBy('id')
            ->limit($limit)
            ->pluck('sinta_id')
            ->filter()
            ->each(fn ($sintaId) => $this->queueStudyProgramExcelCacheWarm((string) $sintaId));
    }

    private function hasStoredStudyProgramSetting(string $sintaId): bool
    {
        return SintaLecturerStudyProgramSetting::query()
            ->where('sinta_id', $sintaId)
            ->exists();
    }

    private function queueStudyProgramExcelCacheWarm(string $sintaId): void
    {
        if ($this->hasStoredStudyProgramSetting($sintaId)) {
            return;
        }

        $filePath = $this->mergedDetailFilePath($sintaId);

        if (! file_exists($filePath)) {
            return;
        }

        $fileTime = (string) filemtime($filePath);
        $cacheKey = $this->detectedStudyProgramCacheKey($sintaId, $fileTime);

        if (Cache::has($cacheKey)) {
            return;
        }

        $lockKey = $this->detectedStudyProgramCacheWarmLockKey($sintaId, $fileTime);

        if (! Cache::add($lockKey, true, now()->addMinutes(15))) {
            return;
        }

        WarmSintaLecturerExcelProdiCacheJob::dispatch($sintaId, $filePath, $fileTime);
    }

    private function detectedStudyProgramCacheKey(string $sintaId, string $fileTime): string
    {
        return "sinta_import_detected_study_program:{$sintaId}:{$fileTime}";
    }

    private function detectedStudyProgramCacheWarmLockKey(string $sintaId, string $fileTime): string
    {
        return "sinta_import_detected_study_program_warming:{$sintaId}:{$fileTime}";
    }

    private function readStudyProgramFromMergedExcelFile(string $filePath): ?string
    {
        try {
            $sheets = (new FastExcel())->importSheets($filePath);
            $rows = null;

            foreach ($sheets as $sheetName => $sheetRows) {
                $normalizedSheetName = Str::of((string) $sheetName)->lower()->replace([' ', '-'], '_')->toString();

                if (str_contains($normalizedSheetName, 'data_dosen')) {
                    $rows = collect($sheetRows);
                    break;
                }
            }

            $rows ??= collect($sheets[0] ?? reset($sheets) ?: []);
            $firstRow = $rows->first();

            if (! $firstRow) {
                return null;
            }

            $row = array_change_key_case((array) $firstRow, CASE_LOWER);
            $value = $row['program studi']
                ?? $row['program_studi']
                ?? $row['study_program']
                ?? data_get(array_values((array) $firstRow), 2);
            $value = is_string($value) ? trim($value) : null;

            return $value !== '' ? $value : null;
        } catch (\Throwable $exception) {
            Log::warning("Failed to read study program from merged Excel {$filePath}: {$exception->getMessage()}");

            return null;
        }
    }

    private function suggestStudyProgramIds(?string $rawStudyProgram, Collection $programs): Collection
    {
        if (! $rawStudyProgram) {
            return collect();
        }

        $parsed = $this->parseExternalStudyProgram($rawStudyProgram);
        $externalLevel = $this->canonicalLevel($parsed['level']);
        $externalName = $this->normalizeStudyProgramText($parsed['name']);
        $externalTokens = $this->studyProgramTokens($externalName);

        $scored = $programs->map(function (StudyProgram $program) use ($externalLevel, $externalName, $externalTokens) {
            $programLevel = $this->canonicalLevel($program->jenjang_nama_singkat ?: $program->jenjang);
            $programName = $this->normalizeStudyProgramText((string) $program->nama);
            $programDisplay = $this->normalizeStudyProgramText((string) $program->display_name);
            $programTokens = $this->studyProgramTokens($programName . ' ' . $programDisplay);
            $score = 0;

            if ($externalLevel && $programLevel && $externalLevel === $programLevel) {
                $score += 100;
            } elseif ($externalLevel && $programLevel && $externalLevel !== $programLevel) {
                $score -= 40;
            }

            if ($externalName !== '' && ($externalName === $programName || $externalName === $programDisplay)) {
                $score += 90;
            }

            if ($externalName !== '' && $programName !== '' && (str_contains($externalName, $programName) || str_contains($programName, $externalName))) {
                $score += 70;
            }

            if ($externalName !== '' && $programDisplay !== '' && (str_contains($externalName, $programDisplay) || str_contains($programDisplay, $externalName))) {
                $score += 50;
            }

            $score += $externalTokens->intersect($programTokens)->count() * 25;

            return ['id' => (int) $program->id, 'score' => $score];
        })->filter(fn (array $item) => $item['score'] >= 80)->sortByDesc('score')->values();

        if ($scored->isEmpty()) {
            return collect();
        }

        $bestScore = (int) $scored->first()['score'];

        return $scored
            ->filter(fn (array $item) => (int) $item['score'] === $bestScore)
            ->pluck('id')
            ->take(2)
            ->values();
    }

    private function parseExternalStudyProgram(string $raw): array
    {
        $raw = trim($raw);
        $level = null;
        $name = $raw;

        if (preg_match('/^\s*(.*?)\s*[-–—]\s*(.+)$/u', $raw, $matches)) {
            $level = trim($matches[1]);
            $name = trim($matches[2]);
        }

        if (! $level && preg_match('/\b(S1|S2|S3|D3|D4|Profesi|Sarjana|Magister|Diploma\s*3|Diploma\s*4)\b/i', $raw, $matches)) {
            $level = $matches[1];
        }

        return ['level' => $level, 'name' => $name];
    }

    private function canonicalLevel(?string $value): ?string
    {
        $value = Str::of((string) $value)->lower()->ascii()->toString();
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = trim($value);

        return match (true) {
            str_contains($value, 's1') || str_contains($value, 'sarjana') => 's1',
            str_contains($value, 's2') || str_contains($value, 'magister') => 's2',
            str_contains($value, 's3') || str_contains($value, 'doktor') => 's3',
            str_contains($value, 'd3') || str_contains($value, 'diploma 3') => 'd3',
            str_contains($value, 'd4') || str_contains($value, 'diploma 4') => 'd4',
            str_contains($value, 'profesi') => 'profesi',
            default => null,
        };
    }

    private function normalizeStudyProgramText(string $value): string
    {
        $value = Str::of($value)->lower()->ascii()->toString();
        $value = str_replace(['&', '/', '-', '_'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]+/', ' ', $value);
        $value = preg_replace('/\b(s1|s2|s3|d3|d4|sarjana|magister|diploma|program|studi)\b/', ' ', $value);
        $value = preg_replace('/\b(pendidikan\s+profesi|pendidikan|profesi)\b/', ' ', $value);
        $value = preg_replace('/\bbidan\b/', ' kebidanan ', $value);
        $value = preg_replace('/\bilmu\b/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function studyProgramTokens(string $value): Collection
    {
        return collect(explode(' ', $value))
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => $token !== '' && strlen($token) > 2)
            ->unique()
            ->values();
    }
}
