<?php

namespace App\Filament\Resources\SintaLecturer\Pages;

use App\Filament\Resources\SintaLecturer\SintaLecturerResource;
use App\Models\SintaLecturer;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ImportSintaLecturers extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = SintaLecturerResource::class;

    protected string $view = 'filament.resources.sinta-lecturer.pages.import-sinta-lecturers';

    protected static ?string $title = 'Ambil Data Dosen SINTA';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $totalLecturers = SintaLecturer::query()->count();
        $statusHtml = "<div style='padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #059669; font-weight: 500;'>✅ Total data dosen SINTA di database: <b>{$totalLecturers}</b></div>";

        $urlPerbarui = route('scrap.perbaruiDosen');
        $buttonStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer; background-color: #525252;';
        $syncButtonHtml = '<button type="button" id="btn-perbarui" style="' . $buttonStyle . '">Ambil / Sinkronisasi Data Dosen SINTA</button>';

        $terminalHtml = <<<HTML
        <div wire:ignore x-data="{
            init() {
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
                    button.innerText = isLoading ? '⏳ Memproses...' : originalText;
                    button.style.opacity = isLoading ? '0.5' : '1';
                };

                const openStream = (url, onDone, onErrorText) => {
                    appendTerminal('[SSE] Membuka koneksi: ' + url + NL);
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
                            appendTerminal(NL + '[ERROR] Gagal membaca response stream: ' + error.message + NL);
                        }
                    };

                    eventSource.onerror = () => {
                        eventSource.close();
                        appendTerminal(onErrorText + NL);
                    };
                };

                document.addEventListener('click', (event) => {
                    const btnPerbarui = event.target.closest('#btn-perbarui');
                    if (!btnPerbarui) return;
                    event.preventDefault();
                    resetTerminal('>>> Memulai sinkronisasi data master dosen SINTA...' + NL);
                    toggleLoading(btnPerbarui, true, 'Ambil / Sinkronisasi Data Dosen SINTA');
                    openStream('{$urlPerbarui}', () => {
                        appendTerminal(NL + '[SUKSES] Data dosen SINTA berhasil diperbarui. Memuat ulang halaman...' + NL);
                        setTimeout(() => { window.location.reload(); }, 2000);
                    }, NL + '[ERROR] Koneksi sinkronisasi dosen diputus server. Cek route scrap.perbaruiDosen atau log Laravel.');
                });
            }
        }" style="background-color: #0a0a0a; border-radius: 0.75rem; border: 1px solid #262626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; height: 450px; overflow: hidden; margin-top: 1.5rem;">
            <div style="background-color: #171717; padding: 0.75rem 1rem; border-bottom: 1px solid #262626; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; letter-spacing: 0.05em;">Terminal Real-time SINTA Lecturer Sync Output</span>
                </div>
                <button type="button" onclick="document.getElementById('output-box').innerHTML='Menunggu perintah...' + String.fromCharCode(10)" style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; background: none; border: none; cursor: pointer;">Clear Log</button>
            </div>
            <div id="terminal-container" style="padding: 1rem; overflow-y: auto; flex-grow: 1; background-color: #0a0a0a;">
                <pre id="output-box" style="color: #4ade80; margin: 0; white-space: pre-wrap; word-break: break-all; font-family: ui-monospace, monospace; font-size: 0.875rem; line-height: 1.5;">Menunggu perintah...</pre>
            </div>
        </div>
        HTML;

        return $schema
            ->schema([
                Section::make('Tahap 1: Ambil / Sinkronisasi Data Dosen SINTA')
                    ->description('Mengambil data master dosen dari SINTA dan menyimpannya ke tabel sinta_lecturers.')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Placeholder::make('status_sinta_lecturers')
                            ->label('Status Data Dosen SINTA')
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
