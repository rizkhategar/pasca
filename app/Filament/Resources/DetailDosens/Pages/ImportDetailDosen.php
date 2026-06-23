<?php

namespace App\Filament\Resources\DetailDosens\Pages;

use App\Filament\Resources\DetailDosens\DetailDosenResource;
use Filament\Resources\Pages\Page;

// --- STRUKTUR INTI COMPONENT & SCHEMA FILAMENT V5 ---
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;

// --- INPUT FIELD FORM ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;

// --- UTILITY LARAVEL & NOTIFIKASI ---
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\SintaLecturer;
use Illuminate\Support\HtmlString;

class ImportDetailDosen extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = DetailDosenResource::class;

    // Mengaktifkan kembali kompas View ke file Blade formalitas kita
    protected string $view = 'filament.resources.detail-dosens.pages.import-detail-dosen';

    protected static ?string $title = 'Import & Scraping SINTA';

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

        $jurusans = Cache::remember('academic_programs_select_import', now()->addHours(12), function () {
            $response = Http::withoutVerifying()->get('https://panel-web.unw.ac.id/api/unw-program-studi');
            if (!$response->successful()) return [];

            return collect($response->json('data', []))
                ->filter(fn($item) => isset($item['id'], $item['nama'], $item['unwFakultas']['nama']) && trim($item['unwFakultas']['nama']) === 'Pascasarjana')
                ->mapWithKeys(fn($item) => [
                    $item['id'] => trim(($item['jenjang'] ?? '') . ' ' . ($item['nama'] ?? ''))
                ])
                ->sortBy(fn($value) => $value)
                ->toArray();
        });

        $urlPerbarui = route('scrap.perbaruiDosen');
        $urlAmbilDetail = route('scrap.ambilDetail', ':id');
        $urlImport = route('scrap.importData', ':id');

        $buttonBaseStyle = 'width: 100%; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.5rem; padding: 0.625rem 0.875rem; font-weight: 600; color: #ffffff; border: none; cursor: pointer;';
        $scrapeButtonHtml = '<button type="button" id="btn-perbarui" style="' . $buttonBaseStyle . ' background-color: #525252;">Mulai Scraping Dosen</button>';
        $extractButtonHtml = '<button type="button" id="btn-ambil-detail" style="' . $buttonBaseStyle . ' background-color: #2563eb;">Ekstrak Data SINTA</button>';
        $importButtonHtml = '<button type="button" id="btn-import" style="' . $buttonBaseStyle . ' background-color: #16a34a;">Import ke Database</button>';

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
                    if (isLoading) {
                        button.disabled = true;
                        button.innerText = '⏳ Memproses...';
                        button.style.opacity = '0.5';
                    } else {
                        button.disabled = false;
                        button.innerText = originalText;
                        button.style.opacity = '1';
                    }
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

                    return eventSource;
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

                    let targetUrl = '{$urlAmbilDetail}'.replace(':id', sintaId);
                    openStream(targetUrl, () => {
                        appendTerminal(NL + '[SUKSES] Seluruh modul & file gabungan berhasil dibuat.' + NL);
                        toggleLoading(btnAmbilDetail, false, 'Ekstrak Data SINTA');
                    }, NL + '[ERROR] Ekstraksi terputus. Cek route scrap.ambilDetail atau log Laravel.');
                });

                document.addEventListener('click', (event) => {
                    const btnImport = event.target.closest('#btn-import');
                    if (!btnImport) return;

                    event.preventDefault();
                    const sintaId = livewire.get('data.sinta_id');
                    const jurusan = livewire.get('data.jurusan');

                    if (!sintaId) return alert('SINTA ID tidak ditemukan. Pilih dosen dulu pada langkah 2.');

                    if (!jurusan || (Array.isArray(jurusan) && jurusan.length === 0)) {
                        return alert('Silakan pilih sekurang-kurangnya satu Jurusan!');
                    }

                    /* PERBAIKAN PIVOT: Mengubah join(', ') menjadi join(',') murni tanpa spasi
                       agar string ID terkirim padat (misal '21,22') untuk diexplode langsung ke tabel pivot */
                    let jurusanString = Array.isArray(jurusan) ? jurusan.join(',') : jurusan;

                    resetTerminal('>>> Memulai migrasi streaming data Excel ke MySQL untuk SINTA ID: ' + sintaId + ' (ID Pivot Departemen: ' + jurusanString + ')...' + NL);
                    toggleLoading(btnImport, true, 'Import ke Database');

                    let targetUrl = '{$urlImport}'.replace(':id', sintaId);
                    targetUrl += '?jurusan=' + encodeURIComponent(jurusanString);

                    openStream(targetUrl, () => {
                        toggleLoading(btnImport, false, 'Import ke Database');
                    }, NL + '[ERROR] Gangguan pada proses stream database. Cek route scrap.importData atau log Laravel.');
                });
            }
        }" style="background-color: #0a0a0a; border-radius: 0.75rem; border: 1px solid #262626; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; height: 450px; overflow: hidden; margin-top: 1.5rem;">
            <div style="background-color: #171717; padding: 0.75rem 1rem; border-bottom: 1px solid #262626; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="display: flex; gap: 0.375rem;">
                        <div style="width: 0.75rem; height: 0.75rem; border-radius: 9999px; background-color: #ef4444;"></div>
                        <div style="width: 0.75rem; height: 0.75rem; border-radius: 9999px; background-color: #eab308;"></div>
                        <div style="width: 0.75rem; height: 0.75rem; border-radius: 9999px; background-color: #22c55e;"></div>
                    </div>
                    <span style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; letter-spacing: 0.05em;">Terminal Real-time Sync Output</span>
                </div>
                <button type="button" onclick="document.getElementById('output-box').innerHTML='Menunggu perintah...' + String.fromCharCode(10)" style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; background: none; border: none; cursor: pointer;">
                    Clear Log
                </button>
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
                                    ->options(
                                        SintaLecturer::query()
                                            ->orderBy('name', 'asc')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn(SintaLecturer $lecturer) => [
                                                $lecturer->sinta_id => trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')')
                                            ])
                                            ->toArray()
                                    )
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return SintaLecturer::query()
                                            ->where('name', 'like', "%{$search}%")
                                            ->orWhere('sinta_id', 'like', "%{$search}%")
                                            ->orderBy('name', 'asc')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(fn(SintaLecturer $lecturer) => [
                                                $lecturer->sinta_id => trim(($lecturer->name ?? '-') . ' (' . $lecturer->sinta_id . ')')
                                            ])
                                            ->toArray();
                                    })
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
                                                $exists = SintaLecturer::where('sinta_id', $data['sinta_id'])->exists();
                                                if ($exists) {
                                                    Notification::make()
                                                        ->title('Gagal Menyimpan')
                                                        ->body('SINTA ID ini sudah terdaftar di database.')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                SintaLecturer::create([
                                                    'sinta_id' => $data['sinta_id'],
                                                    'name'     => $data['nama'],
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

                        Section::make('Langkah 3: Database')
                            ->icon('heroicon-o-server')
                            ->description('Migrasikan seluruh data kualifikasi dan publikasi dosen dari dokumen Excel ke dalam MySQL.')
                            ->schema([
                                Select::make('jurusan')
                                    ->label('Pilih Jurusan')
                                    ->options($jurusans)
                                    ->searchable()
                                    ->multiple()
                                    ->placeholder('-- Silakan Pilih Jurusan --')
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
}
