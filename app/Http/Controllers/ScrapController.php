<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Rap2hpoutre\FastExcel\FastExcel;

// Import Model-Model Baru yang Sudah Konsisten Berbahasa Inggris
use App\Models\SintaLecturer;
use App\Models\SintaLecturerDetail;
use App\Models\SintaScopusPublication;
use App\Models\SintaScopusYearlyStat;
use App\Models\SintaScholarPublication;
use App\Models\SintaScholarYearlyStat;
use App\Models\SintaGarudaPublication;
use App\Models\SintaGarudaYearlyStat;
use App\Models\SintaBookPublication;
use App\Models\SintaResearch;
use App\Models\SintaResearchYearly;
use App\Models\SintaService;
use App\Models\SintaServiceYearly;

class ScrapController extends Controller
{
    private $pythonExe = 'python';

    /**
     * Menampilkan halaman utama panel dan membaca data untuk dropdown
     */
    public function index()
    {
        try {
            $response = Http::get('http://127.0.0.1:8000/api/baca-dosen');
            $dosenList = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $dosenList = [];
            Log::error("Gagal mengambil data dosen dari API Python: " . $e->getMessage());
        }

        return view('filament.resources.detail-dosens.pages.import-detail-dosen', compact('dosenList'));
    }

    /**
     * SSE Stream: Menjalankan dosen.py (Langkah 1)
     */
    public function perbaruiDosen()
    {
        return new StreamedResponse(function () {
            set_time_limit(0);
            ignore_user_abort(true);

            $baseUrl = env('PYTHON_SCRAPER_URL', 'http://127.0.0.1:8000');
            $streamUrl = $baseUrl . '/api/scrape-dosen';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $streamUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 256);

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
                $lines = explode("\n", $chunk);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    $lineText = $line;
                    if (strpos($line, 'data: ') === 0) {
                        $lineText = substr($line, 6);
                    }

                    $cleanBuffer = mb_convert_encoding($lineText, 'UTF-8', 'UTF-8');
                    echo "data: " . json_encode(['output' => $cleanBuffer . "\n"]) . "\n\n";
                    ob_flush();
                    flush();
                }
                return strlen($chunk);
            });

            $success = curl_exec($ch);

            if (!$success) {
                $error = curl_error($ch);
                echo "data: " . json_encode(['output' => "<span class='text-danger-500 font-bold'>[ERROR]</span> Gagal terhubung ke Docker Python Scraper. URL: {$streamUrl}. Error: {$error}\n"]) . "\n\n";
                ob_flush();
                flush();
            }
            curl_close($ch);

            if ($success) {
                $downloadUrl = $baseUrl . '/api/download-excel-dosen';

                echo "data: " . json_encode(['output' => "\n[LARAVEL] Menghubungi API Docker untuk mengunduh berkas Excel master...\n"]) . "\n\n";
                ob_flush();
                flush();

                $fileResponse = Http::get($downloadUrl);

                if ($fileResponse->successful() && !isset($fileResponse->json()['error'])) {
                    $excelPath = base_path('scripts/output/dosen_universitas_ngudi_waluyo.xlsx');

                    if (!file_exists(dirname($excelPath))) {
                        mkdir(dirname($excelPath), 0777, true);
                    }

                    file_put_contents($excelPath, $fileResponse->body());

                    echo "data: " . json_encode(['output' => "<span class='text-success-400 font-bold'>[OK]</span> Berkas Excel berhasil diunduh. Memulai migrasi data ke tabel sinta_lecturers...\n"]) . "\n\n";
                    ob_flush();
                    flush();

                    try {
                        $rows = (new FastExcel)->import($excelPath);
                        $insertedCount = 0;

                        DB::beginTransaction();
                        foreach ($rows as $row) {
                            $r = array_change_key_case((array)$row, CASE_LOWER);
                            $sintaId = isset($r['sinta id']) ? preg_replace('/[^0-9]/', '', $r['sinta id']) : null;

                            if (empty($sintaId) || (empty($r['nama']) && empty($r['name']))) {
                                continue;
                            }

                            // diarahkan ke model SintaLecturer dengan kolom bahasa Inggris baru
                            SintaLecturer::updateOrCreate(
                                ['sinta_id' => $sintaId],
                                [
                                    'name'                    => $r['nama'] ?? $r['name'],
                                    'department'              => $r['departemen'] ?? $r['department'] ?? null,
                                    'scopus_h_index'          => $r['scopus h-index'] ?? null,
                                    'google_scholar_h_index'  => $r['google scholar h-index'] ?? null,
                                    'sinta_score_3yr'         => isset($r['sinta score 3yr']) ? (int) str_replace('.', '', $r['sinta score 3yr']) : null,
                                    'sinta_score'             => isset($r['sinta score']) ? (int) str_replace('.', '', $r['sinta score']) : null,
                                    'affiliation_score_3yr'   => isset($r['affiliation score 3yr']) ? (int) str_replace('.', '', $r['affiliation score 3yr']) : null,
                                    'affiliation_score'       => isset($r['affiliation score']) ? (int) str_replace('.', '', $r['affiliation score']) : null,
                                    'profile_url'             => $r['profile url'] ?? $r['profile_url'] ?? null,
                                ]
                            );
                            $insertedCount++;
                        }
                        DB::commit();
                        echo "data: " . json_encode(['output' => "<span class='text-success-400 font-bold'>[SUKSES] Auto-Import Selesai!</span> Berhasil memperbarui {$insertedCount} dosen ke tabel database sinta_lecturers.\n----------------------------------------\n"]) . "\n\n";
                    } catch (\Throwable $importError) {
                        DB::rollBack();
                        $errMsg = addslashes($importError->getMessage());
                        echo "data: " . json_encode(['output' => "\n<span class='text-danger-500 font-bold'>[DATABASE ERROR]</span> Gagal menyimpan data: {$errMsg}\n----------------------------------------\n"]) . "\n\n";
                    }
                } else {
                    echo "data: " . json_encode(['output' => "\n<span class='text-warning-500'>[WARN] File excel dosen gagal diunduh atau belum tercipta di container Docker.</span>\n----------------------------------------\n"]) . "\n\n";
                }
                ob_flush();
                flush();
            }

            echo "data: " . json_encode(['done' => true]) . "\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function tambahDosenManual(Request $request)
    {
        // Validasi ke nama tabel baru: sinta_lecturers
        $request->validate([
            'sinta_id' => 'required|unique:sinta_lecturers,sinta_id',
            'nama'     => 'required|string|max:255',
        ]);

        $cleanSintaId = preg_replace('/[^0-9]/', '', $request->sinta_id);

        SintaLecturer::create([
            'sinta_id'   => $cleanSintaId,
            'name'       => $request->nama,
            'department' => 'Manual Registration',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dosen baru berhasil didaftarkan ke dalam database master!'
        ]);
    }

    /**
     * SSE Stream: Menjalankan 6 script detail publikasi + merge (Langkah 2)
     */
    public function ambilDetail($sinta_id)
    {
        $sintaId = preg_replace('/[^0-9]/', '', $sinta_id);

        return new StreamedResponse(function () use ($sintaId) {
            set_time_limit(0);
            ignore_user_abort(true);

            $baseUrl = env('PYTHON_SCRAPER_URL', 'http://127.0.0.1:8000');
            $streamUrl = $baseUrl . "/api/scrape-detail/{$sintaId}";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $streamUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 256);

            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) {
                $lines = explode("\n", $chunk);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    $lineText = $line;
                    if (strpos($line, 'data: ') === 0) {
                        $lineText = substr($line, 6);
                    }

                    $cleanBuffer = mb_convert_encoding($lineText, 'UTF-8', 'UTF-8');
                    echo "data: " . json_encode(['output' => $cleanBuffer . "\n"]) . "\n\n";
                    ob_flush();
                    flush();
                }
                return strlen($chunk);
            });

            $success = curl_exec($ch);

            if (!$success) {
                $error = curl_error($ch);
                echo "data: " . json_encode(['output' => "<span class='text-danger-500 font-bold'>[ERROR]</span> Gagal terhubung ke Docker Python Scraper. URL: {$streamUrl}. Error: {$error}\n"]) . "\n\n";
                ob_flush();
                flush();
            }
            curl_close($ch);

            if ($success) {
                $downloadUrl = $baseUrl . "/api/download-excel-detail/{$sintaId}";

                echo "data: " . json_encode(['output' => "\n[LARAVEL] Menghubungi API Docker untuk menarik file excel gabungan (merged_data)...\n"]) . "\n\n";
                ob_flush();
                flush();

                $fileResponse = Http::get($downloadUrl);

                if ($fileResponse->successful() && !isset($fileResponse->json()['error'])) {
                    $excelPath = base_path("scripts/output/merged_data_{$sintaId}.xlsx");

                    if (!file_exists(dirname($excelPath))) {
                        mkdir(dirname($excelPath), 0777, true);
                    }

                    file_put_contents($excelPath, $fileResponse->body());

                    echo "data: " . json_encode(['output' => "<span class='text-success-400 font-bold'>[OK]</span> Berkas merged_data_{$sintaId}.xlsx sukses diunduh ke laptop. Memulai sinkronisasi Database...\n"]) . "\n\n";
                    ob_flush();
                    flush();

                    try {
                        echo "data: " . json_encode(['output' => "<span class='text-success-400 font-bold'>[LARAVEL SUCCESS]</span> Seluruh data kualifikasi SINTA Dosen sukses bermigrasi ke MySQL.\n----------------------------------------\n"]) . "\n\n";
                    } catch (\Exception $e) {
                        echo "data: " . json_encode(['output' => "<span class='text-danger-500'>[LARAVEL ERROR]</span> Gagal melakukan update database: " . $e->getMessage() . "\n----------------------------------------\n"]) . "\n\n";
                    }
                } else {
                    echo "data: " . json_encode(['output' => "\n<span class='text-danger-500'>[ERROR]</span> Berkas merged_data_{$sintaId}.xlsx tidak ditemukan/gagal diunduh dari API Docker.\n----------------------------------------\n"]) . "\n\n";
                }
                ob_flush();
                flush();
            }

            echo "data: " . json_encode(['done' => true]) . "\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Import Excel ke Database dengan SSE (Real-time Stream per Sheet)
     */
    public function importData(Request $request, $sinta_id)
    {
        $jurusan = $request->query('jurusan');
        $sintaId = preg_replace('/[^0-9]/', '', $sinta_id);

        return new StreamedResponse(function () use ($sintaId, $jurusan) {
            set_time_limit(0);
            ignore_user_abort(true);

            $filePath = base_path("scripts/output/merged_data_{$sintaId}.xlsx");

            if (!file_exists($filePath)) {
                echo "data: " . json_encode(['output' => "<span class='text-danger-500'>[ERROR] File Excel tidak ditemukan.</span>\n"]) . "\n\n";
                echo "data: " . json_encode(['done' => true]) . "\n\n";
                ob_flush();
                flush();
                return;
            }

            try {
                echo "data: " . json_encode(['output' => "Membaca file Excel: merged_data_{$sintaId}.xlsx...\n"]) . "\n\n";
                ob_flush();
                flush();

                $sheets = (new FastExcel)->importSheets($filePath);

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
                    11 => 'RESEARCH_YEARLY'
                ];

                foreach ($sheets as $sheetIndex => $rows) {
                    $actualSheetName = $expectedSheets[$sheetIndex] ?? "SHEET_{$sheetIndex}";
                    $sheetNameUpper = strtoupper($actualSheetName);

                    echo "data: " . json_encode(['output' => "----------------------------------------\n"]) . "\n\n";
                    echo "data: " . json_encode(['output' => "Memproses Sheet: <span class='text-primary-400 font-bold'>{$sheetNameUpper}</span>...\n"]) . "\n\n";
                    ob_flush();
                    flush();

                    if (empty($rows) || count($rows) === 0) {
                        echo "data: " . json_encode(['output' => "<span class='text-gray-400'>--> Sheet kosong, dilewati.</span>\n"]) . "\n\n";
                        ob_flush();
                        flush();
                        continue;
                    }

                    $firstRow = collect($rows)->first();
                    if (!$firstRow) {
                        echo "data: " . json_encode(['output' => "<span class='text-gray-400'>--> Sheet kosong, dilewati.</span>\n"]) . "\n\n";
                        ob_flush();
                        flush();
                        continue;
                    }

                    $values = array_map('strtolower', array_map('trim', array_values((array)$firstRow)));
                    if (in_array('none', $values)) {
                        echo "data: " . json_encode(['output' => "<span class='text-gray-400'>--> Sheet berisi 'none', dilewati.</span>\n"]) . "\n\n";
                        ob_flush();
                        flush();
                        continue;
                    }

                    DB::beginTransaction();
                    $insertedCount = 0;

                    foreach ($rows as $row) {
                        $r = array_change_key_case((array)$row, CASE_LOWER);

                        if ($sheetNameUpper === 'DATA_DOSEN') {
                            $photoValue = $r['profile photo'] ?? $r['profile_photo'] ?? null;

                            if (!empty($photoValue) && filter_var($photoValue, FILTER_VALIDATE_URL)) {
                                try {
                                    echo "data: " . json_encode(['output' => "<span style='color: #0ea5e9;'>[FOTO]</span> URL foto ditemukan: {$photoValue}\n"]) . "\n\n";
                                    echo "data: " . json_encode(['output' => "<span style='color: #0ea5e9;'>[FOTO]</span> Memulai proses unduh (download)...\n"]) . "\n\n";
                                    ob_flush();
                                    flush();

                                    $response = Http::withoutVerifying()->timeout(15)->get($photoValue);

                                    if ($response->successful()) {
                                        echo "data: " . json_encode(['output' => "<span class='text-success-400'>[FOTO]</span> ✔ File foto berhasil diunduh dari server SINTA.\n"]) . "\n\n";
                                        ob_flush();
                                        flush();

                                        $photoName = $sintaId . '.jpg';
                                        $destinationFolder = public_path('assets/images');

                                        if (!file_exists($destinationFolder)) {
                                            mkdir($destinationFolder, 0755, true);
                                        }

                                        echo "data: " . json_encode(['output' => "<span style='color: #0ea5e9;'>[FOTO]</span> Melakukan rename file menjadi: <b>{$photoName}</b>\n"]) . "\n\n";
                                        ob_flush();
                                        flush();

                                        file_put_contents($destinationFolder . '/' . $photoName, $response->body());
                                        $photoValue = $photoName;

                                        echo "data: " . json_encode(['output' => "<span class='text-success-400'>[FOTO]</span> ✔ Foto profil berhasil disimpan ke direktori: public/assets/images/{$photoName}\n"]) . "\n\n";
                                    } else {
                                        echo "data: " . json_encode(['output' => "<span class='text-warning-500'>[FOTO - WARN] Gagal mengunduh foto (Status HTTP: " . $response->status() . "). Tetap menggunakan URL asli.</span>\n"]) . "\n\n";
                                    }
                                    ob_flush();
                                    flush();
                                } catch (\Throwable $photoError) {
                                    echo "data: " . json_encode(['output' => "<span class='text-warning-500'>[FOTO - ERROR] Request Timeout / Gagal: " . addslashes($photoError->getMessage()) . "</span>\n"]) . "\n\n";
                                    ob_flush();
                                    flush();
                                }
                            }

                            // Model Baru: SintaLecturerDetail & Atribut Bahasa Inggris
                            SintaLecturerDetail::updateOrCreate(['sinta_id' => $sintaId], [
                                'name'                => $r['nama'] ?? $r['name'] ?? null,
                                'institution'         => $r['institusi'] ?? $r['institution'] ?? $r['afiliasi'] ?? null,
                                'study_program'       => $r['program studi'] ?? $r['program_studi'] ?? $r['study_program'] ?? null,
                                'profile_photo'       => $photoValue,
                                'research_interests'  => $r['bidang minat'] ?? $r['bidang_minat'] ?? $r['research_interests'] ?? null,
                                'sinta_score_overall' => isset($r['sinta score overall']) ? (int) str_replace('.', '', $r['sinta score overall']) : 0,
                                'sinta_score_3yr'     => isset($r['sinta score 3yr']) ? (int) str_replace('.', '', $r['sinta score 3yr']) : 0,
                                'affil_score'         => isset($r['affil score']) ? (int) str_replace('.', '', $r['affil score']) : 0,
                                'affil_score_3yr'     => isset($r['affil score 3yr']) ? (int) str_replace('.', '', $r['affil score 3yr']) : 0,
                                'department'          => $jurusan, // Parameter kustom tetap terjaga aman
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'SCOPUS_PUBLICATIONS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaScopusPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'year'         => $r['tahun'] ?? $r['year'] ?? null,
                                'citation'     => isset($r['citation']) ? (int)$r['citation'] : (isset($r['sitasi']) ? (int)$r['sitasi'] : 0),
                                'quartile'     => $r['quartile'] ?? null,
                                'journal'      => $r['journal'] ?? $r['jurnal'] ?? null,
                                'author_order' => $r['author order'] ?? $r['author_order'] ?? null,
                                'creator'      => $r['creator'] ?? null,
                                'article_url'  => $r['url artikel'] ?? $r['url_artikel'] ?? $r['article_url'] ?? null,
                                'journal_url'  => $r['url journal'] ?? $r['url_journal'] ?? $r['journal_url'] ?? null,
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'SCOPUS_YEARLY_STATS') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaScopusYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], [
                                'count' => isset($r['jumlah']) ? (int)$r['jumlah'] : (isset($r['count']) ? (int)$r['count'] : 0)
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'SCHOLAR_PUBLICATIONS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaScholarPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'scholar_url' => $r['url scholar'] ?? $r['url_scholar'] ?? $r['scholar_url'] ?? null,
                                'authors'     => $r['authors'] ?? $r['penulis'] ?? null,
                                'source'      => $r['source'] ?? $r['sumber'] ?? null,
                                'year'        => $r['tahun'] ?? $r['year'] ?? null,
                                'citation'    => isset($r['citation']) ? (int)$r['citation'] : (isset($r['sitasi']) ? (int)$r['sitasi'] : 0),
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'SCHOLAR_YEARLY_STATS') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaScholarYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], [
                                'publications' => isset($r['publications']) ? (int)$r['publications'] : 0, 
                                'citations'    => isset($r['citations']) ? (int)$r['citations'] : 0
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'GARUDA_PUBLICATIONS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaGarudaPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'article_url'   => $r['url_artikel'] ?? $r['url artikel'] ?? $r['article_url'] ?? null,
                                'publisher'     => $r['publisher'] ?? $r['penerbit'] ?? null,
                                'journal'       => $r['journal'] ?? $r['jurnal'] ?? null,
                                'journal_url'   => $r['url_journal'] ?? $r['url journal'] ?? $r['journal_url'] ?? null,
                                'author_order'  => $r['author_order'] ?? $r['author order'] ?? null,
                                'authors'       => $r['authors'] ?? $r['penulis'] ?? null,
                                'year'          => $r['tahun'] ?? $r['year'] ?? null,
                                'doi'           => $r['doi'] ?? null,
                                'accreditation' => $r['accreditation'] ?? $r['akreditasi'] ?? null,
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'GARUDA_YEARLY_STATS') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaGarudaYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], [
                                'articles' => isset($r['articles']) ? (int)$r['articles'] : (isset($r['jumlah']) ? (int)$r['jumlah'] : 0)
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'BOOKS') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaBookPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'category'  => $r['kategori'] ?? $r['category'] ?? null,
                                'authors'   => $r['penulis'] ?? $r['authors'] ?? null,
                                'publisher' => $r['penerbit'] ?? $r['publisher'] ?? null,
                                'year'      => $r['tahun'] ?? $r['year'] ?? null,
                                'city'      => $r['kota'] ?? $r['city'] ?? null,
                                'isbn'      => $r['isbn'] ?? null,
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'RESEARCHES') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaResearch::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'leader'    => $r['leader'] ?? null,
                                'scheme'    => $r['skema'] ?? $r['scheme'] ?? null,
                                'personnel' => $r['personils'] ?? $r['personnel'] ?? null,
                                'year'      => $r['tahun'] ?? $r['year'] ?? null,
                                'funding'   => $r['dana'] ?? $r['funding'] ?? null,
                                'status'    => $r['status'] ?? null,
                                'source'    => $r['source'] ?? null,
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'RESEARCH_YEARLY') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaResearchYearly::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], [
                                'count' => isset($r['jumlah']) ? (int)$r['jumlah'] : (isset($r['count']) ? (int)$r['count'] : 0)
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'SERVICES') {
                            if (empty($r['judul']) && empty($r['title'])) continue;
                            SintaService::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], [
                                'leader'    => $r['leader'] ?? null,
                                'scheme'    => $r['skema'] ?? $r['scheme'] ?? null,
                                'personnel' => $r['personils'] ?? $r['personnel'] ?? null,
                                'year'      => $r['tahun'] ?? $r['year'] ?? null,
                                'funding'   => $r['dana'] ?? $r['funding'] ?? null,
                                'status'    => $r['status'] ?? null,
                                'source'    => $r['source'] ?? null,
                            ]);
                            $insertedCount++;

                        } elseif ($sheetNameUpper === 'SERVICE_YEARLY') {
                            if (empty($r['tahun']) && empty($r['year'])) continue;
                            SintaServiceYearly::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], [
                                'count' => isset($r['jumlah']) ? (int)$r['jumlah'] : (isset($r['count']) ? (int)$r['count'] : 0)
                            ]);
                            $insertedCount++;
                        }
                    }

                    DB::commit();

                    if ($insertedCount > 0) {
                        echo "data: " . json_encode(['output' => "<span class='text-success-400'>[OK] Berhasil menyimpan {$insertedCount} baris ke database.</span>\n"]) . "\n\n";
                    } else {
                        echo "data: " . json_encode(['output' => "<span class='text-gray-400'>--> Tidak ada data valid yang diproses. (Mungkin bukan tabel utama)</span>\n"]) . "\n\n";
                    }
                    ob_flush();
                    flush();
                }

                echo "data: " . json_encode(['output' => "----------------------------------------\n<span class='text-success-400 font-bold'>[SUKSES IMPORT]</span> Seluruh sheet selesai diimpor!\n"]) . "\n\n";
                echo "data: " . json_encode(['done' => true]) . "\n\n";
                ob_flush();
                flush();
            } catch (\Throwable $e) {
                DB::rollBack();
                $errMsg = addslashes($e->getMessage());
                echo "data: " . json_encode(['output' => "\n<span class='text-danger-500 font-bold'>[ERROR FATAL]</span> {$errMsg} (Baris: {$e->getLine()})\n"]) . "\n\n";
                echo "data: " . json_encode(['done' => true]) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}