<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Models\SintaLecturer;
use App\Models\StudyProgram;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class ImportSintaLecturers extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * Ubah jam otomatis Fetch All SINTA di sini.
     * Format: HH:MM, contoh '00:00', '23:30'.
     */
    public const AUTO_FETCH_ALL_TIME = '00:00';

    protected static string $resource = SintaLecturerResource::class;

    protected string $view = 'filament.resources.sinta-lecturer.pages.import-sinta-lecturers';

    protected static ?string $title = 'Import Lecturers';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
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
            'importAll' => route('scrap.sintaFetchBatches.importAll'),
        ];

        $buttonBaseStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer; margin-top: 0.375rem; text-decoration: none;';

        $buttons = [
            'syncSinta' => '<button type="button" id="btn-perbarui" style="' . $buttonBaseStyle . ' background-color: #525252;">Sync SINTA Lecturers</button>',
            'fetchSelected' => '<button type="button" id="btn-ambil-detail" style="' . $buttonBaseStyle . ' background-color: #2563eb;">Fetch Selected Lecturer</button>',
            'fetchAll' => '<button type="button" id="btn-fetch-all-details" style="' . $buttonBaseStyle . ' background-color: #0f766e;">Fetch All / Lanjutkan Otomatis</button>',
            'syncPrograms' => '<button type="button" id="btn-sync-program-studi" style="' . $buttonBaseStyle . ' background-color: #7c3aed;">Sync Study Programs</button>',
            'settings' => '<a href="' . SintaLecturerResource::getUrl('bulk-prodi-settings') . '" style="' . $buttonBaseStyle . ' background-color: #ea580c;">Setting Prodi Fetch All</a>',
            'importSelected' => '<button type="button" id="btn-import" style="' . $buttonBaseStyle . ' background-color: #16a34a;">Import Selected</button>',
            'importAll' => '<button type="button" id="btn-import-all" style="' . $buttonBaseStyle . ' background-color: #15803d;">Import All to Database</button>',
        ];

        $routesJson = json_encode($routes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $terminalHtml = <<<'HTML'
        <div wire:ignore x-data x-init="new Function('livewire', $refs.runner.value)($wire)">
            <textarea x-ref="runner" hidden>
                if (window.__sintaImportPageCleanup) {
                    window.__sintaImportPageCleanup();
                }

                const routes = __ROUTES_JSON__;
                const NL = String.fromCharCode(10);

                const outputBox = document.getElementById('output-box');
                const terminalContainer = document.getElementById('terminal-container');

                const appendTerminal = (text) => {
                    if (!outputBox || !terminalContainer) return;
                    outputBox.appendChild(document.createTextNode(String(text || '')));
                    terminalContainer.scrollTop = terminalContainer.scrollHeight;
                };

                const resetTerminal = (text) => {
                    if (!outputBox || !terminalContainer) return;
                    outputBox.textContent = String(text || '');
                    terminalContainer.scrollTop = terminalContainer.scrollHeight;
                };

                const stripHtml = (value) => String(value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

                const notify = (status, title, body = '') => {
                    if (livewire && typeof livewire.call === 'function') {
                        livewire.call('notifyFromBrowser', status, title, body);
                    }
                };

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

                const getSelectedSintaId = () => String(getState('data.sinta_id') || '').trim();
                const getSelectedStudyPrograms = () => {
                    const value = getState('data.program_studi');
                    if (Array.isArray(value)) return value.filter((item) => item !== null && item !== undefined && String(item).trim() !== '');
                    return String(value || '').trim();
                };

                const toggleLoading = (button, isLoading, originalText) => {
                    if (!button) return;
                    button.disabled = isLoading;
                    button.innerText = isLoading ? '⏳ Processing...' : originalText;
                    button.style.opacity = isLoading ? '0.5' : '1';
                };

                const openStream = (url, options = {}) => {
                    const {
                        button = null,
                        originalText = '',
                        successTitle = 'Berhasil',
                        errorTitle = 'Gagal',
                        resetText = null,
                        reloadAfter = false,
                    } = options;

                    if (resetText !== null) {
                        resetTerminal(resetText + NL + NL);
                    }

                    if (button) {
                        toggleLoading(button, true, originalText);
                    }

                    appendTerminal('[SSE] Opening connection: ' + url + NL);

                    let streamOutput = '';
                    const eventSource = new EventSource(url);

                    eventSource.onmessage = (event) => {
                        try {
                            const data = JSON.parse(event.data);

                            if (data.output) {
                                streamOutput += data.output + NL;
                                appendTerminal(stripHtml(data.output) + NL);
                            }

                            if (data.done) {
                                eventSource.close();
                                if (button) toggleLoading(button, false, originalText);
                                notify('success', successTitle, stripHtml(streamOutput).slice(0, 240));
                                if (reloadAfter) setTimeout(() => window.location.reload(), 1500);
                            }
                        } catch (error) {
                            eventSource.close();
                            if (button) toggleLoading(button, false, originalText);
                            appendTerminal('[ERROR] Failed to parse stream response: ' + error.message + NL);
                            notify('danger', errorTitle, error.message);
                        }
                    };

                    eventSource.onerror = () => {
                        eventSource.close();
                        if (button) toggleLoading(button, false, originalText);
                        appendTerminal('[ERROR] Stream connection was interrupted. Check Laravel logs.' + NL);
                        notify('danger', errorTitle, 'Stream connection was interrupted.');
                    };
                };

                const clickHandler = (event) => {
                    const clearButton = event.target.closest('#btn-clear-terminal');
                    if (clearButton) {
                        event.preventDefault();
                        resetTerminal('Waiting for command...' + NL);
                        return;
                    }

                    const syncButton = event.target.closest('#btn-perbarui');
                    if (syncButton) {
                        event.preventDefault();
                        openStream(routes.syncLecturers, {
                            button: syncButton,
                            originalText: 'Sync SINTA Lecturers',
                            successTitle: 'SINTA lecturers synced',
                            errorTitle: 'SINTA lecturer sync failed',
                            resetText: '>>> Starting SINTA lecturer master sync...',
                            reloadAfter: true,
                        });
                        return;
                    }

                    const fetchSelectedButton = event.target.closest('#btn-ambil-detail');
                    if (fetchSelectedButton) {
                        event.preventDefault();
                        const sintaId = getSelectedSintaId();
                        if (!sintaId) {
                            notify('warning', 'Lecturer not selected', 'Please select a lecturer first.');
                            return;
                        }

                        openStream(routes.fetchSelected.replace(':id', encodeURIComponent(sintaId)), {
                            button: fetchSelectedButton,
                            originalText: 'Fetch Selected Lecturer',
                            successTitle: 'SINTA detail fetched',
                            errorTitle: 'Failed to fetch SINTA detail',
                            resetText: '>>> Fetching SINTA detail modules for ID: ' + sintaId + '...',
                        });
                        return;
                    }

                    const fetchAllButton = event.target.closest('#btn-fetch-all-details');
                    if (fetchAllButton) {
                        event.preventDefault();
                        openStream(routes.fetchAll, {
                            button: fetchAllButton,
                            originalText: 'Fetch All / Lanjutkan Otomatis',
                            successTitle: 'Fetch All queued',
                            errorTitle: 'Fetch All gagal dijalankan',
                            resetText: '>>> Queueing Fetch All in background...',
                        });
                        return;
                    }

                    const syncProgramsButton = event.target.closest('#btn-sync-program-studi');
                    if (syncProgramsButton) {
                        event.preventDefault();
                        openStream(routes.syncPrograms, {
                            button: syncProgramsButton,
                            originalText: 'Sync Study Programs',
                            successTitle: 'Study programs synced',
                            errorTitle: 'Study program sync failed',
                            resetText: '>>> Starting study program sync from UNW API...',
                            reloadAfter: true,
                        });
                        return;
                    }

                    const importSelectedButton = event.target.closest('#btn-import');
                    if (importSelectedButton) {
                        event.preventDefault();
                        const sintaId = getSelectedSintaId();
                        const programStudi = getSelectedStudyPrograms();

                        if (!sintaId) {
                            notify('warning', 'SINTA ID was not found', 'Please select a lecturer in Step 2.');
                            return;
                        }

                        if (!programStudi || (Array.isArray(programStudi) && programStudi.length === 0)) {
                            notify('warning', 'Study program is required', 'Please select at least one Study Program.');
                            return;
                        }

                        const programStudiString = Array.isArray(programStudi) ? programStudi.join(',') : programStudi;

                        openStream(routes.importSelected.replace(':id', encodeURIComponent(sintaId)) + '?jurusan=' + encodeURIComponent(programStudiString), {
                            button: importSelectedButton,
                            originalText: 'Import Selected',
                            successTitle: 'Lecturer imported',
                            errorTitle: 'Lecturer import failed',
                            resetText: '>>> Importing lecturer into database for SINTA ID: ' + sintaId + '...',
                        });
                        return;
                    }

                    const importAllButton = event.target.closest('#btn-import-all');
                    if (importAllButton) {
                        event.preventDefault();
                        openStream(routes.importAll, {
                            button: importAllButton,
                            originalText: 'Import All to Database',
                            successTitle: 'Import All queued',
                            errorTitle: 'Import All gagal dijalankan',
                            resetText: '>>> Queueing Import All in background...',
                        });
                    }
                };

                document.addEventListener('click', clickHandler);

                window.__sintaImportPageCleanup = () => {
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
                            ])
                            ->columnSpan(1),

                        Section::make('Step 3: Setting Prodi & Import')
                            ->icon('heroicon-o-server')
                            ->description('Fetch All otomatis mengisi tabel setting prodi dari Excel merged. Buka table setting untuk koreksi data yang kosong/null.')
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

    protected function getSintaLecturerOptions(?string $search = null): array
    {
        return SintaLecturer::query()
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sinta_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (SintaLecturer $lecturer): array => [
                $lecturer->sinta_id => trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')'),
            ])
            ->toArray();
    }

    protected function getStudyProgramOptions(): array
    {
        return Cache::remember('sinta_import_study_program_options_v4', now()->addMinutes(10), function (): array {
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
}
