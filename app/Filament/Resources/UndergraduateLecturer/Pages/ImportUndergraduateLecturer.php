<?php

namespace App\Filament\Resources\UndergraduateLecturer\Pages;

use App\Filament\Resources\UndergraduateLecturer\UndergraduateLecturerResource;
use App\Models\SintaLecturer;
use App\Models\StudyProgram;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class ImportUndergraduateLecturer extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = UndergraduateLecturerResource::class;

    protected string $view = 'filament.resources.undergraduate-lecturer.pages.import-undergraduate-lecturer';

    protected static ?string $title = 'Import & Scraping SINTA Undergraduate';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $sintaLecturerExists = SintaLecturer::query()->exists();
        $statusHtml = $sintaLecturerExists
            ? '<div style="padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #059669; font-weight: 500;">✅ <b>Data daftar dosen tersedia.</b></div>'
            : '<div style="padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #dc2626; font-weight: 500;">⚠️ <b>Daftar dosen kosong.</b> Silahkan lakukan scraping data dosen.</div>';

        $programStudis = Cache::remember('study_programs_undergraduate_select_import', now()->addHours(12), function () {
            return StudyProgram::query()
                ->where(function ($query) {
                    $query->whereNull('jenjang')
                        ->orWhere('jenjang', 'not like', '%Magister%');
                })
                ->where(function ($query) {
                    $query->whereNull('jenjang_nama_singkat')
                        ->orWhere('jenjang_nama_singkat', '!=', 'S2');
                })
                ->orderBy('jenjang')
                ->orderBy('nama')
                ->get()
                ->mapWithKeys(fn (StudyProgram $program) => [
                    $program->id => $program->display_name,
                ])
                ->toArray();
        });

        $urlPerbarui = route('scrap.perbaruiDosen');
        $urlAmbilDetail = route('scrap.ambilDetail', ':id');
        $urlImport = route('undergraduate-scrap.importData', ':id');
        $urlSyncProgramStudi = route('scrap.syncStudyPrograms');

        $buttonBaseStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer;';
        $scrapeButtonHtml = '<button type="button" id="btn-perbarui" style="' . $buttonBaseStyle . ' background-color: #525252;">Mulai Scraping Dosen</button>';
        $extractButtonHtml = '<button type="button" id="btn-ambil-detail" style="' . $buttonBaseStyle . ' background-color: #2563eb;">Ekstrak Data SINTA</button>';
        $syncStudyProgramButtonHtml = '<button type="button" id="btn-sync-program-studi" style="' . $buttonBaseStyle . ' background-color: #7c3aed;">Sinkronisasi Program Studi</button>';
        $importButtonHtml = '<button type="button" id="btn-import" style="' . $buttonBaseStyle . ' background-color: #16a34a;">Import ke Undergraduate</button>';

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
                    resetTerminal('>>> Memulai pembaruan data master dosen (dosen.py)....' + NL);
                    toggleLoading(btnPerbarui, true, 'Mulai Scraping Dosen');
                    openStream('{$urlPerbarui}', () => {
                        appendTerminal(NL + '[SUKSES] Daftar dosen berhasil diperbarui. Memuat ulang...' + NL);
                        setTimeout(() => { window.location.reload(); }, 2000);
                    }, NL + '[ERROR] Koneksi scraping dosen diputus server. Cek route scrap.perbaruiDosen atau log Laravel.');
                });

                document.addEventListener('click', (event) => {
                    const btnAmbilDetail = event.target.closest('#btn-ambil-detail');
                    if (!btnAmbilDetail) return;
                    event.preventDefault();
                    const sintaId = livewire.get('data.sinta_id');
                    if (!sintaId) return alert('Silakan pilih dosen terlebih dahulu!');
                    resetTerminal('>>> Mengekstrak detail modul SINTA untuk ID: ' + sintaId + '...' + NL + NL);
                    toggleLoading(btnAmbilDetail, true, 'Ekstrak Data SINTA');
                    const targetUrl = '{$urlAmbilDetail}'.replace(':id', sintaId);
                    openStream(targetUrl, () => {
                        appendTerminal(NL + '[SUKSES] Seluruh modul & file gabungan berhasil dibuat.' + NL);
                        toggleLoading(btnAmbilDetail, false, 'Ekstrak Data SINTA');
                    }, NL + '[ERROR] Ekstraksi terputus. Cek route scrap.ambilDetail atau log Laravel.');
                });

                document.addEventListener('click', (event) => {
                    const btnSyncProgramStudi = event.target.closest('#btn-sync-program-studi');
                    if (!btnSyncProgramStudi) return;
                    event.preventDefault();
                    resetTerminal('>>> Memulai sinkronisasi program studi dari API UNW...' + NL + NL);
                    toggleLoading(btnSyncProgramStudi, true, 'Sinkronisasi Program Studi');
                    openStream('{$urlSyncProgramStudi}', () => {
                        appendTerminal(NL + '[SUKSES] Program studi berhasil disinkronkan. Memuat ulang dropdown...' + NL);
                        setTimeout(() => { window.location.reload(); }, 1500);
                    }, NL + '[ERROR] Sinkronisasi program studi terputus. Cek route scrap.syncStudyPrograms atau log Laravel.');
                });

                document.addEventListener('click', (event) => {
                    const btnImport = event.target.closest('#btn-import');
                    if (!btnImport) return;
                    event.preventDefault();
                    const sintaId = livewire.get('data.sinta_id');
                    const programStudi = livewire.get('data.program_studi');
                    if (!sintaId) return alert('SINTA ID tidak ditemukan. Pilih dosen dulu pada langkah 2.');
                    if (!programStudi || (Array.isArray(programStudi) && programStudi.length === 0)) {
                        return alert('Silakan pilih sekurang-kurangnya satu Program Studi Undergraduate!');
                    }
                    const programStudiString = Array.isArray(programStudi) ? programStudi.join(',') : programStudi;
                    resetTerminal('>>> Memulai migrasi streaming data Excel ke MySQL untuk Undergraduate SINTA ID: ' + sintaId + ' (study_program_id: ' + programStudiString + ')...' + NL);
                    toggleLoading(btnImport, true, 'Import ke Undergraduate');
                    let targetUrl = '{$urlImport}'.replace(':id', sintaId);
                    targetUrl += '?jurusan=' + encodeURIComponent(programStudiString);
                    openStream(targetUrl, () => {
                        toggleLoading(btnImport, false, 'Import ke Undergraduate');
                    }, NL + '[ERROR] Gangguan pada proses stream database undergraduate. Cek route undergraduate-scrap.importData atau log Laravel.');
                });
            }
        }" style="background-color: #0a0a0a; border-radius: 0.75rem; border: 1px solid #262626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; height: 450px; overflow: hidden; margin-top: 1.5rem;">
            <div style="background-color: #171717; padding: 0.75rem 1rem; border-bottom: 1px solid #262626; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; letter-spacing: 0.05em;">Terminal Real-time Undergraduate Sync Output</span>
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
                Grid::make(3)
                    ->schema([
                        Section::make('Langkah 1: Perbarui Daftar')
                            ->icon('heroicon-o-arrow-path')
                            ->schema([
                                Placeholder::make('status_excel')
                                    ->label('Status Data Dosen')
                                    ->content(new HtmlString($statusHtml)),
                                Placeholder::make('button_perbarui_dosen')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($scrapeButtonHtml)),
                            ])
                            ->columnSpan(1),

                        Section::make('Langkah 2: Ambil Detail')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->schema([
                                Select::make('sinta_id')
                                    ->label('Pilih Dosen SINTA')
                                    ->options($this->getSintaLecturerOptions())
                                    ->getSearchResultsUsing(fn (string $search): array => $this->getSintaLecturerOptions($search))
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        $lecturer = SintaLecturer::where('sinta_id', $value)->first();

                                        return $lecturer
                                            ? trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')')
                                            : null;
                                    })
                                    ->searchable()
                                    ->placeholder('-- Silakan Pilih Dosen --')
                                    ->required()
                                    ->suffixAction(
                                        Action::make('tambahDosenManual')
                                            ->label('Tambah Manual')
                                            ->icon('heroicon-m-plus')
                                            ->modalHeading('Form Tambah Data Dosen')
                                            ->modalWidth('md')
                                            ->form([
                                                TextInput::make('sinta_id')
                                                    ->label('SINTA ID')
                                                    ->placeholder('Contoh: 6954305')
                                                    ->required()
                                                    ->numeric(),
                                                TextInput::make('nama')
                                                    ->label('Nama Lengkap')
                                                    ->placeholder('Nama Lengkap Beserta Gelar')
                                                    ->required(),
                                            ])
                                            ->action(function (array $data) {
                                                if (SintaLecturer::where('sinta_id', $data['sinta_id'])->exists()) {
                                                    Notification::make()
                                                        ->title('Gagal Menyimpan')
                                                        ->body('SINTA ID ini sudah terdaftar di database.')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                SintaLecturer::create([
                                                    'sinta_id' => $data['sinta_id'],
                                                    'name' => $data['nama'],
                                                ]);

                                                Notification::make()
                                                    ->title('Sukses')
                                                    ->body('Dosen berhasil ditambahkan ke database.')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                                Placeholder::make('button_ambil_detail')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($extractButtonHtml)),
                            ])
                            ->columnSpan(1),

                        Section::make('Langkah 3: Database Undergraduate')
                            ->icon('heroicon-o-server')
                            ->description('Migrasikan seluruh data SINTA dosen dan daftarkan dosen ke tabel undergraduate.')
                            ->schema([
                                Placeholder::make('button_sync_program_studi')
                                    ->hiddenLabel()
                                    ->content(new HtmlString($syncStudyProgramButtonHtml)),
                                Select::make('program_studi')
                                    ->label('Pilih Program Studi Undergraduate')
                                    ->options($programStudis)
                                    ->searchable()
                                    ->multiple()
                                    ->placeholder('-- Silakan Pilih Program Studi Undergraduate --')
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
