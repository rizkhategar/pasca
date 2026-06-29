<?php

namespace App\Filament\Resources\PostgraduateLecturer\Pages;

use App\Filament\Resources\PostgraduateLecturer\PostgraduateLecturerResource;
use App\Models\SintaLecturer;
use App\Models\StudyProgram;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class ImportPostgraduateLecturer extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = PostgraduateLecturerResource::class;

    protected string $view = 'filament.resources.postgraduate-lecturer.pages.import-postgraduate-lecturer';

    protected static ?string $title = 'Import Postgraduate Lecturers';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $programStudis = Cache::remember('study_programs_select_import', now()->addHours(12), function () {
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

        $urlAmbilDetail = route('scrap.ambilDetail', ':id');
        $urlImport = route('scrap.importData', ':id');
        $urlSyncProgramStudi = route('scrap.syncStudyPrograms');

        $buttonBaseStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer;';
        $extractButtonHtml = '<button type="button" id="btn-ambil-detail" style="' . $buttonBaseStyle . ' background-color: #2563eb;">Fetch SINTA Lecturer Detail</button>';
        $syncStudyProgramButtonHtml = '<button type="button" id="btn-sync-program-studi" style="' . $buttonBaseStyle . ' background-color: #7c3aed;">Sync Study Programs</button>';
        $importButtonHtml = '<button type="button" id="btn-import" style="' . $buttonBaseStyle . ' background-color: #16a34a;">Import to Postgraduate</button>';

        $terminalHtml = <<<HTML
        <div wire:ignore x-data="{
            init() {
                const livewire = this.\$wire;
                const NL = String.fromCharCode(10);
                const outputBox = document.getElementById('output-box');
                const terminalContainer = document.getElementById('terminal-container');

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

                const toggleLoading = (button, isLoading, originalText) => {
                    if (!button) return;
                    button.disabled = isLoading;
                    button.innerText = isLoading ? '⏳ Processing...' : originalText;
                    button.style.opacity = isLoading ? '0.5' : '1';
                };

                const openStream = (url, onDone, onErrorText) => {
                    appendTerminal('[SSE] Opening connection: ' + url + NL);
                    const eventSource = new EventSource(url);

                    eventSource.onmessage = (event) => {
                        try {
                            const data = JSON.parse(event.data);
                            if (data.output) appendTerminal(data.output);
                            if (data.done) {
                                eventSource.close();
                                if (onDone) onDone();
                            }
                        } catch (error) {
                            appendTerminal(NL + '[ERROR] Failed to parse stream response: ' + error.message + NL);
                        }
                    };

                    eventSource.onerror = () => {
                        eventSource.close();
                        appendTerminal(onErrorText + NL);
                    };
                };

                document.addEventListener('click', (event) => {
                    const btnAmbilDetail = event.target.closest('#btn-ambil-detail');
                    if (!btnAmbilDetail) return;
                    event.preventDefault();
                    const sintaId = livewire.get('data.sinta_id');
                    if (!sintaId) return alert('Please select a lecturer from the SINTA Lecturers master table first.');
                    resetTerminal('>>> Fetching SINTA detail modules for ID: ' + sintaId + '...' + NL + NL);
                    toggleLoading(btnAmbilDetail, true, 'Fetch SINTA Lecturer Detail');
                    const targetUrl = '{$urlAmbilDetail}'.replace(':id', sintaId);
                    openStream(targetUrl, () => {
                        appendTerminal(NL + '[SUCCESS] All modules and merged import file have been generated.' + NL);
                        toggleLoading(btnAmbilDetail, false, 'Fetch SINTA Lecturer Detail');
                    }, NL + '[ERROR] Detail extraction was interrupted. Check route scrap.ambilDetail or Laravel logs.');
                });

                document.addEventListener('click', (event) => {
                    const btnSyncProgramStudi = event.target.closest('#btn-sync-program-studi');
                    if (!btnSyncProgramStudi) return;
                    event.preventDefault();
                    resetTerminal('>>> Starting postgraduate study program sync from UNW API...' + NL + NL);
                    toggleLoading(btnSyncProgramStudi, true, 'Sync Study Programs');
                    openStream('{$urlSyncProgramStudi}', () => {
                        appendTerminal(NL + '[SUCCESS] Study programs have been synced. Reloading dropdown...' + NL);
                        setTimeout(() => { window.location.reload(); }, 1500);
                    }, NL + '[ERROR] Study program sync was interrupted. Check route scrap.syncStudyPrograms or Laravel logs.');
                });

                document.addEventListener('click', (event) => {
                    const btnImport = event.target.closest('#btn-import');
                    if (!btnImport) return;
                    event.preventDefault();
                    const sintaId = livewire.get('data.sinta_id');
                    const programStudi = livewire.get('data.program_studi');
                    if (!sintaId) return alert('SINTA ID was not found. Please select a lecturer in Step 1.');
                    if (!programStudi || (Array.isArray(programStudi) && programStudi.length === 0)) {
                        return alert('Please select at least one Postgraduate Study Program.');
                    }
                    const programStudiString = Array.isArray(programStudi) ? programStudi.join(',') : programStudi;
                    resetTerminal('>>> Importing lecturer into postgraduate_lecturers for SINTA ID: ' + sintaId + ' (study_program_id: ' + programStudiString + ')...' + NL);
                    toggleLoading(btnImport, true, 'Import to Postgraduate');
                    let targetUrl = '{$urlImport}'.replace(':id', sintaId);
                    targetUrl += '?jurusan=' + encodeURIComponent(programStudiString);
                    openStream(targetUrl, () => {
                        toggleLoading(btnImport, false, 'Import to Postgraduate');
                    }, NL + '[ERROR] Database import stream was interrupted. Check route scrap.importData or Laravel logs.');
                });
            }
        }" style="background-color: #0a0a0a; border-radius: 0.75rem; border: 1px solid #262626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; height: 450px; overflow: hidden; margin-top: 1.5rem;">
            <div style="background-color: #171717; padding: 0.75rem 1rem; border-bottom: 1px solid #262626; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; letter-spacing: 0.05em;">Real-time Postgraduate Import Output</span>
                </div>
                <button type="button" onclick="document.getElementById('output-box').innerHTML='Waiting for command...' + String.fromCharCode(10)" style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; background: none; border: none; cursor: pointer;">Clear Log</button>
            </div>
            <div id="terminal-container" style="padding: 1rem; overflow-y: auto; flex-grow: 1; background-color: #0a0a0a;">
                <pre id="output-box" style="color: #4ade80; margin: 0; white-space: pre-wrap; word-break: break-all; font-family: ui-monospace, monospace; font-size: 0.875rem; line-height: 1.5;">Waiting for command...</pre>
            </div>
        </div>
        HTML;

        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        Section::make('Step 1: Fetch SINTA Lecturer Detail')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->description('Select a lecturer from the sinta_lecturers master table, then fetch the SINTA detail data needed for import.')
                            ->schema([
                                Select::make('sinta_id')
                                    ->label('Select Lecturer from SINTA Lecturers')
                                    ->options($this->getSintaLecturerOptions())
                                    ->getSearchResultsUsing(fn (string $search): array => $this->getSintaLecturerOptions($search))
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $lecturer = SintaLecturer::where('sinta_id', $value)->first();

                                        return $lecturer
                                            ? trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')')
                                            : null;
                                    })
                                    ->searchable()
                                    ->placeholder('-- Select Lecturer from SINTA Master --')
                                    ->required(),
                                Placeholder::make('button_ambil_detail')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($extractButtonHtml)),
                            ])
                            ->columnSpan(1),

                        Section::make('Step 2: Import to Postgraduate')
                            ->icon('heroicon-o-server')
                            ->description('Select postgraduate study programs, then register the lecturer into postgraduate_lecturers and postgraduate_lecturer_study_programs.')
                            ->schema([
                                Placeholder::make('button_sync_program_studi')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($syncStudyProgramButtonHtml)),
                                Select::make('program_studi')
                                    ->label('Select Postgraduate Study Programs')
                                    ->options($programStudis)
                                    ->searchable()
                                    ->multiple()
                                    ->placeholder('-- Select Postgraduate Study Programs --')
                                    ->required()
                                    ->native(false),
                                Placeholder::make('button_import_database')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($importButtonHtml)),
                            ])
                            ->columnSpan(1),
                    ]),

                Placeholder::make('terminal_sync')
                    ->hiddenLabel()
                    ->content(new HtmlString($terminalHtml)),
            ])
            ->statePath('data');
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
