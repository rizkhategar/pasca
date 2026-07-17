<?php

namespace App\Filament\Resources\PostgraduateLecturer\Pages;

use App\Filament\Resources\PostgraduateLecturer\PostgraduateLecturerResource;
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

        $programStudis = StudyProgram::query()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (StudyProgram $program) => [
                $program->id => $program->display_name,
            ])
            ->toArray();

        $urlPerbarui = route('scrap.perbaruiDosen');
        $urlAmbilDetail = route('scrap.ambilDetail', ':id');
        $urlImport = route('scrap.importData', ':id');
        $urlSyncProgramStudi = route('scrap.syncStudyPrograms');
        $urlFetchAllDetails = route('scrap.sintaFetchBatches.fetchAll');
        $urlResumeFetch = route('scrap.sintaFetchBatches.resume');
        $urlRetryFailed = route('scrap.sintaFetchBatches.retryFailed');
        $urlResetBatch = route('scrap.sintaFetchBatches.reset');
        $urlImportAll = route('scrap.sintaFetchBatches.importAll');
        $urlStudyProgramSettings = route('scrap.sintaFetchBatches.studyProgramSettings');
        $urlSaveStudyProgramSettings = route('scrap.sintaFetchBatches.saveStudyProgramSettings');

        $buttonBaseStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer; margin-top: 0.375rem;';
        $buttonSecondaryStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-weight: 600; color: #111827; border: 1px solid #d1d5db; cursor: pointer; background-color: #ffffff; margin-top: 0.375rem;';
        $syncSintaLecturerButtonHtml = '<button type="button" id="btn-perbarui" style="' . $buttonBaseStyle . ' background-color: #525252;">Sync SINTA Lecturers</button>';
        $fetchSelectedButtonHtml = '<button type="button" id="btn-ambil-detail" style="' . $buttonBaseStyle . ' background-color: #2563eb;">Fetch Selected Lecturer</button>';
        $fetchAllButtonHtml = '<button type="button" id="btn-fetch-all-details" style="' . $buttonBaseStyle . ' background-color: #0f766e;">Fetch All Registered Lecturers</button>';
        $resumeButtonHtml = '<button type="button" id="btn-resume-fetch" style="' . $buttonSecondaryStyle . '">Resume Fetch</button>';
        $retryButtonHtml = '<button type="button" id="btn-retry-failed" style="' . $buttonSecondaryStyle . '">Retry Failed</button>';
        $resetButtonHtml = '<button type="button" id="btn-reset-batch" style="' . $buttonSecondaryStyle . '">Reset Batch</button>';
        $syncStudyProgramButtonHtml = '<button type="button" id="btn-sync-program-studi" style="' . $buttonBaseStyle . ' background-color: #7c3aed;">Sync Study Programs</button>';
        $settingButtonHtml = '<button type="button" id="btn-open-prodi-settings" style="' . $buttonBaseStyle . ' background-color: #ea580c;">Setting Prodi Fetch All</button>';
        $importButtonHtml = '<button type="button" id="btn-import" style="' . $buttonBaseStyle . ' background-color: #16a34a;">Import Selected</button>';
        $importAllButtonHtml = '<button type="button" id="btn-import-all" style="' . $buttonBaseStyle . ' background-color: #15803d;">Import All to Database</button>';

        $terminalHtml = <<<HTML
        <div wire:ignore x-data="{
            init() {
                const livewire = this.\$wire;
                const NL = String.fromCharCode(10);
                const outputBox = document.getElementById('output-box');
                const terminalContainer = document.getElementById('terminal-container');
                const prodiModal = document.getElementById('bulk-prodi-modal');
                const prodiModalBody = document.getElementById('bulk-prodi-modal-body');
                const prodiSummary = document.getElementById('bulk-prodi-summary');
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

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

                const notify = (status, title, body = '') => {
                    if (livewire && typeof livewire.call === 'function') {
                        livewire.call('notifyFromBrowser', status, title, body);
                    } else if (livewire && typeof livewire.notifyFromBrowser === 'function') {
                        livewire.notifyFromBrowser(status, title, body);
                    }
                };

                const stripHtml = (value) => String(value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                const normalize = (value) => stripHtml(value).toLowerCase();

                const hasFatalKeyword = (text) => [
                    'traceback', 'gagal membuka halaman', 'httperror', 'status: 403', 'status: 404', 'status: 500',
                    'failed to connect to the docker python scraper', 'curl error', 'connection was interrupted',
                    'terjadi kesalahan fatal', '[fatal error]', 'fatal scraper pattern detected', 'import all is blocked',
                    'excel file was not found', 'merged detail excel was not downloaded'
                ].some((keyword) => text.includes(keyword));

                const hasWarningKeyword = (text) => [
                    'tidak ada publikasi', 'kosong/tidak ditemukan', 'membuat sheet berisi', 'sheet contains',
                    'empty sheet', 'grafik garuda tidak ditemukan', 'gagal menemukan xaxis', 'gagal menemukan series',
                    'success_with_warning', 'empty-data warning'
                ].some((keyword) => text.includes(keyword));

                const notifyFromOutput = (streamOutput, successTitle, successBody, errorTitle) => {
                    const plainText = stripHtml(streamOutput);
                    const normalized = normalize(streamOutput);

                    if (hasFatalKeyword(normalized)) {
                        notify('danger', errorTitle, plainText.slice(0, 240) || 'The process failed. Please check the terminal output and Laravel logs.');
                        return;
                    }

                    if (hasWarningKeyword(normalized)) {
                        notify('warning', successTitle + ' with warnings', 'The process finished, but some modules were empty. This is allowed for lecturers with no publication data.');
                        return;
                    }

                    notify('success', successTitle, successBody);
                };

                const openStream = (url, onDone, onErrorText, successTitle, successBody, errorTitle, onError = null) => {
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
                            notify('danger', 'Failed to parse import response', error.message);
                        }
                    };

                    eventSource.onerror = () => {
                        eventSource.close();
                        appendTerminal(onErrorText + NL);
                        notify('danger', errorTitle, stripHtml(onErrorText));
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
                        if (reloadAfter) setTimeout(() => { window.location.reload(); }, 1500);
                    }, NL + '[ERROR] Stream connection was interrupted. Check Laravel logs.', successTitle, successBody, errorTitle, () => {
                        toggleLoading(button, false, originalText);
                    });
                    return true;
                };

                const renderProdiSettings = (payload) => {
                    if (!prodiModalBody || !prodiSummary) return;
                    const programs = payload.programs || [];
                    const items = payload.items || [];
                    const summary = payload.summary || {};
                    const batch = payload.batch;

                    prodiSummary.innerHTML = batch
                        ? 'Batch #' + batch.id + ' | status: <b>' + batch.status + '</b> | ready: <b>' + (summary.ready_count || 0) + '</b> | belum setting: <b>' + (summary.missing_setting_count || 0) + '</b> | gagal: <b>' + (summary.failed_count || 0) + '</b> | belum fetch: <b>' + (summary.unfetched_count || 0) + '</b>'
                        : 'Belum ada batch fetch. Jalankan Fetch All terlebih dahulu.';

                    if (!items.length) {
                        prodiModalBody.innerHTML = '<tr><td colspan="5" style="padding: 0.75rem; text-align: center; color: #6b7280;">Belum ada data batch fetch.</td></tr>';
                        return;
                    }

                    prodiModalBody.innerHTML = items.map((item) => {
                        const disabled = item.can_set_program ? '' : 'disabled';
                        const options = programs.map((program) => {
                            const selected = (item.study_program_ids || []).includes(program.id) ? 'selected' : '';
                            return '<option value="' + program.id + '" ' + selected + '>' + program.display_name + '</option>';
                        }).join('');
                        const statusColor = item.fetch_status === 'failed' ? '#dc2626' : (item.fetch_status === 'success_with_warning' ? '#d97706' : '#16a34a');
                        return '<tr>' +
                            '<td style="padding:0.5rem;border-bottom:1px solid #e5e7eb;font-family:monospace;">' + item.sinta_id + '</td>' +
                            '<td style="padding:0.5rem;border-bottom:1px solid #e5e7eb;">' + (item.lecturer_name || '-') + '</td>' +
                            '<td style="padding:0.5rem;border-bottom:1px solid #e5e7eb;color:' + statusColor + ';font-weight:700;">' + item.fetch_status + '</td>' +
                            '<td style="padding:0.5rem;border-bottom:1px solid #e5e7eb;"><select data-sinta-id="' + item.sinta_id + '" multiple ' + disabled + ' style="width:100%;min-width:260px;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.375rem;min-height:84px;">' + options + '</select></td>' +
                            '<td style="padding:0.5rem;border-bottom:1px solid #e5e7eb;">' + item.setting_status + '</td>' +
                        '</tr>';
                    }).join('');
                };

                const loadProdiSettings = () => {
                    if (!prodiModal) return;
                    prodiModal.style.display = 'flex';
                    if (prodiModalBody) prodiModalBody.innerHTML = '<tr><td colspan="5" style="padding:0.75rem;text-align:center;">Loading...</td></tr>';
                    fetch('{$urlStudyProgramSettings}', { headers: { 'Accept': 'application/json' } })
                        .then((response) => response.json())
                        .then(renderProdiSettings)
                        .catch((error) => {
                            notify('danger', 'Failed to load prodi settings', error.message);
                        });
                };

                const saveProdiSettings = () => {
                    const selects = Array.from(document.querySelectorAll('#bulk-prodi-modal select[data-sinta-id]'));
                    const settings = selects.map((select) => ({
                        sinta_id: select.dataset.sintaId,
                        study_program_ids: Array.from(select.selectedOptions).map((option) => option.value),
                    }));

                    fetch('{$urlSaveStudyProgramSettings}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ settings }),
                    })
                    .then((response) => response.json())
                    .then((payload) => {
                        if (!payload.success) throw new Error(payload.message || 'Failed to save settings.');
                        notify('success', 'Prodi settings saved', payload.message || 'Study program settings have been saved.');
                        loadProdiSettings();
                    })
                    .catch((error) => notify('danger', 'Failed to save prodi settings', error.message));
                };

                document.addEventListener('click', (event) => {
                    if (runButtonStream(event, '#btn-perbarui', '{$urlPerbarui}', '>>> Starting SINTA lecturer master sync...', 'Sync SINTA Lecturers', 'SINTA lecturers synced', 'SINTA lecturer data has been updated successfully.', 'SINTA lecturer sync failed', true)) return;
                    if (runButtonStream(event, '#btn-fetch-all-details', '{$urlFetchAllDetails}', '>>> Starting bulk fetch for all registered lecturers...', 'Fetch All Registered Lecturers', 'Bulk fetch finished', 'All available lecturers were processed.', 'Bulk fetch failed')) return;
                    if (runButtonStream(event, '#btn-resume-fetch', '{$urlResumeFetch}', '>>> Resuming latest lecturer fetch batch...', 'Resume Fetch', 'Fetch batch resumed', 'The latest pending batch was processed.', 'Resume fetch failed')) return;
                    if (runButtonStream(event, '#btn-retry-failed', '{$urlRetryFailed}', '>>> Retrying failed lecturer fetch items...', 'Retry Failed', 'Failed items retried', 'Failed lecturers were processed again.', 'Retry failed')) return;
                    if (runButtonStream(event, '#btn-reset-batch', '{$urlResetBatch}', '>>> Resetting latest lecturer fetch batch...', 'Reset Batch', 'Batch reset', 'The latest batch was cancelled.', 'Reset batch failed')) return;
                    if (runButtonStream(event, '#btn-import-all', '{$urlImportAll}', '>>> Starting Import All to Database...', 'Import All to Database', 'Import All finished', 'All ready lecturers have been imported.', 'Import All failed')) return;

                    const btnAmbilDetail = event.target.closest('#btn-ambil-detail');
                    if (btnAmbilDetail) {
                        event.preventDefault();
                        const sintaId = livewire.get('data.sinta_id');
                        if (!sintaId) {
                            notify('warning', 'Lecturer not selected', 'Please select a lecturer from the SINTA Lecturers master table first.');
                            return;
                        }
                        resetTerminal('>>> Fetching SINTA detail modules for ID: ' + sintaId + '...' + NL + NL);
                        toggleLoading(btnAmbilDetail, true, 'Fetch Selected Lecturer');
                        const targetUrl = '{$urlAmbilDetail}'.replace(':id', sintaId);
                        openStream(targetUrl, () => {
                            appendTerminal(NL + '[SUCCESS] All modules and merged import file have been generated.' + NL);
                            toggleLoading(btnAmbilDetail, false, 'Fetch Selected Lecturer');
                        }, NL + '[ERROR] Detail extraction was interrupted. Check route scrap.ambilDetail or Laravel logs.', 'SINTA detail fetched', 'The lecturer detail Excel file has been generated successfully.', 'Failed to fetch SINTA detail', () => {
                            toggleLoading(btnAmbilDetail, false, 'Fetch Selected Lecturer');
                        });
                        return;
                    }

                    const btnSyncProgramStudi = event.target.closest('#btn-sync-program-studi');
                    if (btnSyncProgramStudi) {
                        event.preventDefault();
                        resetTerminal('>>> Starting study program sync from UNW API...' + NL + NL);
                        toggleLoading(btnSyncProgramStudi, true, 'Sync Study Programs');
                        openStream('{$urlSyncProgramStudi}', () => {
                            appendTerminal(NL + '[SUCCESS] Study programs have been synced. Reloading dropdown...' + NL);
                            setTimeout(() => { window.location.reload(); }, 1500);
                        }, NL + '[ERROR] Study program sync was interrupted. Check route scrap.syncStudyPrograms or Laravel logs.', 'Study programs synced', 'All study programs have been synced successfully.', 'Study program sync failed', () => {
                            toggleLoading(btnSyncProgramStudi, false, 'Sync Study Programs');
                        });
                        return;
                    }

                    const btnImport = event.target.closest('#btn-import');
                    if (btnImport) {
                        event.preventDefault();
                        const sintaId = livewire.get('data.sinta_id');
                        const programStudi = livewire.get('data.program_studi');
                        if (!sintaId) {
                            notify('warning', 'SINTA ID was not found', 'Please select a lecturer in Step 2.');
                            return;
                        }
                        if (!programStudi || (Array.isArray(programStudi) && programStudi.length === 0)) {
                            notify('warning', 'Study program is required', 'Please select at least one Study Program.');
                            return;
                        }
                        const programStudiString = Array.isArray(programStudi) ? programStudi.join(',') : programStudi;
                        resetTerminal('>>> Importing lecturer into lecturers for SINTA ID: ' + sintaId + ' (study_program_id: ' + programStudiString + ')...' + NL);
                        toggleLoading(btnImport, true, 'Import Selected');
                        let targetUrl = '{$urlImport}'.replace(':id', sintaId);
                        targetUrl += '?jurusan=' + encodeURIComponent(programStudiString);
                        openStream(targetUrl, () => {
                            toggleLoading(btnImport, false, 'Import Selected');
                        }, NL + '[ERROR] Database import stream was interrupted. Check route scrap.importData or Laravel logs.', 'Postgraduate lecturer imported', 'The lecturer has been imported into lecturers successfully.', 'Postgraduate lecturer import failed', () => {
                            toggleLoading(btnImport, false, 'Import Selected');
                        });
                        return;
                    }

                    if (event.target.closest('#btn-open-prodi-settings')) {
                        event.preventDefault();
                        loadProdiSettings();
                        return;
                    }

                    if (event.target.closest('#btn-close-prodi-settings')) {
                        event.preventDefault();
                        if (prodiModal) prodiModal.style.display = 'none';
                        return;
                    }

                    if (event.target.closest('#btn-save-prodi-settings')) {
                        event.preventDefault();
                        saveProdiSettings();
                    }
                });
            }
        }">
            <div id="bulk-prodi-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
                <div style="background:#fff;border-radius:0.75rem;box-shadow:0 20px 40px rgba(0,0,0,0.25);width:min(1100px,96vw);max-height:86vh;display:flex;flex-direction:column;overflow:hidden;">
                    <div style="padding:1rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                        <div>
                            <h3 style="margin:0;font-size:1rem;font-weight:800;color:#111827;">Setting Prodi Fetch All</h3>
                            <p id="bulk-prodi-summary" style="margin:0.25rem 0 0;color:#4b5563;font-size:0.875rem;">Loading...</p>
                        </div>
                        <button type="button" id="btn-close-prodi-settings" style="border:0;background:#f3f4f6;border-radius:0.5rem;padding:0.5rem 0.75rem;cursor:pointer;font-weight:700;">Tutup</button>
                    </div>
                    <div style="overflow:auto;padding:1rem;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                            <thead>
                                <tr style="background:#f9fafb;text-align:left;">
                                    <th style="padding:0.5rem;border-bottom:1px solid #e5e7eb;">SINTA ID</th>
                                    <th style="padding:0.5rem;border-bottom:1px solid #e5e7eb;">Nama Dosen</th>
                                    <th style="padding:0.5rem;border-bottom:1px solid #e5e7eb;">Status Fetch</th>
                                    <th style="padding:0.5rem;border-bottom:1px solid #e5e7eb;">Program Studi</th>
                                    <th style="padding:0.5rem;border-bottom:1px solid #e5e7eb;">Status Setting</th>
                                </tr>
                            </thead>
                            <tbody id="bulk-prodi-modal-body"></tbody>
                        </table>
                    </div>
                    <div style="padding:1rem;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:0.75rem;">
                        <button type="button" id="btn-save-prodi-settings" style="background:#16a34a;color:#fff;border:0;border-radius:0.5rem;padding:0.625rem 1rem;font-weight:700;cursor:pointer;">Simpan Setting Prodi</button>
                    </div>
                </div>
            </div>

            <div style="background-color: #0a0a0a; border-radius: 0.75rem; border: 1px solid #262626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; height: 450px; overflow: hidden; margin-top: 1.5rem;">
                <div style="background-color: #171717; padding: 0.75rem 1rem; border-bottom: 1px solid #262626; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; letter-spacing: 0.05em;">Real-time Lecturer Import Output</span>
                    </div>
                    <button type="button" onclick="document.getElementById('output-box').innerHTML='Waiting for command...' + String.fromCharCode(10)" style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; background: none; border: none; cursor: pointer;">Clear Log</button>
                </div>
                <div id="terminal-container" style="padding: 1rem; overflow-y: auto; flex-grow: 1; background-color: #0a0a0a;">
                    <pre id="output-box" style="color: #4ade80; margin: 0; white-space: pre-wrap; word-break: break-all; font-family: ui-monospace, monospace; font-size: 0.875rem; line-height: 1.5;">Waiting for command...</pre>
                </div>
            </div>
        </div>
        HTML;

        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Step 1: Sync SINTA Lecturers')
                            ->description('Fetch master lecturer data from SINTA and store it in the sinta_lecturers table.')
                            ->icon('heroicon-o-arrow-path')
                            ->schema([
                                Placeholder::make('status_sinta_lecturers')
                                    ->label('SINTA Lecturer Data Status')
                                    ->content(new HtmlString($statusSintaLecturersHtml)),
                                Placeholder::make('button_sync_sinta_lecturers')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($syncSintaLecturerButtonHtml)),
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

                                        return $lecturer
                                            ? trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')')
                                            : null;
                                    })
                                    ->searchable()
                                    ->placeholder('-- Select Lecturer from SINTA Master --')
                                    ->required(),
                                Placeholder::make('button_ambil_detail')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($fetchSelectedButtonHtml)),
                                Placeholder::make('button_fetch_all_detail')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($fetchAllButtonHtml)),
                                Placeholder::make('button_resume_fetch')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($resumeButtonHtml)),
                                Placeholder::make('button_retry_failed')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($retryButtonHtml)),
                                Placeholder::make('button_reset_batch')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($resetButtonHtml)),
                            ])
                            ->columnSpan(1),

                        Section::make('Step 3: Setting Prodi & Import')
                            ->icon('heroicon-o-server')
                            ->description('Set study program mappings for batch results, then import selected or all ready lecturers.')
                            ->schema([
                                Placeholder::make('button_sync_program_studi')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($syncStudyProgramButtonHtml)),
                                Placeholder::make('button_setting_prodi_fetch_all')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($settingButtonHtml)),
                                Select::make('program_studi')
                                    ->label('Select Study Programs')
                                    ->options($programStudis)
                                    ->searchable()
                                    ->multiple()
                                    ->placeholder('-- Select Study Programs --')
                                    ->required()
                                    ->native(false),
                                Placeholder::make('button_import_database')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($importButtonHtml)),
                                Placeholder::make('button_import_all_database')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($importAllButtonHtml)),
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
