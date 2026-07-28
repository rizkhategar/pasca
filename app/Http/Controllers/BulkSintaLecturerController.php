<?php

namespace App\Http\Controllers;

use App\Models\PostgraduateLecturer;
use App\Models\PostgraduateLecturerStudyProgram;
use App\Models\SintaBookPublication;
use App\Models\SintaGarudaPublication;
use App\Models\SintaGarudaYearlyStat;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerDetail;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\SintaResearch;
use App\Models\SintaResearchYearly;
use App\Models\SintaScholarPublication;
use App\Models\SintaScholarYearlyStat;
use App\Models\SintaScopusPublication;
use App\Models\SintaScopusYearlyStat;
use App\Models\SintaService;
use App\Models\SintaServiceYearly;
use App\Models\StudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkSintaLecturerController extends Controller
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

    private function keepAlive($ch): void
    {
        $lastPing = microtime(true);

        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_XFERINFOFUNCTION, function () use (&$lastPing) {
            if (microtime(true) - $lastPing >= 15) {
                echo ": heartbeat\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }

                flush();
                $lastPing = microtime(true);
            }

            return 0;
        });
    }

    public function fetchAll(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            $batch = $this->createBatchFromMasterLecturers();

            if (! $batch) {
                $this->stream(['output' => "<span class='text-warning-500'>[WARN]</span> No SINTA lecturer records were found. Run Step 1 sync first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $this->stream(['output' => "[BATCH] Created fetch batch #{$batch->id} with {$batch->total_items} lecturer(s).\n"]);
            $this->processBatchItems($batch, ['pending']);
        });
    }

    public function resume(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            $batch = $this->latestBatch();

            if (! $batch) {
                $this->stream(['output' => "<span class='text-warning-500'>[WARN]</span> No fetch batch was found. Start Fetch All first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $this->stream(['output' => "[BATCH] Resuming batch #{$batch->id}. Pending items will continue.\n"]);
            $this->processBatchItems($batch, ['pending']);
        });
    }

    public function retryFailed(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            $batch = $this->latestBatch();

            if (! $batch) {
                $this->stream(['output' => "<span class='text-warning-500'>[WARN]</span> No fetch batch was found. Start Fetch All first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $failedItems = $batch->items()->where('status', 'failed')->get();

            if ($failedItems->isEmpty()) {
                $this->stream(['output' => "<span class='text-gray-400'>[INFO]</span> No failed item was found in batch #{$batch->id}.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            foreach ($failedItems as $item) {
                $item->update([
                    'status' => 'pending',
                    'error_message' => null,
                    'import_status' => 'not_ready',
                    'import_error' => null,
                    'retry_count' => $item->retry_count + 1,
                ]);
            }

            $this->stream(['output' => "[BATCH] Retrying {$failedItems->count()} failed item(s) from batch #{$batch->id}.\n"]);
            $this->processBatchItems($batch->fresh(), ['pending']);
        });
    }

    public function reset(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            $batch = $this->latestBatch();

            if (! $batch) {
                $this->stream(['output' => "<span class='text-gray-400'>[INFO]</span> No active batch was found.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $batch->update([
                'status' => 'cancelled',
                'finished_at' => now(),
                'error_message' => 'Cancelled manually by admin.',
            ]);

            $this->stream(['output' => "<span class='text-warning-500'>[RESET]</span> Batch #{$batch->id} was cancelled. Start Fetch All to create a new batch.\n"]);
            $this->stream(['done' => true]);
        });
    }

    public function studyProgramSettings(): JsonResponse
    {
        $batch = $this->latestBatch();
        $programs = $this->magisterStudyProgramsQuery()
            ->get()
            ->map(fn (StudyProgram $program) => [
                'id' => $program->id,
                'display_name' => $program->display_name,
                'jenjang' => $program->jenjang,
            ])
            ->values();

        if (! $batch) {
            return response()->json([
                'batch' => null,
                'programs' => $programs,
                'items' => [],
                'summary' => [
                    'ready_count' => 0,
                    'missing_setting_count' => 0,
                    'failed_count' => 0,
                    'unfetched_count' => SintaLecturer::query()->count(),
                ],
            ]);
        }

        $items = $batch->items()
            ->orderBy('lecturer_name')
            ->orderBy('sinta_id')
            ->get();

        $settings = SintaLecturerStudyProgramSetting::query()
            ->whereIn('sinta_id', $items->pluck('sinta_id')->filter()->values())
            ->get()
            ->groupBy('sinta_id');

        $mappedItems = $items->map(function (SintaLecturerFetchBatchItem $item) use ($settings) {
            $selected = $settings->get($item->sinta_id, collect())->pluck('study_program_id')->map(fn ($id) => (int) $id)->values();
            $canSet = in_array($item->status, ['success', 'success_with_warning'], true);

            return [
                'sinta_id' => $item->sinta_id,
                'lecturer_name' => $item->lecturer_name,
                'fetch_status' => $item->status,
                'import_status' => $item->import_status,
                'warning_message' => $item->warning_message,
                'error_message' => $item->error_message,
                'can_set_program' => $canSet,
                'study_program_ids' => $selected,
                'setting_status' => $canSet ? ($selected->isEmpty() ? 'not_set' : 'complete') : 'blocked',
            ];
        })->values();

        $summary = $this->batchReadinessSummary($batch);

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'total_items' => $batch->total_items,
                'processed_items' => $batch->processed_items,
                'success_items' => $batch->success_items,
                'warning_items' => $batch->warning_items,
                'failed_items' => $batch->failed_items,
            ],
            'programs' => $programs,
            'items' => $mappedItems,
            'summary' => $summary,
        ]);
    }

    public function saveStudyProgramSettings(Request $request): JsonResponse
    {
        $settings = collect($request->input('settings', []));

        if ($settings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No lecturer study program settings were submitted.',
            ], 422);
        }

        DB::transaction(function () use ($settings): void {
            foreach ($settings as $setting) {
                $sintaId = $this->cleanSintaId(data_get($setting, 'sinta_id'));

                if (! $sintaId) {
                    continue;
                }

                $selectedStudyProgramIds = collect(data_get($setting, 'study_program_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values();

                $validStudyProgramIds = $this->magisterStudyProgramsQuery()
                    ->whereIn('id', $selectedStudyProgramIds)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values();

                SintaLecturerStudyProgramSetting::where('sinta_id', $sintaId)->delete();

                foreach ($validStudyProgramIds as $studyProgramId) {
                    SintaLecturerStudyProgramSetting::create([
                        'sinta_id' => $sintaId,
                        'study_program_id' => $studyProgramId,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Lecturer study program settings have been saved.',
        ]);
    }

    public function importAll(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            $batch = $this->latestBatch();

            if (! $batch) {
                $this->stream(['output' => "<span class='text-danger-500'>[ERROR]</span> No fetch batch was found. Run Fetch All first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $summary = $this->batchReadinessSummary($batch);

            if ($summary['unfetched_count'] > 0) {
                $this->stream(['output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because {$summary['unfetched_count']} SINTA lecturer(s) are not included in the latest fetch batch. Run Fetch All again first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            if ($summary['failed_count'] > 0 || $summary['pending_count'] > 0 || $summary['processing_count'] > 0) {
                $this->stream(['output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because the latest batch still has failed, pending, or processing items. Use Resume or Retry Failed first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            if ($summary['missing_setting_count'] > 0) {
                $this->stream(['output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because {$summary['missing_setting_count']} lecturer(s) do not have study program settings. Open Setting Prodi Fetch All first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $items = $batch->items()
                ->whereIn('status', ['success', 'success_with_warning'])
                ->orderBy('lecturer_name')
                ->get();

            $imported = 0;
            $failed = 0;

            foreach ($items as $item) {
                $settings = SintaLecturerStudyProgramSetting::query()
                    ->where('sinta_id', $item->sinta_id)
                    ->pluck('study_program_id')
                    ->map(fn ($id) => (string) $id)
                    ->values();

                $studyProgramIds = $settings->implode(',');
                $filePath = base_path("scripts/output/merged_data_{$item->sinta_id}.xlsx");

                $this->stream(['output' => "\n[BATCH IMPORT] Importing {$item->lecturer_name} ({$item->sinta_id})...\n"]);

                if (! file_exists($filePath)) {
                    $failed++;
                    $message = "merged_data_{$item->sinta_id}.xlsx was not found. Fetch this lecturer again.";
                    $item->update(['import_status' => 'import_failed', 'import_error' => $message]);
                    $this->stream(['output' => "<span class='text-danger-500'>[ERROR]</span> {$message}\n"]);
                    continue;
                }

                try {
                    $this->importMergedDetailFile($filePath, $item->sinta_id, $studyProgramIds);
                    SintaLecturerStudyProgramSetting::where('sinta_id', $item->sinta_id)->update(['last_used_at' => now(), 'updated_by' => auth()->id()]);
                    $item->update(['import_status' => 'imported', 'import_error' => null]);
                    $imported++;
                } catch (\Throwable $e) {
                    $failed++;
                    $item->update(['import_status' => 'import_failed', 'import_error' => $e->getMessage()]);
                    $this->stream(['output' => "<span class='text-danger-500 font-bold'>[IMPORT ERROR]</span> " . addslashes($e->getMessage()) . "\n"]);
                }
            }

            $this->stream(['output' => "\n<span class='text-success-400 font-bold'>[IMPORT ALL FINISHED]</span> Imported: {$imported}. Failed: {$failed}.\n"]);
            $this->stream(['done' => true]);
        });
    }

    private function streamResponse(callable $callback): StreamedResponse
    {
        return new StreamedResponse(function () use ($callback): void {
            set_time_limit(0);
            ignore_user_abort(true);
            $callback();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function createBatchFromMasterLecturers(): ?SintaLecturerFetchBatch
    {
        $lecturers = SintaLecturer::query()->orderBy('name')->get(['sinta_id', 'name']);

        if ($lecturers->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($lecturers) {
            SintaLecturerFetchBatch::query()
                ->whereIn('status', ['pending', 'running', 'paused', 'failed'])
                ->update(['status' => 'cancelled', 'finished_at' => now(), 'error_message' => 'Superseded by a new fetch-all batch.']);

            $batch = SintaLecturerFetchBatch::create([
                'status' => 'running',
                'total_items' => $lecturers->count(),
                'processed_items' => 0,
                'success_items' => 0,
                'warning_items' => 0,
                'failed_items' => 0,
                'started_at' => now(),
            ]);

            foreach ($lecturers as $lecturer) {
                $batch->items()->create([
                    'sinta_id' => $lecturer->sinta_id,
                    'lecturer_name' => $lecturer->name,
                    'status' => 'pending',
                    'import_status' => 'not_ready',
                ]);
            }

            return $batch;
        });
    }

    private function processBatchItems(SintaLecturerFetchBatch $batch, array $statuses): void
    {
        $baseUrl = config('services.python_scraper.url');
        $items = $batch->items()->whereIn('status', $statuses)->orderBy('id')->get();

        if ($items->isEmpty()) {
            $this->refreshBatchCounters($batch);
            $this->stream(['output' => "<span class='text-gray-400'>[INFO]</span> No matching batch item needs processing.\n"]);
            $this->stream(['done' => true]);
            return;
        }

        $batch->update(['status' => 'running', 'paused_at' => null]);

        foreach ($items as $item) {
            $batch->update(['current_sinta_id' => $item->sinta_id]);
            $item->update([
                'status' => 'processing',
                'started_at' => now(),
                'finished_at' => null,
                'error_message' => null,
                'warning_message' => null,
                'import_status' => 'not_ready',
                'import_error' => null,
            ]);

            $this->stream(['output' => "\n========================================\n"]);
            $this->stream(['output' => "[BATCH] Fetching {$item->lecturer_name} ({$item->sinta_id})\n"]);
            $result = $this->fetchOneLecturerDetail($baseUrl, $item->sinta_id);

            $item->update([
                'status' => $result['status'],
                'log_output' => Str::limit($result['log_output'], 65000, '... [log truncated]'),
                'error_message' => $result['error_message'],
                'warning_message' => $result['warning_message'],
                'finished_at' => now(),
            ]);

            $this->refreshBatchCounters($batch);

            if ($result['status'] === 'failed') {
                $batch->update([
                    'status' => 'paused',
                    'paused_at' => now(),
                    'error_message' => $result['error_message'],
                    'current_sinta_id' => $item->sinta_id,
                ]);

                $this->stream(['output' => "\n<span class='text-danger-500 font-bold'>[BATCH PAUSED]</span> Fatal error detected for {$item->sinta_id}. Fix the issue or use Retry Failed / Resume.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            if ($result['status'] === 'success_with_warning') {
                $this->stream(['output' => "<span class='text-warning-500'>[WARNING]</span> Fetch completed with empty-data warnings. This lecturer can still be imported after prodi setting.\n"]);
            } else {
                $this->stream(['output' => "<span class='text-success-400'>[OK]</span> Fetch completed successfully.\n"]);
            }
        }

        $this->refreshBatchCounters($batch);
        $batch->refresh();

        if ($batch->items()->whereIn('status', ['pending', 'processing', 'failed'])->doesntExist()) {
            $batch->update([
                'status' => 'completed',
                'finished_at' => now(),
                'current_sinta_id' => null,
                'error_message' => null,
            ]);
            $this->stream(['output' => "\n<span class='text-success-400 font-bold'>[BATCH COMPLETED]</span> All lecturers were fetched. Open Setting Prodi Fetch All before Import All.\n"]);
        } else {
            $this->stream(['output' => "\n<span class='text-gray-400'>[BATCH]</span> Batch processing stopped with remaining items.\n"]);
        }

        $this->stream(['done' => true]);
    }

    private function fetchOneLecturerDetail(string $baseUrl, string $sintaId): array
    {
        $streamUrl = $baseUrl . "/api/scrape-detail/{$sintaId}";
        $logOutput = "[LARAVEL] Connecting to Python scraper: {$streamUrl}\n";
        $this->stream(['output' => $logOutput]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $streamUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 256);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, string $chunk) use (&$logOutput) {
            foreach (explode("\n", $chunk) as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                $lineText = str_starts_with($line, 'data: ') ? substr($line, 6) : $line;
                $cleanLine = mb_convert_encoding($lineText, 'UTF-8', 'UTF-8');
                $logOutput .= strip_tags($cleanLine) . "\n";
                $this->stream(['output' => $cleanLine . "\n"]);
            }

            return strlen($chunk);
        });
        $this->keepAlive($ch);

        $success = curl_exec($ch);

        if (! $success) {
            $curlError = curl_error($ch);
            $message = "[CURL ERROR] Failed to connect to the Docker Python scraper. URL: {$streamUrl}. Error: {$curlError}\n";
            Log::error($message);
            $logOutput .= $message;
            $this->stream(['output' => "<span class='text-danger-500 font-bold'>[ERROR]</span> {$message}"]);
        }

        curl_close($ch);

        $downloaded = false;

        if ($success) {
            $downloaded = $this->downloadMergedDetailExcel($baseUrl, $sintaId);
            $logOutput .= $downloaded
                ? "[LARAVEL] merged_data_{$sintaId}.xlsx downloaded successfully.\n"
                : "[LARAVEL] merged_data_{$sintaId}.xlsx could not be downloaded.\n";
        }

        return $this->classifyScraperOutput($logOutput, (bool) $success && $downloaded);
    }

    private function downloadMergedDetailExcel(string $baseUrl, string $sintaId): bool
    {
        $downloadUrl = $baseUrl . "/api/download-excel-detail/{$sintaId}";
        $this->stream(['output' => "\n[LARAVEL] Contacting Docker API to download merged detail Excel file...\n"]);
        $fileResponse = Http::timeout(60)->get($downloadUrl);
        $isJsonError = str_contains((string) $fileResponse->header('Content-Type'), 'application/json')
            && data_get($fileResponse->json(), 'error');

        if (! $fileResponse->successful() || $isJsonError) {
            $this->stream(['output' => "\n<span class='text-danger-500'>[ERROR]</span> merged_data_{$sintaId}.xlsx was not found or could not be downloaded from Docker API.\n----------------------------------------\n"]);
            return false;
        }

        $excelPath = base_path("scripts/output/merged_data_{$sintaId}.xlsx");

        if (! file_exists(dirname($excelPath))) {
            mkdir(dirname($excelPath), 0777, true);
        }

        file_put_contents($excelPath, $fileResponse->body());
        $this->stream(['output' => "<span class='text-success-400 font-bold'>[OK]</span> merged_data_{$sintaId}.xlsx downloaded successfully.\n"]);

        return true;
    }

    private function classifyScraperOutput(string $logOutput, bool $downloaded): array
    {
        $normalized = Str::of(strip_tags($logOutput))->lower()->toString();
        $fatalPatterns = [
            'traceback',
            'gagal membuka halaman',
            'httperror',
            'status: 403',
            'status: 404',
            'status: 500',
            'failed to connect to the docker python scraper',
            'curl error',
            'connection was interrupted',
            'terjadi kesalahan fatal',
            '[fatal error]',
            'sinta id tidak diberikan',
            'exception',
        ];

        foreach ($fatalPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return [
                    'status' => 'failed',
                    'error_message' => "Fatal scraper pattern detected: {$pattern}",
                    'warning_message' => null,
                    'log_output' => $logOutput,
                ];
            }
        }

        if (! $downloaded) {
            return [
                'status' => 'failed',
                'error_message' => 'Merged detail Excel was not downloaded. The scraper did not finish successfully.',
                'warning_message' => null,
                'log_output' => $logOutput,
            ];
        }

        $warningPatterns = [
            'tidak ada publikasi',
            'data scopus kosong/tidak ditemukan',
            'data scholar kosong/tidak ditemukan',
            'data garuda kosong/tidak ditemukan',
            'data books kosong/tidak ditemukan',
            'data services kosong/tidak ditemukan',
            'data researches kosong/tidak ditemukan',
            "membuat sheet berisi 'none'",
            'sheet contains',
            'empty sheet',
            'grafik garuda tidak ditemukan',
            'gagal menemukan xaxis',
            'gagal menemukan series',
            'no valid data was processed',
        ];

        foreach ($warningPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return [
                    'status' => 'success_with_warning',
                    'error_message' => null,
                    'warning_message' => "Empty-data warning detected: {$pattern}",
                    'log_output' => $logOutput,
                ];
            }
        }

        return [
            'status' => 'success',
            'error_message' => null,
            'warning_message' => null,
            'log_output' => $logOutput,
        ];
    }

    private function latestBatch(): ?SintaLecturerFetchBatch
    {
        return SintaLecturerFetchBatch::query()->latest('id')->first();
    }

    private function refreshBatchCounters(SintaLecturerFetchBatch $batch): void
    {
        $batch->refresh();
        $batch->update([
            'processed_items' => $batch->items()->whereIn('status', ['success', 'success_with_warning', 'failed'])->count(),
            'success_items' => $batch->items()->where('status', 'success')->count(),
            'warning_items' => $batch->items()->where('status', 'success_with_warning')->count(),
            'failed_items' => $batch->items()->where('status', 'failed')->count(),
        ]);
    }

    private function batchReadinessSummary(SintaLecturerFetchBatch $batch): array
    {
        $batchItemIds = $batch->items()->pluck('sinta_id')->filter()->values();
        $currentMasterIds = SintaLecturer::query()->pluck('sinta_id')->filter()->values();
        $successItems = $batch->items()->whereIn('status', ['success', 'success_with_warning'])->get();
        $settingIds = SintaLecturerStudyProgramSetting::query()
            ->whereIn('sinta_id', $successItems->pluck('sinta_id'))
            ->pluck('sinta_id')
            ->unique();

        return [
            'ready_count' => $successItems->filter(fn ($item) => $settingIds->contains($item->sinta_id))->count(),
            'missing_setting_count' => $successItems->reject(fn ($item) => $settingIds->contains($item->sinta_id))->count(),
            'failed_count' => $batch->items()->where('status', 'failed')->count(),
            'pending_count' => $batch->items()->where('status', 'pending')->count(),
            'processing_count' => $batch->items()->where('status', 'processing')->count(),
            'unfetched_count' => $currentMasterIds->diff($batchItemIds)->count(),
        ];
    }

    private function magisterStudyProgramsQuery()
    {
        return StudyProgram::query()
            ->where(function ($query): void {
                $query->whereRaw('LOWER(study_programs.jenjang) LIKE ?', ['%magister%'])
                    ->orWhereRaw('LOWER(study_programs.jenjang) IN (?, ?, ?)', ['s2', 's-2', 'strata 2'])
                    ->orWhereRaw('LOWER(study_programs.jenjang_nama_singkat) IN (?, ?)', ['s2', 's-2'])
                    ->orWhereRaw('LOWER(study_programs.slug) LIKE ?', ['magister-%'])
                    ->orWhereRaw('LOWER(study_programs.slug) LIKE ?', ['s2-%'])
                    ->orWhereRaw('LOWER(study_programs.page_slug) LIKE ?', ['magister-%'])
                    ->orWhereRaw('LOWER(study_programs.page_slug) LIKE ?', ['s2-%']);
            })
            ->orderBy('jenjang')
            ->orderBy('nama');
    }

    private function importMergedDetailFile(string $filePath, string $sintaId, ?string $studyProgramIds): void
    {
        $this->stream(['output' => "Reading Excel file: " . basename($filePath) . "...\n"]);
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
            $this->stream(['output' => "Processing sheet: <span class='text-primary-400 font-bold'>{$sheetNameUpper}</span>...\n"]);

            if (empty($rows) || count($rows) === 0 || ! collect($rows)->first()) {
                $this->stream(['output' => "<span class='text-gray-400'>--> Empty sheet. Skipped.</span>\n"]);
                continue;
            }

            $firstRow = collect($rows)->first();
            $values = array_map('strtolower', array_map('trim', array_values((array) $firstRow)));

            if (in_array('none', $values, true)) {
                $this->stream(['output' => "<span class='text-gray-400'>--> Sheet contains 'none'. Skipped.</span>\n"]);
                continue;
            }

            DB::beginTransaction();
            $insertedCount = 0;

            try {
                foreach ($rows as $row) {
                    $r = $this->normalizeRow($row);

                    if ($sheetNameUpper === 'DATA_DOSEN') {
                        if ($this->importLecturerDataRow($r, $sintaId, $studyProgramIds)) {
                            $insertedCount++;
                        }
                    } elseif ($sheetNameUpper === 'SCOPUS_PUBLICATIONS') {
                        if (empty($r['judul']) && empty($r['title'])) continue;
                        SintaScopusPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], ['year' => $r['tahun'] ?? $r['year'] ?? null, 'citation' => isset($r['citation']) ? (int) $r['citation'] : (isset($r['sitasi']) ? (int) $r['sitasi'] : 0), 'quartile' => $r['quartile'] ?? null, 'journal' => $r['journal'] ?? $r['jurnal'] ?? null, 'author_order' => $r['author order'] ?? $r['author_order'] ?? null, 'creator' => $r['creator'] ?? null, 'article_url' => $r['url artikel'] ?? $r['url_artikel'] ?? $r['article_url'] ?? null, 'journal_url' => $r['url journal'] ?? $r['url_journal'] ?? $r['journal_url'] ?? null]);
                        $insertedCount++;
                    } elseif ($sheetNameUpper === 'SCOPUS_YEARLY_STATS') {
                        if (empty($r['tahun']) && empty($r['year'])) continue;
                        SintaScopusYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], ['count' => isset($r['jumlah']) ? (int) $r['jumlah'] : (isset($r['count']) ? (int) $r['count'] : 0)]);
                        $insertedCount++;
                    } elseif ($sheetNameUpper === 'SCHOLAR_PUBLICATIONS') {
                        if (empty($r['judul']) && empty($r['title'])) continue;
                        SintaScholarPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], ['scholar_url' => $r['url scholar'] ?? $r['url_scholar'] ?? $r['scholar_url'] ?? null, 'authors' => $r['authors'] ?? $r['penulis'] ?? null, 'source' => $r['source'] ?? $r['sumber'] ?? null, 'year' => $r['tahun'] ?? $r['year'] ?? null, 'citation' => isset($r['citation']) ? (int) $r['citation'] : (isset($r['sitasi']) ? (int) $r['sitasi'] : 0)]);
                        $insertedCount++;
                    } elseif ($sheetNameUpper === 'SCHOLAR_YEARLY_STATS') {
                        if (empty($r['tahun']) && empty($r['year'])) continue;
                        SintaScholarYearlyStat::updateOrCreate(['sinta_id' => $sintaId, 'year' => $r['tahun'] ?? $r['year']], ['publications' => isset($r['publications']) ? (int) $r['publications'] : 0, 'citations' => isset($r['citations']) ? (int) $r['citations'] : 0]);
                        $insertedCount++;
                    } elseif ($sheetNameUpper === 'GARUDA_PUBLICATIONS') {
                        if (empty($r['judul']) && empty($r['title'])) continue;
                        SintaGarudaPublication::updateOrCreate(['sinta_id' => $sintaId, 'title' => $r['judul'] ?? $r['title']], ['article_url' => $r['url_artikel'] ?? $r['url artikel'] ?? $r['article_url'] ?? null, 'publisher' => $r['publisher'] ?? $r['penerbit'] ?? null, 'journal' => $r['journal'] ?? $r['jurnal'] ?? null, 'journal_url' => $r['url_journal'] ?? $r['url journal'] ?? $r['journal_url'] ?? null, 'author_order' => $r['author_order'] ?? $r['author order'] ?? null, 'authors' => $r['authors'] ?? $r['penulis'] ?? null, 'year' => $r['tahun'] ?? $r['year'] ?? null, 'doi' => $r['doi'] ?? null, 'accreditation' => $r['accreditation'] ?? $r['akreditasi'] ?? null]);
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
                $this->stream(['output' => $insertedCount > 0 ? "<span class='text-success-400'>[OK] Successfully saved {$insertedCount} rows into the database.</span>\n" : "<span class='text-gray-400'>--> No valid data was processed.</span>\n"]);
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }

        $this->stream(['output' => "----------------------------------------\n<span class='text-success-400 font-bold'>[IMPORT SUCCESS]</span> All sheets have been imported successfully.\n"]);
        $this->stream(['output' => $this->deleteImportedExcelFile($filePath)]);
    }

    private function importLecturerDataRow(array $r, string $sintaId, ?string $studyProgramIds): bool
    {
        $photoValue = $r['profile photo'] ?? $r['profile_photo'] ?? null;

        if (! empty($photoValue) && filter_var($photoValue, FILTER_VALIDATE_URL)) {
            try {
                $this->stream(['output' => "<span style='color: #0ea5e9;'>[PHOTO]</span> Photo URL found: {$photoValue}\n"]);
                $storedPhotoPath = $this->downloadLecturerPhotoToStorage($photoValue, $sintaId);
                if ($storedPhotoPath) {
                    $photoValue = $storedPhotoPath;
                }
            } catch (\Throwable $photoError) {
                $this->stream(['output' => "<span class='text-warning-500'>[PHOTO - ERROR] Request timeout or failed request: " . addslashes($photoError->getMessage()) . "</span>\n"]);
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

        $postgraduateLecturer = PostgraduateLecturer::updateOrCreate(
            ['sinta_id' => $sintaId],
            [
                'name' => $r['nama'] ?? $r['name'] ?? null,
                'institution' => $r['institusi'] ?? $r['institution'] ?? $r['afiliasi'] ?? null,
                'profile_photo' => $photoValue,
            ]
        );

        if (! empty($studyProgramIds)) {
            $validStudyProgramIds = $this->resolveStudyProgramIds($studyProgramIds);
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

    private function resolveStudyProgramIds(?string $studyProgramIds): array
    {
        $selectedStudyProgramIds = collect(explode(',', (string) $studyProgramIds))
            ->map(fn ($studyProgramId) => trim((string) $studyProgramId))
            ->filter()
            ->unique()
            ->values();

        return StudyProgram::query()
            ->whereIn('id', $selectedStudyProgramIds)
            ->orWhereIn('id_unw_program_studi', $selectedStudyProgramIds)
            ->pluck('id')
            ->map(fn ($studyProgramId) => (int) $studyProgramId)
            ->unique()
            ->values()
            ->toArray();
    }

    private function downloadLecturerPhotoToStorage(string $photoUrl, string $sintaId): ?string
    {
        $response = Http::withoutVerifying()->timeout(15)->get($photoUrl);

        if (! $response->successful()) {
            $this->stream(['output' => "<span class='text-warning-500'>[PHOTO - WARN] Failed to download profile photo. HTTP status: " . $response->status() . ". Keeping the original URL.</span>\n"]);
            return null;
        }

        $photoName = $sintaId . '.jpg';
        $photoPath = $this->photoStorageDirectory . '/' . $photoName;

        Storage::disk('public')->makeDirectory($this->photoStorageDirectory);
        Storage::disk('public')->put($photoPath, $response->body());

        $this->stream(['output' => "<span class='text-success-400'>[PHOTO]</span> ✔ Profile photo saved to: storage/app/public/{$photoPath}\n"]);

        return $photoPath;
    }

    private function deleteImportedExcelFile(string $filePath): string
    {
        $fileName = basename($filePath);

        if (! file_exists($filePath)) {
            return "<span class='text-gray-400'>[CLEANUP]</span> File {$fileName} no longer exists in scripts/output.\n";
        }

        if (@unlink($filePath)) {
            return "<span class='text-success-400'>[CLEANUP]</span> File {$fileName} was deleted from scripts/output.\n";
        }

        Log::warning("Failed to delete imported Excel file: {$filePath}");

        return "<span class='text-warning-500'>[CLEANUP - WARN]</span> File {$fileName} was imported successfully, but could not be deleted. Check scripts/output file or folder permissions.\n";
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
}
