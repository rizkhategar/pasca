<?php

namespace App\Http\Controllers;

use App\Models\PostgraduateLecturer;
use App\Models\PostgraduateLecturerStudyProgram;
use App\Models\SintaBookPublication;
use App\Models\SintaGarudaPublication;
use App\Models\SintaGarudaYearlyStat;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerDetail;
use App\Models\SintaResearch;
use App\Models\SintaResearchYearly;
use App\Models\SintaScholarPublication;
use App\Models\SintaScholarYearlyStat;
use App\Models\SintaScopusPublication;
use App\Models\SintaScopusYearlyStat;
use App\Models\SintaService;
use App\Models\SintaServiceYearly;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScrapController extends Controller
{
    private string $photoStorageDirectory = 'sinta-lecturers';

    private function stream(array $payload): void
    {
        echo 'data: ' . json_encode($payload) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    private function normalizeRow(array|object $row): array
    {
        return array_change_key_case((array) $row, CASE_LOWER);
    }

    private function cleanSintaId(?string $value): ?string
    {
        $cleaned = preg_replace('/[^0-9]/', '', (string) $value);

        return $cleaned !== '' ? $cleaned : null;
    }

    private function deleteImportedExcelFile(string $filePath): string
    {
        $fileName = basename($filePath);

        if (! file_exists($filePath)) {
            return "<span class='text-gray-400'>[CLEANUP]</span> File {$fileName} sudah tidak ada di scripts/output.\n";
        }

        if (@unlink($filePath)) {
            return "<span class='text-success-400'>[CLEANUP]</span> File {$fileName} berhasil dihapus dari scripts/output.\n";
        }

        Log::warning("Gagal menghapus file import Excel: {$filePath}");

        return "<span class='text-warning-500'>[CLEANUP - WARN]</span> File {$fileName} sudah berhasil diimport, tetapi gagal dihapus. Cek permission file/folder scripts/output.\n";
    }

    private function downloadLecturerPhotoToStorage(string $photoUrl, string $sintaId): ?string
    {
        $response = Http::withoutVerifying()->timeout(15)->get($photoUrl);

        if (! $response->successful()) {
            $this->stream(['output' => "<span class='text-warning-500'>[FOTO - WARN] Gagal mengunduh foto (Status HTTP: " . $response->status() . "). Tetap menggunakan URL asli.</span>\n"]);
            return null;
        }

        $photoName = $sintaId . '.jpg';
        $photoPath = $this->photoStorageDirectory . '/' . $photoName;

        Storage::disk('public')->makeDirectory($this->photoStorageDirectory);
        Storage::disk('public')->put($photoPath, $response->body());

        $this->stream(['output' => "<span class='text-success-400'>[FOTO]</span> ✔ Foto profil berhasil disimpan ke direktori: storage/app/public/{$photoPath}\n"]);

        return $photoPath;
    }

    public function index()
    {
        try {
            $response = Http::get('http://127.0.0.1:8000/api/baca-dosen');
            $dosenList = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $dosenList = [];
            Log::error('Gagal mengambil data dosen dari API Python: ' . $e->getMessage());
        }

        return view('filament.resources.detail-dosens.pages.import-detail-dosen', compact('dosenList'));
    }

    public function perbaruiDosen(): StreamedResponse
    {
        return new StreamedResponse(function () {
            set_time_limit(0);
            ignore_user_abort(true);

            $baseUrl = env('PYTHON_SCRAPER_URL', 'http://127.0.0.1:8000');
            $streamUrl = $baseUrl . '/api/scrape-dosen';
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $streamUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 256);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
                foreach (explode("\n", $chunk) as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }

                    $lineText = str_starts_with($line, 'data: ') ? substr($line, 6) : $line;
                    $this->stream(['output' => mb_convert_encoding($lineText, 'UTF-8', 'UTF-8') . "\n"]);
                }

                return strlen($chunk);
            });

            $success = curl_exec($ch);

            if (! $success) {
                $this->stream(['output' => "<span class='text-danger-500 font-bold'>[ERROR]</span> Gagal terhubung ke Docker Python Scraper. URL: {$streamUrl}. Error: " . curl_error($ch) . "\n"]);
            }

            curl_close($ch);

            if ($success) {
                $this->downloadAndImportMasterLecturers($baseUrl);
            }

            $this->stream(['done' => true]);
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function downloadAndImportMasterLecturers(string $baseUrl): void
    {
        $downloadUrl = $baseUrl . '/api/download-excel-dosen';
        $this->stream(['output' => "\n[LARAVEL] Menghubungi API Docker untuk mengunduh berkas Excel master...\n"]);
        $fileResponse = Http::get($downloadUrl);

        if (! $fileResponse->successful() || isset($fileResponse->json()['error'])) {
            $this->stream(['output' => "\n<span class='text-warning-500'>[WARN] File excel dosen gagal diunduh atau belum tercipta di container Docker.</span>\n----------------------------------------\n"]);
            return;
        }

        $excelPath = base_path('scripts/output/dosen_universitas_ngudi_waluyo.xlsx');

        if (! file_exists(dirname($excelPath))) {
            mkdir(dirname($excelPath), 0777, true);
        }

        file_put_contents($excelPath, $fileResponse->body());
        $this->stream(['output' => "<span class='text-success-400 font-bold'>[OK]</span> Berkas Excel berhasil diunduh. Memulai migrasi data ke tabel sinta_lecturers...\n"]);

        try {
            $rows = (new FastExcel())->import($excelPath);
            $insertedCount = 0;
            DB::beginTransaction();

            foreach ($rows as $row) {
                $r = $this->normalizeRow($row);
                $sintaId = $this->cleanSintaId($r['sinta id'] ?? null);

                if (! $sintaId || (empty($r['nama']) && empty($r['name']))) {
                    continue;
                }

                SintaLecturer::updateOrCreate(
                    ['sinta_id' => $sintaId],
                    [
                        'name' => $r['nama'] ?? $r['name'],
                        'department' => $r['departemen'] ?? $r['department'] ?? null,
                        'scopus_h_index' => $r['scopus h-index'] ?? null,
                        'google_scholar_h_index' => $r['google scholar h-index'] ?? null,
                        'sinta_score_3yr' => isset($r['sinta score 3yr']) ? (int) str_replace('.', '', $r['sinta score 3yr']) : null,
                        'sinta_score' => isset($r['sinta score']) ? (int) str_replace('.', '', $r['sinta score']) : null,
                        'affiliation_score_3yr' => isset($r['affiliation score 3yr']) ? (int) str_replace('.', '', $r['affiliation score 3yr']) : null,
                        'affiliation_score' => isset($r['affiliation score']) ? (int) str_replace('.', '', $r['affiliation score']) : null,
                        'profile_url' => $r['profile url'] ?? $r['profile_url'] ?? null,
                    ]
                );

                $insertedCount++;
            }

            DB::commit();
            $this->stream(['output' => "<span class='text-success-400 font-bold'>[SUKSES] Auto-Import Selesai!</span> Berhasil memperbarui {$insertedCount} dosen ke tabel database sinta_lecturers.\n"]);
            $this->stream(['output' => $this->deleteImportedExcelFile($excelPath) . "----------------------------------------\n"]);
        } catch (\Throwable $importError) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $this->stream(['output' => "\n<span class='text-danger-500 font-bold'>[DATABASE ERROR]</span> Gagal menyimpan data: " . addslashes($importError->getMessage()) . "\n----------------------------------------\n"]);
        }
    }

    public function tambahDosenManual(Request $request)
    {
        $request->validate([
            'sinta_id' => 'required|unique:sinta_lecturers,sinta_id',
            'nama' => 'required|string|max:255',
        ]);

        SintaLecturer::create([
            'sinta_id' => $this->cleanSintaId($request->sinta_id),
            'name' => $request->nama,
            'department' => 'Manual Registration',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dosen baru berhasil didaftarkan ke dalam database master!',
        ]);
    }

    public function syncStudyPrograms(): StreamedResponse
    {
        return new StreamedResponse(function () {
            set_time_limit(0);
            ignore_user_abort(true);
            $this->stream(['output' => "Menghubungi API UNW program studi...\n"]);

            try {
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->get('https://panel-web.unw.ac.id/api/unw-program-studi');

                if (! $response->successful()) {
                    $this->stream(['output' => "<span class='text-danger-500'>[ERROR]</span> API program studi gagal diakses. Status HTTP: " . $response->status() . "\n"]);
                    $this->stream(['done' => true]);
                    return;
                }

                $items = $response->json('data', []);
                $syncedCount = 0;
                DB::beginTransaction();

                foreach ($items as $item) {
                    $apiId = data_get($item, 'id');
                    if (! $apiId) {
                        continue;
                    }

                    StudyProgram::updateOrCreate(
                        ['id_unw_program_studi' => (int) $apiId],
                        [
                            'nama' => data_get($item, 'nama'),
                            'slug' => data_get($item, 'slug'),
                            'page_slug' => data_get($item, 'page_slug'),
                            'jenjang' => data_get($item, 'jenjang'),
                            'jenjang_nama_singkat' => data_get($item, 'jenjang_nama_singkat'),
                            'unw_fakultas_id' => data_get($item, 'unwFakultas.id'),
                            'unw_fakultas_nama' => trim((string) data_get($item, 'unwFakultas.nama')),
                            'unw_fakultas_page_slug' => data_get($item, 'unwFakultas.page_slug'),
                            'api_created_at' => data_get($item, 'createdAt'),
                            'api_updated_at' => data_get($item, 'updatedAt'),
                            'created' => data_get($item, 'created'),
                            'updated' => data_get($item, 'updated'),
                        ]
                    );

                    $syncedCount++;
                }

                DB::commit();
                Cache::forget('study_programs_select_import');
                Cache::forget('academic_programs_nav');
                $this->stream(['output' => "<span class='text-success-400'>[OK]</span> Berhasil menyinkronkan {$syncedCount} program studi ke tabel study_programs.\n"]);
            } catch (\Throwable $e) {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }

                Log::error('Gagal sinkronisasi program studi: ' . $e->getMessage());
                $this->stream(['output' => "<span class='text-danger-500 font-bold'>[ERROR]</span> " . addslashes($e->getMessage()) . "\n"]);
            }

            $this->stream(['done' => true]);
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function ambilDetail($sinta_id): StreamedResponse
    {
        $sintaId = $this->cleanSintaId($sinta_id);

        return new StreamedResponse(function () use ($sintaId) {
            set_time_limit(0);
            ignore_user_abort(true);
            $baseUrl = env('PYTHON_SCRAPER_URL', 'http://127.0.0.1:8000');
            $streamUrl = $baseUrl . "/api/scrape-detail/{$sintaId}";
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $streamUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 256);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
                foreach (explode("\n", $chunk) as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }

                    $lineText = str_starts_with($line, 'data: ') ? substr($line, 6) : $line;
                    $this->stream(['output' => mb_convert_encoding($lineText, 'UTF-8', 'UTF-8') . "\n"]);
                }

                return strlen($chunk);
            });

            $success = curl_exec($ch);

            if (! $success) {
                $this->stream(['output' => "<span class='text-danger-500 font-bold'>[ERROR]</span> Gagal terhubung ke Docker Python Scraper. URL: {$streamUrl}. Error: " . curl_error($ch) . "\n"]);
            }

            curl_close($ch);

            if ($success) {
                $this->downloadMergedDetailExcel($baseUrl, $sintaId);
            }

            $this->stream(['done' => true]);
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function downloadMergedDetailExcel(string $baseUrl, string $sintaId): void
    {
        $downloadUrl = $baseUrl . "/api/download-excel-detail/{$sintaId}";
        $this->stream(['output' => "\n[LARAVEL] Menghubungi API Docker untuk menarik file excel gabungan (merged_data)...\n"]);
        $fileResponse = Http::get($downloadUrl);

        if (! $fileResponse->successful() || isset($fileResponse->json()['error'])) {
            $this->stream(['output' => "\n<span class='text-danger-500'>[ERROR]</span> Berkas merged_data_{$sintaId}.xlsx tidak ditemukan/gagal diunduh dari API Docker.\n----------------------------------------\n"]);
            return;
        }

        $excelPath = base_path("scripts/output/merged_data_{$sintaId}.xlsx");

        if (! file_exists(dirname($excelPath))) {
            mkdir(dirname($excelPath), 0777, true);
        }

        file_put_contents($excelPath, $fileResponse->body());
        $this->stream(['output' => "<span class='text-success-400 font-bold'>[OK]</span> Berkas merged_data_{$sintaId}.xlsx sukses diunduh ke laptop. Memulai sinkronisasi Database...\n"]);
    }

    public function importData(Request $request, $sinta_id): StreamedResponse
    {
        $studyProgramIds = $request->query('jurusan', $request->query('program_studi'));
        $sintaId = $this->cleanSintaId($sinta_id);

        return new StreamedResponse(function () use ($sintaId, $studyProgramIds) {
            set_time_limit(0);
            ignore_user_abort(true);
            $filePath = base_path("scripts/output/merged_data_{$sintaId}.xlsx");

            if (! file_exists($filePath)) {
                $this->stream(['output' => "<span class='text-danger-500'>[ERROR] File Excel tidak ditemukan.</span>\n"]);
                $this->stream(['done' => true]);
                return;
            }

            try {
                $this->stream(['output' => "Membaca file Excel: merged_data_{$sintaId}.xlsx...\n"]);
                $sheets = (new FastExcel())->importSheets($filePath);
                $expectedSheets = [
                    0 => 'DATA_DOSEN',
                    1 => 'SCOPUS_PUBLICATIONS',
                    2 => 'SCOPUS_YEARLY_STATS',
                    3 => 'SCHOLAR_PUBLICATIONS',
                    4 => 'SCHOLAR_YEARLY_STATS',
                    5 => 'GARUDA_PUBLICATIONS',
                    6 => 'GARUDA_YEARLY_STATS',
                    7 => 'BOOKS',
                    8 => 'SERVICES',
                    9 => 'SERVICE_YEARLY',
                    10 => 'RESEARCHES',
                    11 => 'RESEARCH_YEARLY',
                ];

                foreach ($sheets as $sheetIndex => $rows) {
                    $sheetNameUpper = strtoupper($expectedSheets[$sheetIndex] ?? "SHEET_{$sheetIndex}");
                    $this->stream(['output' => "----------------------------------------\n"]);
                    $this->stream(['output' => "Memproses Sheet: <span class='text-primary-400 font-bold'>{$sheetNameUpper}</span>...\n"]);

                    if (empty($rows) || count($rows) === 0 || ! collect($rows)->first()) {
                        $this->stream(['output' => "<span class='text-gray-400'>--> Sheet kosong, dilewati.</span>\n"]);
                        continue;
                    }

                    $firstRow = collect($rows)->first();
                    $values = array_map('strtolower', array_map('trim', array_values((array) $firstRow)));

                    if (in_array('none', $values, true)) {
                        $this->stream(['output' => "<span class='text-gray-400'>--> Sheet berisi 'none', dilewati.</span>\n"]);
                        continue;
                    }

                    DB::beginTransaction();
                    $insertedCount = 0;

                    foreach ($rows as $row) {
                        $r = $this->normalizeRow($row);

                        if ($sheetNameUpper === 'DATA_DOSEN') {
                            if ($this->importLecturerDataRow($r, $sintaId, $studyProgramIds)) {
                                $insertedCount++;
                            }
                        } elseif ($sheetNameUpper === 'SCOPUS_PUBLICATIONS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaScopusPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'year' => $r['tahun'] ?? $r['year'] ?? null,
                                'citation' => isset($r['citation']) ? (int) $r['citation'] : (isset($r['sitasi']) ? (int) $r['sitasi'] : 0),
                                'quartile' => $r['quartile'] ?? null,
                                'journal' => $r['journal'] ?? $r['jurnal'] ?? null,
                                'author_order' => $r['author order'] ?? $r['author_order'] ?? null,
                                'creator' => $r['creator'] ?? null,
                                'article_url' => $r['url artikel'] ?? $r['url_artikel'] ?? $r['article_url'] ?? null,
                                'journal_url' => $r['url journal'] ?? $r['url_journal'] ?? $r['journal_url'] ?? null,
                            ]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'SCOPUS_YEARLY_STATS') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaScopusYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], ['count' => isset($r['jumlah']) ? (int) $r['jumlah'] : (isset($r['count']) ? (int) $r['count'] : 0)]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'SCHOLAR_PUBLICATIONS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaScholarPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'scholar_url' => $r['url scholar'] ?? $r['url_scholar'] ?? $r['scholar_url'] ?? null,
                                'authors' => $r['authors'] ?? $r['penulis'] ?? null,
                                'source' => $r['source'] ?? $r['sumber'] ?? null,
                                'year' => $r['tahun'] ?? $r['year'] ?? null,
                                'citation' => isset($r['citation']) ? (int) $r['citation'] : (isset($r['sitasi']) ? (int) $r['sitasi'] : 0),
                            ]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'SCHOLAR_YEARLY_STATS') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaScholarYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], ['publications' => isset($r['publications']) ? (int) $r['publications'] : 0, 'citations' => isset($r['citations']) ? (int) $r['citations'] : 0]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'GARUDA_PUBLICATIONS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaGarudaPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'article_url' => $r['url_artikel'] ?? $r['url artikel'] ?? $r['article_url'] ?? null,
                                'publisher' => $r['publisher'] ?? $r['penerbit'] ?? null,
                                'journal' => $r['journal'] ?? $r['jurnal'] ?? null,
                                'journal_url' => $r['url_journal'] ?? $r['url journal'] ?? $r['journal_url'] ?? null,
                                'author_order' => $r['author_order'] ?? $r['author order'] ?? null,
                                'authors' => $r['authors'] ?? $r['penulis'] ?? null,
                                'year' => $r['tahun'] ?? $r['year'] ?? null,
                                'doi' => $r['doi'] ?? null,
                                'accreditation' => $r['accreditation'] ?? $r['akreditasi'] ?? null,
                            ]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'GARUDA_YEARLY_STATS') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaGarudaYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], ['articles' => isset($r['articles']) ? (int) $r['articles'] : (isset($r['jumlah']) ? (int) $r['jumlah'] : 0)]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'BOOKS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaBookPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], ['category' => $r['kategori'] ?? $r['category'] ?? null, 'authors' => $r['penulis'] ?? $r['authors'] ?? null, 'publisher' => $r['penerbit'] ?? $r['publisher'] ?? null, 'year' => $r['tahun'] ?? $r['year'] ?? null, 'city' => $r['kota'] ?? $r['city'] ?? null, 'isbn' => $r['isbn'] ?? null]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'RESEARCHES') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaResearch::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], ['leader' => $r['leader'] ?? null, 'scheme' => $r['skema'] ?? $r['scheme'] ?? null, 'personnel' => $r['personils'] ?? $r['personnel'] ?? null, 'year' => $r['tahun'] ?? $r['year'] ?? null, 'funding' => $r['dana'] ?? $r['funding'] ?? null, 'status' => $r['status'] ?? null, 'source' => $r['source'] ?? null]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'RESEARCH_YEARLY') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaResearchYearly::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], ['count' => isset($r['jumlah']) ? (int) $r['jumlah'] : (isset($r['count']) ? (int) $r['count'] : 0)]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'SERVICES') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaService::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], ['leader' => $r['leader'] ?? null, 'scheme' => $r['skema'] ?? $r['scheme'] ?? null, 'personnel' => $r['personils'] ?? $r['personnel'] ?? null, 'year' => $r['tahun'] ?? $r['year'] ?? null, 'funding' => $r['dana'] ?? $r['funding'] ?? null, 'status' => $r['status'] ?? null, 'source' => $r['source'] ?? null]);
                            $insertedCount++;
                        } elseif ($sheetNameUpper === 'SERVICE_YEARLY') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaServiceYearly::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], ['count' => isset($r['jumlah']) ? (int) $r['jumlah'] : (isset($r['count']) ? (int) $r['count'] : 0)]);
                            $insertedCount++;
                        }
                    }

                    DB::commit();
                    $this->stream(['output' => $insertedCount > 0 ? "<span class='text-success-400'>[OK] Berhasil menyimpan {$insertedCount} baris ke database.</span>\n" : "<span class='text-gray-400'>--> Tidak ada data valid yang diproses.</span>\n"]);
                }

                $this->stream(['output' => "----------------------------------------\n<span class='text-success-400 font-bold'>[SUKSES IMPORT]</span> Seluruh sheet selesai diimpor!\n"]);
                $this->stream(['output' => $this->deleteImportedExcelFile($filePath)]);
                $this->stream(['done' => true]);
            } catch (\Throwable $e) {
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }

                $this->stream(['output' => "\n<span class='text-danger-500 font-bold'>[ERROR FATAL]</span> " . addslashes($e->getMessage()) . " (Baris: {$e->getLine()})\n"]);
                $this->stream(['done' => true]);
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function importLecturerDataRow(array $r, string $sintaId, ?string $studyProgramIds): bool
    {
        $photoValue = $r['profile photo'] ?? $r['profile_photo'] ?? null;

        if (! empty($photoValue) && filter_var($photoValue, FILTER_VALIDATE_URL)) {
            try {
                $this->stream(['output' => "<span style='color: #0ea5e9;'>[FOTO]</span> URL foto ditemukan: {$photoValue}\n"]);
                $storedPhotoPath = $this->downloadLecturerPhotoToStorage($photoValue, $sintaId);
                if ($storedPhotoPath) {
                    $photoValue = $storedPhotoPath;
                }
            } catch (\Throwable $photoError) {
                $this->stream(['output' => "<span class='text-warning-500'>[FOTO - ERROR] Request Timeout / Gagal: " . addslashes($photoError->getMessage()) . "</span>\n"]);
            }
        } elseif (is_string($photoValue) && trim($photoValue) !== '') {
            $photoValue = trim(str_replace('\\', '/', $photoValue), '/');
            if (! str_contains($photoValue, '/')) {
                $photoValue = $this->photoStorageDirectory . '/' . basename($photoValue);
            }
        }

        SintaLecturerDetail::updateOrCreate(['sinta_id' => $sintaId], [
            'institution' => $r['institusi'] ?? $r['institution'] ?? $r['afiliasi'] ?? null,
            'study_program' => $r['program studi'] ?? $r['program_studi'] ?? $r['study_program'] ?? null,
            'profile_photo' => $photoValue,
            'research_interests' => $r['bidang minat'] ?? $r['bidang_minat'] ?? $r['research_interests'] ?? null,
            'sinta_score_overall' => isset($r['sinta score overall']) ? (int) str_replace('.', '', $r['sinta score overall']) : 0,
            'sinta_score_3yr' => isset($r['sinta score 3yr']) ? (int) str_replace('.', '', $r['sinta score 3yr']) : 0,
            'affil_score' => isset($r['affil score']) ? (int) str_replace('.', '', $r['affil score']) : 0,
            'affil_score_3yr' => isset($r['affil score 3yr']) ? (int) str_replace('.', '', $r['affil score 3yr']) : 0,
        ]);

        $postgraduateLecturer = PostgraduateLecturer::firstOrCreate(
            ['sinta_id' => $sintaId],
            [
                'name' => $r['nama'] ?? $r['name'] ?? null,
                'institution' => $r['institusi'] ?? $r['institution'] ?? $r['afiliasi'] ?? null,
                'profile_photo' => $photoValue,
            ]
        );

        if (! empty($studyProgramIds)) {
            $selectedStudyProgramIds = collect(explode(',', $studyProgramIds))
                ->map(fn ($studyProgramId) => trim((string) $studyProgramId))
                ->filter()
                ->unique()
                ->values();

            $validStudyProgramIds = StudyProgram::query()
                ->whereIn('id', $selectedStudyProgramIds)
                ->orWhereIn('id_unw_program_studi', $selectedStudyProgramIds)
                ->pluck('id')
                ->map(fn ($studyProgramId) => (int) $studyProgramId)
                ->unique()
                ->values()
                ->toArray();

            PostgraduateLecturerStudyProgram::where('postgraduate_lecturer_id', $postgraduateLecturer->id)->delete();

            foreach ($validStudyProgramIds as $studyProgramId) {
                PostgraduateLecturerStudyProgram::create([
                    'postgraduate_lecturer_id' => $postgraduateLecturer->id,
                    'study_program_id' => $studyProgramId,
                ]);
            }
        }

        return true;
    }
}
