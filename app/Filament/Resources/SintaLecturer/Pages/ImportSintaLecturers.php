<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Http\Controllers\SmartBulkSintaLecturerController;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ImportSintaLecturers extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = SintaLecturerResource::class;

    protected string $view = 'filament.resources.sinta-lecturer.pages.import-sinta-lecturers';

    protected static ?string $title = 'Import Lecturers';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function settingProdiFetchAllAction(): Actions\Action
    {
        return Actions\Action::make('settingProdiFetchAll')
            ->label('Setting Prodi Fetch All')
            ->icon('heroicon-o-academic-cap')
            ->color('warning')
            ->modalHeading('Setting Prodi Fetch All')
            ->modalDescription('Gunakan search/filter untuk memilih dosen yang ingin disetting. Program studi otomatis dari Excel tetap bisa diganti atau dipilih lebih dari satu sebelum disimpan.')
            ->modalWidth('7xl')
            ->fillForm(fn (): array => [
                'filter_search' => null,
                'filter_study_program_id' => null,
                'lecturers' => $this->getBulkProdiSettingRows(),
            ])
            ->form([
                Section::make('Filter Data')
                    ->description('Filter langsung aktif saat mengetik atau memilih program studi. Pilih Belum disetting / Null untuk menampilkan dosen yang belum punya setting tersimpan.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('filter_search')
                                    ->label('Cari Nama Dosen / SINTA ID')
                                    ->placeholder('Ketik nama atau SINTA ID...')
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
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
                                    ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
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
                                    ->preload()
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

        $programStudis = $this->getStudyProgramOptions();

        $routes = [
            'syncLecturers' => route('scrap.perbaruiDosen'),
            'fetchSelected' => route('scrap.ambilDetail', ':id'),
            'importSelected' => route('scrap.importData', ':id'),
            'syncPrograms' => route('scrap.syncStudyPrograms'),
            'fetchAll' => route('scrap.sintaFetchBatches.fetchAll'),
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
            'fetchAll' => '<button type="button" id="btn-fetch-all-details" style="' . $buttonBaseStyle . ' background-color: #0f766e;">Fetch All Registered Lecturers</button>',
            'resume' => '<button type="button" id="btn-resume-fetch" style="' . $buttonSecondaryStyle . '">Resume Fetch</button>',
            'retry' => '<button type="button" id="btn-retry-failed" style="' . $buttonSecondaryStyle . '">Retry Failed</button>',
            'reset' => '<button type="button" id="btn-reset-batch" style="' . $buttonSecondaryStyle . '">Reset Batch</button>',
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
                    if (runButtonStream(event, '#btn-fetch-all-details', routes.fetchAll, '>>> Starting bulk fetch for all registered lecturers...', 'Fetch All Registered Lecturers', 'Bulk fetch finished', 'All available lecturers were processed.', 'Bulk fetch failed')) return;
                    if (runButtonStream(event, '#btn-resume-fetch', routes.resume, '>>> Resuming latest lecturer fetch batch...', 'Resume Fetch', 'Fetch batch resumed', 'The latest pending batch was processed.', 'Resume fetch failed')) return;
                    if (runButtonStream(event, '#btn-retry-failed', routes.retryFailed, '>>> Retrying failed lecturer fetch items...', 'Retry Failed', 'Failed items retried', 'Failed lecturers were processed again.', 'Retry failed')) return;
                    if (runButtonStream(event, '#btn-reset-batch', routes.reset, '>>> Resetting latest lecturer fetch batch...', 'Reset Batch', 'Batch reset', 'The latest batch was cancelled.', 'Reset batch failed')) return;
                    if (runButtonStream(event, '#btn-import-all', routes.importAll, '>>> Starting Import All to Database...', 'Import All to Database', 'Import All finished', 'All ready lecturers have been imported.', 'Import All failed')) return;
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
                window.__bulkSintaLecturerImportCleanup = () => document.removeEventListener('click', clickHandler);
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
                                Placeholder::make('button_resume_fetch')->hiddenLabel()->content(new HtmlString($buttons['resume'])),
                                Placeholder::make('button_retry_failed')->hiddenLabel()->content(new HtmlString($buttons['retry'])),
                                Placeholder::make('button_reset_batch')->hiddenLabel()->content(new HtmlString($buttons['reset'])),
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

    private function getBulkProdiSettingRows(?string $search = null, mixed $studyProgramFilter = null): array
    {
        $response = app(SmartBulkSintaLecturerController::class)->studyProgramSettings();
        $payload = $response->getData(true);

        if ($response->getStatusCode() >= 400) {
            Notification::make()
                ->title('Setting Prodi belum bisa dibuka')
                ->body(data_get($payload, 'message', 'Jalankan migration dan Fetch All terlebih dahulu.'))
                ->danger()
                ->send();

            return [];
        }

        $normalizedSearch = trim(strtolower((string) $search));
        $studyProgramFilter = filled($studyProgramFilter) ? (string) $studyProgramFilter : null;

        return collect(data_get($payload, 'items', []))
            ->map(fn (array $item): array => [
                'sinta_id' => (string) data_get($item, 'sinta_id', ''),
                'lecturer_name' => (string) data_get($item, 'lecturer_name', '-'),
                'fetch_status' => (string) data_get($item, 'fetch_status', '-'),
                'detected_study_program' => (string) data_get($item, 'detected_study_program', ''),
                'study_program_ids' => collect(data_get($item, 'study_program_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values()
                    ->toArray(),
                'setting_status' => (string) data_get($item, 'setting_status', 'not_set'),
            ])
            ->when($normalizedSearch !== '', function ($rows) use ($normalizedSearch) {
                return $rows->filter(function (array $row) use ($normalizedSearch): bool {
                    return str_contains(strtolower($row['lecturer_name'] ?? ''), $normalizedSearch)
                        || str_contains(strtolower($row['sinta_id'] ?? ''), $normalizedSearch);
                });
            })
            ->when($studyProgramFilter, function ($rows) use ($studyProgramFilter) {
                return $rows->filter(function (array $row) use ($studyProgramFilter): bool {
                    $selectedIds = collect($row['study_program_ids'] ?? [])->map(fn ($id) => (string) $id);

                    if ($studyProgramFilter === '__null__') {
                        return $selectedIds->isEmpty() || ($row['setting_status'] ?? 'not_set') !== 'complete';
                    }

                    return $selectedIds->contains((string) $studyProgramFilter);
                });
            })
            ->values()
            ->toArray();
    }

    private function saveBulkProdiSettings(array $data): void
    {
        $rows = collect(data_get($data, 'lecturers', []));

        DB::transaction(function () use ($rows): void {
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
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }
        });

        Notification::make()
            ->title('Setting prodi berhasil disimpan')
            ->body('Mapping program studi untuk batch fetch all sudah diperbarui.')
            ->success()
            ->send();
    }

    private function getStudyProgramFilterOptions(): array
    {
        return ['__null__' => 'Belum disetting / Null'] + $this->getStudyProgramOptions();
    }

    private function getStudyProgramOptions(): array
    {
        return StudyProgram::query()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (StudyProgram $program) => [
                $program->id => $program->display_name,
            ])
            ->toArray();
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
}
