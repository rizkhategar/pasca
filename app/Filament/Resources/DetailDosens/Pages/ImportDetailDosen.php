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
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;

// --- INPUT FIELD FORM ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;

// --- UTILITY LARAVEL & NOTIFIKASI ---
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\DaftarDosen;
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
        $excelExists = file_exists(base_path('scripts/output/dosen_universitas_ngudi_waluyo.xlsx'));
        $statusHtml = $excelExists
            ? '<div style="padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #059669; font-weight: 500;">✅ <b>File tersedia.</b> Silakan lanjut ke Langkah 2.</div>'
            : '<div style="padding: 0.75rem; border-radius: 0.5rem; background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #dc2626; font-weight: 500;">⚠️ <b>Belum ada data.</b> Jalankan scraping.</div>';

        $jurusans = Cache::remember('academic_programs_select_import', now()->addHours(12), function () {
            $response = Http::withoutVerifying()->get('https://panel-web.unw.ac.id/api/unw-program-studi');
            if (!$response->successful()) return [];

            return collect($response->json('data', []))
                ->filter(fn($item) => isset($item['slug'], $item['nama'], $item['unwFakultas']['nama']) && trim($item['unwFakultas']['nama']) === 'Pascasarjana')
                ->mapWithKeys(fn($item) => [
                    $item['slug'] => trim(($item['jenjang'] ?? '') . ' ' . ($item['nama'] ?? ''))
                ])
                ->sortBy(fn($value) => $value)
                ->toArray();
        });

        $urlPerbarui = route('scrap.perbaruiDosen');
        $urlAmbilDetail = route('scrap.ambilDetail', ':id');
        $urlImport = route('scrap.importData', ':id');

        $terminalHtml = <<<HTML
        <div wire:ignore x-data="{
            init() {
                const outputBox = document.getElementById('output-box');
                const terminalContainer = document.getElementById('terminal-container');
                const btnPerbarui = document.getElementById('btn-perbarui');
                const btnAmbilDetail = document.getElementById('btn-ambil-detail');
                const btnImport = document.getElementById('btn-import');

                const appendTerminal = (text) => {
                    outputBox.innerHTML += text;
                    terminalContainer.scrollTop = terminalContainer.scrollHeight;
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

                if (btnPerbarui) {
                    btnPerbarui.addEventListener('click', () => {
                        outputBox.innerHTML = '>>> Memulai pembaruan data master dosen (dosen.py)....\\n';
                        toggleLoading(btnPerbarui, true, 'Mulai Scraping Dosen');

                        const eventSource = new EventSource('{$urlPerbarui}');
                        eventSource.onmessage = (event) => {
                            const data = JSON.parse(event.data);
                            if (data.output) appendTerminal(data.output);
                            if (data.done) {
                                eventSource.close();
                                appendTerminal('\\n[SUKSES] Daftar dosen berhasil diperbarui. Memuat ulang...\\n');
                                setTimeout(() => { window.location.reload(); }, 2000);
                            }
                        };
                        eventSource.onerror = () => {
                            eventSource.close();
                            appendTerminal('\\n[ERROR] Koneksi diputus server.\\n');
                            toggleLoading(btnPerbarui, false, 'Mulai Scraping Dosen');
                        };
                    });
                }

                if (btnAmbilDetail) {
                    btnAmbilDetail.addEventListener('click', () => {
                        const sintaId = this.\$wire.get('data.sinta_id');
                        if (!sintaId) return alert('Silakan pilih dosen terlebih dahulu!');

                        outputBox.innerHTML = '>>> Mengekstrak detail modul SINTA untuk ID: ' + sintaId + '...\\n\\n';
                        toggleLoading(btnAmbilDetail, true, 'Ekstrak Data SINTA');

                        let targetUrl = '{$urlAmbilDetail}'.replace(':id', sintaId);
                        const eventSource = new EventSource(targetUrl);

                        eventSource.onmessage = (event) => {
                            const data = JSON.parse(event.data);
                            if (data.output) appendTerminal(data.output);
                            if (data.done) {
                                eventSource.close();
                                appendTerminal('\\n[SUKSES] Seluruh modul & file gabungan berhasil dibuat.\\n');
                                toggleLoading(btnAmbilDetail, false, 'Ekstrak Data SINTA');
                            }
                        };
                        eventSource.onerror = () => {
                            eventSource.close();
                            appendTerminal('\\n[ERROR] Ekstraksi terputus.\\n');
                            toggleLoading(btnAmbilDetail, false, 'Ekstrak Data SINTA');
                        };
                    });
                }

                if (btnImport) {
                    btnImport.addEventListener('click', () => {
                        const sintaId = this.\$wire.get('data.sinta_id');
                        const jurusan = this.\$wire.get('data.jurusan');

                        if (!sintaId) return alert('SINTA ID tidak ditemukan. Pilih dosen dulu pada langkah 2.');
                        if (!jurusan) return alert('Silakan pilih Jurusan terlebih dahulu!');

                        appendTerminal('\\n>>> Memulai migrasi streaming data Excel ke MySQL untuk SINTA ID: ' + sintaId + ' (Jurusan: ' + jurusan + ')...\\n');
                        toggleLoading(btnImport, true, 'Import ke Database');

                        let targetUrl = '{$urlImport}'.replace(':id', sintaId);
                        targetUrl += '?jurusan=' + encodeURIComponent(jurusan);

                        const eventSource = new EventSource(targetUrl);
                        eventSource.onmessage = (event) => {
                            const data = JSON.parse(event.data);
                            if (data.output) appendTerminal(data.output);
                            if (data.done) {
                                eventSource.close();
                                toggleLoading(btnImport, false, 'Import ke Database');
                            }
                        };
                        eventSource.onerror = () => {
                            eventSource.close();
                            appendTerminal('\\n[ERROR] Gangguan pada proses stream database.\\n');
                            toggleLoading(btnImport, false, 'Import ke Database');
                        };
                    });
                }
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
                <button type="button" onclick="document.getElementById('output-box').innerHTML='Menunggu perintah...\\n'" style="color: #a3a3a3; font-family: ui-monospace, monospace; font-size: 0.75rem; background: none; border: none; cursor: pointer;">
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
                                    ->label('')
                                    ->content(new HtmlString($statusHtml)),
                                Actions::make([
                                    Action::make('perbaruiDosen')
                                        ->label('Mulai Scraping Dosen')
                                        ->color('gray')
                                        ->extraAttributes([
                                            'id' => 'btn-perbarui',
                                            'class' => 'w-full justify-center text-center font-semibold'
                                        ]),
                                ]),
                            ])
                            ->columnSpan(1),

                        Section::make('Langkah 2: Ambil Detail')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->schema([
                                Select::make('sinta_id')
                                    ->label('Pilih Dosen SINTA')
                                    ->options(DaftarDosen::orderBy('nama', 'asc')->pluck('nama', 'sinta_id'))
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
                                                $exists = DaftarDosen::where('sinta_id', $data['sinta_id'])->exists();
                                                if ($exists) {
                                                    Notification::make()
                                                        ->title('Gagal Menyimpan')
                                                        ->body('SINTA ID ini sudah terdaftar di database.')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                DaftarDosen::create([
                                                    'sinta_id' => $data['sinta_id'],
                                                    'nama' => $data['nama'],
                                                ]);

                                                Notification::make()
                                                    ->title('Sukses')
                                                    ->body('Dosen berhasil ditambahkan ke database.')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                                Actions::make([
                                    Action::make('ambilDetail')
                                        ->label('Ekstrak Data SINTA')
                                        ->color('primary')
                                        ->extraAttributes([
                                            'id' => 'btn-ambil-detail',
                                            'class' => 'w-full justify-center text-center font-semibold'
                                        ]),
                                ]),
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
                                    ->placeholder('-- Silakan Pilih Jurusan --')
                                    ->required()
                                    ->native(false),
                                Actions::make([
                                    Action::make('importDatabase')
                                        ->label('Import ke Database')
                                        ->color('success')
                                        ->extraAttributes([
                                            'id' => 'btn-import',
                                            'class' => 'w-full justify-center text-center font-semibold'
                                        ]),
                                ]),
                            ])
                            ->columnSpan(1),
                    ]),

                Placeholder::make('terminal_sync')
                    ->label('')
                    ->content(new HtmlString($terminalHtml)),
            ])
            ->statePath('data');
    }
}