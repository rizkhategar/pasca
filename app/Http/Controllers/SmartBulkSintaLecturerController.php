<?php

namespace App\Http\Controllers;

use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SmartBulkSintaLecturerController extends Controller
{
    private function stream(array $payload): void
    {
        echo 'data: ' . json_encode($payload) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    public function fetchAll(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            if (! $this->batchTablesReady()) {
                $this->streamMissingMigrationMessage();
                return;
            }

            $batch = $this->createBatchFromMasterLecturers();

            if (! $batch) {
                $this->stream(['output' => "<span class='text-warning-500'>[WARN]</span> No SINTA lecturer records were found. Run Step 1 sync first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $skipped = $batch->items()->whereIn('status', ['success', 'success_with_warning'])->count();
            $pending = $batch->items()->where('status', 'pending')->count();

            $this->stream(['output' => "[BATCH] Created fetch batch #{$batch->id} with {$batch->total_items} lecturer(s).\n"]);
            $this->stream(['output' => "[BATCH] Existing merged Excel files detected: {$skipped}. Pending scraper jobs: {$pending}.\n"]);
            $this->processBatchItems($batch, ['pending']);
        });
    }

    public function resume(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            if (! $this->batchTablesReady()) {
                $this->streamMissingMigrationMessage();
                return;
            }

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
            if (! $this->batchTablesReady()) {
                $this->streamMissingMigrationMessage();
                return;
            }

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
                if ($this->mergedDetailFileExists($item->sinta_id)) {
                    $item->update([
                        'status' => 'success',
                        'error_message' => null,
                        'warning_message' => 'Existing merged detail Excel was found. Scraper retry was skipped.',
                        'import_status' => 'not_ready',
                        'import_error' => null,
                        'finished_at' => now(),
                        'retry_count' => $item->retry_count + 1,
                    ]);
                    continue;
                }

                $item->update([
                    'status' => 'pending',
                    'error_message' => null,
                    'warning_message' => null,
                    'import_status' => 'not_ready',
                    'import_error' => null,
                    'retry_count' => $item->retry_count + 1,
                ]);
            }

            $this->refreshBatchCounters($batch);
            $this->stream(['output' => "[BATCH] Retrying failed item(s) from batch #{$batch->id}. Items with existing merged files are marked ready without scraping.\n"]);
            $this->processBatchItems($batch->fresh(), ['pending']);
        });
    }

    public function reset(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            if (! $this->batchTablesReady()) {
                $this->streamMissingMigrationMessage();
                return;
            }

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
        if (! $this->batchTablesReady()) {
            return response()->json([
                'batch' => null,
                'programs' => [],
                'items' => [],
                'summary' => [
                    'ready_count' => 0,
                    'missing_setting_count' => 0,
                    'failed_count' => 0,
                    'unfetched_count' => SintaLecturer::query()->count(),
                ],
                'message' => 'Batch tables are not available. Run php artisan migrate first.',
            ], 503);
        }

        $batch = $this->latestBatch();
        $programs = $this->allStudyProgramsQuery()
            ->get()
            ->map(fn (StudyProgram $program) => [
                'id' => (int) $program->id,
                'display_name' => $program->display_name,
                'nama' => $program->nama,
                'jenjang' => $program->jenjang,
                'jenjang_nama_singkat' => $program->jenjang_nama_singkat,
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

        $masterLecturers = SintaLecturer::query()
            ->whereIn('sinta_id', $items->pluck('sinta_id')->filter()->values())
            ->get(['sinta_id', 'name'])
            ->keyBy('sinta_id');

        $settings = SintaLecturerStudyProgramSetting::query()
            ->whereIn('sinta_id', $items->pluck('sinta_id')->filter()->values())
            ->get()
            ->groupBy('sinta_id');

        $programModels = $this->allStudyProgramsQuery()->get();

        $mappedItems = $items->map(function (SintaLecturerFetchBatchItem $item) use ($settings, $masterLecturers, $programModels) {
            $existing = $settings->get($item->sinta_id, collect())
                ->pluck('study_program_id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $detectedStudyProgram = $this->readStudyProgramFromMergedExcel($item->sinta_id);
            $suggested = $existing->isEmpty()
                ? $this->suggestStudyProgramIds($detectedStudyProgram, $programModels)
                : collect();

            $selected = $existing->isNotEmpty() ? $existing : $suggested;
            $canSet = in_array($item->status, ['success', 'success_with_warning'], true);
            $name = $item->lecturer_name ?: data_get($masterLecturers->get($item->sinta_id), 'name');

            return [
                'sinta_id' => (string) $item->sinta_id,
                'lecturer_name' => $name ?: '-',
                'fetch_status' => $item->status,
                'import_status' => $item->import_status,
                'warning_message' => $item->warning_message,
                'error_message' => $item->error_message,
                'can_set_program' => $canSet,
                'detected_study_program' => $detectedStudyProgram,
                'study_program_ids' => $selected->map(fn ($id) => (int) $id)->values(),
                'setting_status' => $canSet
                    ? ($existing->isNotEmpty() ? 'complete' : ($suggested->isNotEmpty() ? 'auto_suggested' : 'not_set'))
                    : 'blocked',
            ];
        })->values();

        $summary = $this->batchReadinessSummary($batch, $mappedItems);

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
        if (! $this->batchTablesReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Batch tables are not available. Run php artisan migrate first.',
            ], 503);
        }

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

                $validStudyProgramIds = $this->allStudyProgramsQuery()
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
                ->update([
                    'status' => 'cancelled',
                    'finished_at' => now(),
                    'error_message' => 'Superseded by a new fetch-all batch.',
                ]);

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
                $hasMergedFile = $this->mergedDetailFileExists($lecturer->sinta_id);

                $batch->items()->create([
                    'sinta_id' => (string) $lecturer->sinta_id,
                    'lecturer_name' => $lecturer->name,
                    'status' => $hasMergedFile ? 'success' : 'pending',
                    'warning_message' => $hasMergedFile ? 'Existing merged detail Excel was found. Scraper was skipped.' : null,
                    'import_status' => 'not_ready',
                    'finished_at' => $hasMergedFile ? now() : null,
                ]);
            }

            $this->refreshBatchCounters($batch);

            return $batch->fresh();
        });
    }

    private function processBatchItems(SintaLecturerFetchBatch $batch, array $statuses): void
    {
        $baseUrl = rtrim((string) config('services.python_scraper.url'), '/');
        $items = $batch->items()->whereIn('status', $statuses)->orderBy('id')->get();

        if ($items->isEmpty()) {
            $this->refreshBatchCounters($batch);
            $batch->update([
                'status' => 'completed',
                'finished_at' => now(),
                'current_sinta_id' => null,
                'error_message' => null,
            ]);
            $this->stream(['output' => "<span class='text-success-400'>[SKIP]</span> All lecturers already have merged Excel files. No Python scraper was executed.\n"]);
            $this->stream(['done' => true]);
            return;
        }

        $batch->update(['status' => 'running', 'paused_at' => null]);

        foreach ($items as $item) {
            if ($this->mergedDetailFileExists($item->sinta_id)) {
                $item->update([
                    'status' => 'success',
                    'warning_message' => 'Existing merged detail Excel was found. Scraper was skipped.',
                    'error_message' => null,
                    'finished_at' => now(),
                ]);
                $this->refreshBatchCounters($batch);
                $this->stream(['output' => "<span class='text-success-400'>[SKIP]</span> {$item->lecturer_name} ({$item->sinta_id}) already has merged_data_{$item->sinta_id}.xlsx.\n"]);
                continue;
            }

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

            $this->stream(['output' => $result['status'] === 'success_with_warning'
                ? "<span class='text-warning-500'>[WARNING]</span> Fetch completed with empty-data warnings. This lecturer can still be imported after prodi setting.\n"
                : "<span class='text-success-400'>[OK]</span> Fetch completed successfully.\n"]);
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
            $this->stream(['output' => "\n<span class='text-success-400 font-bold'>[BATCH COMPLETED]</span> All lecturers are ready. Open Setting Prodi Fetch All before Import All.\n"]);
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

        $excelPath = $this->mergedDetailFilePath($sintaId);

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
        $fatalPatterns = ['traceback', 'gagal membuka halaman', 'httperror', 'status: 403', 'status: 404', 'status: 500', 'failed to connect to the docker python scraper', 'curl error', 'connection was interrupted', 'terjadi kesalahan fatal', '[fatal error]', 'sinta id tidak diberikan', 'exception'];

        foreach ($fatalPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return ['status' => 'failed', 'error_message' => "Fatal scraper pattern detected: {$pattern}", 'warning_message' => null, 'log_output' => $logOutput];
            }
        }

        if (! $downloaded) {
            return ['status' => 'failed', 'error_message' => 'Merged detail Excel was not downloaded. The scraper did not finish successfully.', 'warning_message' => null, 'log_output' => $logOutput];
        }

        $warningPatterns = ['tidak ada publikasi', 'data scopus kosong/tidak ditemukan', 'data scholar kosong/tidak ditemukan', 'data garuda kosong/tidak ditemukan', 'data books kosong/tidak ditemukan', 'data services kosong/tidak ditemukan', 'data researches kosong/tidak ditemukan', "membuat sheet berisi 'none'", 'sheet contains', 'empty sheet', 'grafik garuda tidak ditemukan', 'gagal menemukan xaxis', 'gagal menemukan series', 'no valid data was processed'];

        foreach ($warningPatterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return ['status' => 'success_with_warning', 'error_message' => null, 'warning_message' => "Empty-data warning detected: {$pattern}", 'log_output' => $logOutput];
            }
        }

        return ['status' => 'success', 'error_message' => null, 'warning_message' => null, 'log_output' => $logOutput];
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

    private function batchReadinessSummary(SintaLecturerFetchBatch $batch, ?Collection $mappedItems = null): array
    {
        $batchItemIds = $batch->items()->pluck('sinta_id')->filter()->values();
        $currentMasterIds = SintaLecturer::query()->pluck('sinta_id')->filter()->values();
        $successItems = $mappedItems ?? $batch->items()->whereIn('status', ['success', 'success_with_warning'])->get();

        return [
            'ready_count' => $successItems->filter(fn ($item) => ! empty(data_get($item, 'study_program_ids', [])))->count(),
            'missing_setting_count' => $successItems->filter(fn ($item) => empty(data_get($item, 'study_program_ids', [])))->count(),
            'failed_count' => $batch->items()->where('status', 'failed')->count(),
            'pending_count' => $batch->items()->where('status', 'pending')->count(),
            'processing_count' => $batch->items()->where('status', 'processing')->count(),
            'unfetched_count' => $currentMasterIds->diff($batchItemIds)->count(),
        ];
    }

    private function allStudyProgramsQuery()
    {
        return StudyProgram::query()->orderBy('jenjang')->orderBy('nama');
    }

    private function mergedDetailFilePath(string $sintaId): string
    {
        return base_path("scripts/output/merged_data_{$sintaId}.xlsx");
    }

    private function mergedDetailFileExists(string $sintaId): bool
    {
        return file_exists($this->mergedDetailFilePath($sintaId));
    }

    private function readStudyProgramFromMergedExcel(string $sintaId): ?string
    {
        $filePath = $this->mergedDetailFilePath($sintaId);

        if (! file_exists($filePath)) {
            return null;
        }

        try {
            $sheets = (new FastExcel())->importSheets($filePath);
            $rows = collect($sheets[0] ?? $sheets['DATA_DOSEN'] ?? []);
            $firstRow = $rows->first();

            if (! $firstRow) {
                return null;
            }

            $row = array_change_key_case((array) $firstRow, CASE_LOWER);
            $value = $row['program studi'] ?? $row['program_studi'] ?? $row['study_program'] ?? data_get(array_values((array) $firstRow), 2);
            $value = is_string($value) ? trim($value) : null;

            return $value !== '' ? $value : null;
        } catch (\Throwable $exception) {
            Log::warning("Failed to read study program from merged Excel {$filePath}: {$exception->getMessage()}");
            return null;
        }
    }

    private function suggestStudyProgramIds(?string $rawStudyProgram, Collection $programs): Collection
    {
        if (! $rawStudyProgram) {
            return collect();
        }

        $parsed = $this->parseExternalStudyProgram($rawStudyProgram);
        $externalLevel = $this->canonicalLevel($parsed['level']);
        $externalName = $this->normalizeStudyProgramText($parsed['name']);
        $externalTokens = $this->studyProgramTokens($externalName);

        $scored = $programs->map(function (StudyProgram $program) use ($externalLevel, $externalName, $externalTokens) {
            $programLevel = $this->canonicalLevel($program->jenjang_nama_singkat ?: $program->jenjang);
            $programName = $this->normalizeStudyProgramText((string) $program->nama);
            $programDisplay = $this->normalizeStudyProgramText((string) $program->display_name);
            $programTokens = $this->studyProgramTokens($programName . ' ' . $programDisplay);
            $score = 0;

            if ($externalLevel && $programLevel && $externalLevel === $programLevel) $score += 100;
            elseif ($externalLevel && $programLevel && $externalLevel !== $programLevel) $score -= 40;
            if ($externalName !== '' && ($externalName === $programName || $externalName === $programDisplay)) $score += 90;
            if ($externalName !== '' && $programName !== '' && (str_contains($externalName, $programName) || str_contains($programName, $externalName))) $score += 70;
            if ($externalName !== '' && $programDisplay !== '' && (str_contains($externalName, $programDisplay) || str_contains($programDisplay, $externalName))) $score += 50;
            $score += $externalTokens->intersect($programTokens)->count() * 25;

            return ['id' => (int) $program->id, 'score' => $score];
        })->filter(fn (array $item) => $item['score'] >= 80)->sortByDesc('score')->values();

        if ($scored->isEmpty()) return collect();

        $bestScore = (int) $scored->first()['score'];

        return $scored->filter(fn (array $item) => (int) $item['score'] === $bestScore)->pluck('id')->take(2)->values();
    }

    private function parseExternalStudyProgram(string $raw): array
    {
        $raw = trim($raw);
        $level = null;
        $name = $raw;

        if (preg_match('/^\s*(.*?)\s*[-–—]\s*(.+)$/u', $raw, $matches)) {
            $level = trim($matches[1]);
            $name = trim($matches[2]);
        }

        if (! $level && preg_match('/\b(S1|S2|S3|D3|D4|Profesi|Sarjana|Magister|Diploma\s*3|Diploma\s*4)\b/i', $raw, $matches)) {
            $level = $matches[1];
        }

        return ['level' => $level, 'name' => $name];
    }

    private function canonicalLevel(?string $value): ?string
    {
        $value = Str::of((string) $value)->lower()->ascii()->toString();
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = trim($value);

        return match (true) {
            str_contains($value, 's1') || str_contains($value, 'sarjana') => 's1',
            str_contains($value, 's2') || str_contains($value, 'magister') => 's2',
            str_contains($value, 's3') || str_contains($value, 'doktor') => 's3',
            str_contains($value, 'd3') || str_contains($value, 'diploma 3') => 'd3',
            str_contains($value, 'd4') || str_contains($value, 'diploma 4') => 'd4',
            str_contains($value, 'profesi') => 'profesi',
            default => null,
        };
    }

    private function normalizeStudyProgramText(string $value): string
    {
        $value = Str::of($value)->lower()->ascii()->toString();
        $value = str_replace(['&', '/', '-', '_'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]+/', ' ', $value);
        $value = preg_replace('/\b(s1|s2|s3|d3|d4|sarjana|magister|diploma|program|studi)\b/', ' ', $value);
        $value = preg_replace('/\b(pendidikan\s+profesi|pendidikan|profesi)\b/', ' ', $value);
        $value = preg_replace('/\bbidan\b/', ' kebidanan ', $value);
        $value = preg_replace('/\bilmu\b/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function studyProgramTokens(string $value): Collection
    {
        return collect(explode(' ', $value))->map(fn ($token) => trim($token))->filter(fn ($token) => $token !== '' && strlen($token) > 2)->unique()->values();
    }

    private function batchTablesReady(): bool
    {
        return Schema::hasTable('sinta_lecturer_fetch_batches')
            && Schema::hasTable('sinta_lecturer_fetch_batch_items')
            && Schema::hasTable('sinta_lecturer_study_program_settings');
    }

    private function streamMissingMigrationMessage(): void
    {
        $this->stream(['output' => "<span class='text-danger-500 font-bold'>[ERROR]</span> Batch fetch tables are missing. Run: php artisan migrate\n"]);
        $this->stream(['done' => true]);
    }

    private function cleanSintaId(?string $value): ?string
    {
        $cleaned = preg_replace('/[^0-9]/', '', (string) $value);

        return $cleaned !== '' ? $cleaned : null;
    }
}
