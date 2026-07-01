<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Models\SintaLecturer;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Throwable;

class ImportSintaLecturers extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = SintaLecturerResource::class;

    protected string $view = 'filament.resources.sinta-lecturer.pages.import-sinta-lecturers';

    protected static ?string $title = 'Import SINTA Lecturers';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addManualLecturer')
                ->label('Add Manual Lecturer')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->modalHeading('Add Manual SINTA Lecturer')
                ->modalWidth('md')
                ->form([
                    TextInput::make('sinta_id')
                        ->label('SINTA ID')
                        ->placeholder('Example: 6954305')
                        ->required()
                        ->numeric(),
                    TextInput::make('name')
                        ->label('Lecturer Name')
                        ->placeholder('Full name with academic title')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $sintaId = preg_replace('/[^0-9]/', '', (string) ($data['sinta_id'] ?? ''));

                    if ($sintaId === '') {
                        Notification::make()
                            ->title('Invalid SINTA ID')
                            ->body('Please enter a valid numeric SINTA ID.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if (SintaLecturer::where('sinta_id', $sintaId)->exists()) {
                        Notification::make()
                            ->title('Lecturer already exists')
                            ->body('This SINTA ID is already registered in the master lecturer table.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        SintaLecturer::create([
                            'sinta_id' => $sintaId,
                            'name' => $data['name'],
                            'department' => 'Manual Registration',
                        ]);

                        Notification::make()
                            ->title('Lecturer added')
                            ->body('The lecturer has been added to the SINTA lecturer master table.')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Failed to add lecturer')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
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
        $statusHtml = "<div style='padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #059669; font-weight: 500;'>✅ Total SINTA lecturer records in database: <b>{$totalLecturers}</b></div>";

        $urlPerbarui = route('scrap.perbaruiDosen');
        $buttonStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer; background-color: #525252;';
        $syncButtonHtml = '<button type="button" id="btn-perbarui" style="' . $buttonStyle . '">Sync SINTA Lecturers</button>';

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

                const notify = (status, title, body = '') => {
                    if (livewire && typeof livewire.call === 'function') {
                        livewire.call('notifyFromBrowser', status, title, body);
                    } else if (livewire && typeof livewire.notifyFromBrowser === 'function') {
                        livewire.notifyFromBrowser(status, title, body);
                    }
                };

                const stripHtml = (value) => String(value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

                const hasErrorKeyword = (text) => [
                    '[error]', 'error', 'gagal', 'failed', 'fatal', 'exception', 'database error',
                    'tidak tersedia', 'not found', 'could not', 'interrupted', 'tidak ditemukan'
                ].some((keyword) => text.includes(keyword));

                const notifyFromOutput = (streamOutput, successTitle, successBody, errorTitle) => {
                    const plainText = stripHtml(streamOutput);
                    const normalized = plainText.toLowerCase();

                    if (hasErrorKeyword(normalized)) {
                        notify('danger', errorTitle, plainText.slice(0, 240) || 'The process failed. Please check the terminal output and Laravel logs.');
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

                document.addEventListener('click', (event) => {
                    const btnPerbarui = event.target.closest('#btn-perbarui');
                    if (!btnPerbarui) return;
                    event.preventDefault();
                    resetTerminal('>>> Starting SINTA lecturer master sync...' + NL);
                    toggleLoading(btnPerbarui, true, 'Sync SINTA Lecturers');
                    openStream('{$urlPerbarui}', () => {
                        appendTerminal(NL + '[SUCCESS] SINTA lecturer data has been updated. Reloading page...' + NL);
                        setTimeout(() => { window.location.reload(); }, 2000);
                    }, NL + '[ERROR] Lecturer sync connection was interrupted. Check route scrap.perbaruiDosen or Laravel logs.', 'SINTA lecturers synced', 'SINTA lecturer data has been updated successfully.', 'SINTA lecturer sync failed', () => {
                        toggleLoading(btnPerbarui, false, 'Sync SINTA Lecturers');
                    });
                });
            }
        }" style="background-color: #0a0a0a; border-radius: 0.75rem; border: 1px solid #262626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; height: 450px; overflow: hidden; margin-top: 1.5rem;">
            <div style="background-color: #171717; padding: 0.75rem 1rem; border-bottom: 1px solid #262626; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; letter-spacing: 0.05em;">Real-time SINTA Lecturer Sync Output</span>
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
                Section::make('Step 1: Sync SINTA Lecturers')
                    ->description('Fetch master lecturer data from SINTA and store it in the sinta_lecturers table.')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Placeholder::make('status_sinta_lecturers')
                            ->label('SINTA Lecturer Data Status')
                            ->content(new HtmlString($statusHtml)),

                        Placeholder::make('button_sync_sinta_lecturers')
                            ->hiddenLabel()
                            ->content(new HtmlString($syncButtonHtml)),
                    ]),

                Placeholder::make('terminal_sync')
                    ->hiddenLabel()
                    ->content(new HtmlString($terminalHtml)),
            ])
            ->statePath('data');
    }
}
