<?php

namespace App\Jobs;

use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchAllSintaLecturerDetailsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;

    public int $tries = 5;

    public int $backoff = 10;

    public int $uniqueFor = 86400;

    public function uniqueId(): string
    {
        return 'sinta-lecturer-fetch-all';
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('sinta-lecturer-fetch-all'))->dontRelease()];
    }

    public function handle(): void
    {
        if ($this->shouldSkipDuplicateRun()) {
            return;
        }

        $batch = $this->createBatchFromMasterLecturers();

        if (! $batch) {
            Log::warning('[SINTA FETCH ALL] No SINTA lecturer records were found.');
            return;
        }

        $this->processBatchItems($batch, ['pending']);
    }

    private function shouldSkipDuplicateRun(): bool
    {
        $latestBatch = SintaLecturerFetchBatch::query()->latest('id')->first();

        if (! $latestBatch) {
            return false;
        }

        $hasActiveWork = in_array($latestBatch->status, ['queued', 'running'], true)
            && $latestBatch->items()->whereIn('status', ['pending', 'processing'])->exists();

        if ($hasActiveWork) {
            Log::warning('[SINTA FETCH ALL] Duplicate fetch-all job skipped because another batch is still active.', [
                'batch_id' => $latestBatch->id,
                'status' => $latestBatch->status,
            ]);

            return true;
        }

        return false;
    }

    private function createBatchFromMasterLecturers(): ?SintaLecturerFetchBatch
    {
        $lecturers = SintaLecturer::query()
            ->orderBy('name')
            ->get(['sinta_id', 'name']);

        if ($lecturers->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($lecturers): SintaLecturerFetchBatch {
            SintaLecturerFetchBatch::query()
                ->whereIn('status', ['pending', 'queued', 'running', 'paused', 'failed'])
                ->update([
                    'status' => 'cancelled',
                    'finished_at' => now(),
                    'error_message' => 'Superseded by a new queued fetch-all batch.',
                ]);

            $batch = SintaLecturerFetchBatch::create([
                'status' => 'running',
                'total_items' => $lecturers->count(),
                'processed_items' => 0,
                'success_items' => 0,
                'warning_items' => 0,
                'failed_items' => 0,
                'started_at' => now(),
                'error_message' => 'Queued fetch-all job is running in background.',
            ]);

            foreach ($lecturers as $lecturer) {
                $hasMergedFile = $this->mergedDetailFileExists((string) $lecturer->sinta_id);

                $batch->items()->create([
                    'sinta_id' => (string) $lecturer->sinta_id,
                    'lecturer_name' => $lecturer->name,
                    'status' => $hasMergedFile ? 'success' : 'pending',
                    'warning_message' => $hasMergedFile ? 'Existing merged detail Excel was found. Python scraper was skipped.' : null,
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

        $this->requeueSuccessfulItemsWithMissingMergedFiles($batch);

        $items = $batch->items()
            ->whereIn('status', $statuses)
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            $this->refreshBatchCounters($batch);
            $batch->update([
                'status' => 'completed',
                'finished_at' => now(),
                'current_sinta_id' => null,
                'error_message' => 'All lecturers already have merged Excel files. No Python scraper was executed.',
            ]);
            return;
        }

        $batch->update([
            'status' => 'running',
            'paused_at' => null,
            'error_message' => 'Queued fetch-all job is running in background.',
        ]);

        foreach ($items as $item) {
            if ($this->mergedDetailFileExists((string) $item->sinta_id)) {
                $item->update([
                    'status' => 'success',
                    'warning_message' => 'Existing merged detail Excel was found. Python scraper was skipped.',
                    'error_message' => null,
                    'finished_at' => now(),
                ]);
                $this->refreshBatchCounters($batch);
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

            $result = $this->fetchOneLecturerDetail($baseUrl, (string) $item->sinta_id);

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
                return;
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
        } else {
            $batch->update([
                'status' => 'paused',
                'error_message' => 'Batch stopped with remaining pending, processing, or failed items.',
            ]);
        }
    }

    private function requeueSuccessfulItemsWithMissingMergedFiles(SintaLecturerFetchBatch $batch): void
    {
        $items = $batch->items()
            ->whereIn('status', ['success', 'success_with_warning'])
            ->get(['id', 'sinta_id', 'status']);

        foreach ($items as $item) {
            if ($this->mergedDetailFileExists((string) $item->sinta_id)) {
                continue;
            }

            $item->update([
                'status' => 'pending',
                'warning_message' => 'Merged detail Excel was missing from scripts/output. Item was queued for re-scraping.',
                'error_message' => null,
                'finished_at' => null,
                'import_status' => 'not_ready',
                'import_error' => null,
            ]);
        }

        $this->refreshBatchCounters($batch);
    }

    private function fetchOneLecturerDetail(string $baseUrl, string $sintaId): array
    {
        $streamUrl = $baseUrl . "/api/scrape-detail/{$sintaId}";
        $logOutput = "[LARAVEL QUEUE] Connecting to Python scraper: {$streamUrl}\n";

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
            }

            return strlen($chunk);
        });

        $success = curl_exec($ch);

        if (! $success) {
            $curlError = curl_error($ch);
            $message = "[CURL ERROR] Failed to connect to the Docker Python scraper. URL: {$streamUrl}. Error: {$curlError}\n";
            Log::error($message);
            $logOutput .= $message;
        }

        curl_close($ch);

        $downloaded = false;

        if ($success) {
            $downloaded = $this->downloadMergedDetailExcel($baseUrl, $sintaId);
            $logOutput .= $downloaded
                ? "[LARAVEL QUEUE] merged_data_{$sintaId}.xlsx downloaded and verified successfully.\n"
                : "[LARAVEL QUEUE] merged_data_{$sintaId}.xlsx could not be downloaded or verified in scripts/output.\n";
        }

        return $this->classifyScraperOutput($logOutput, (bool) $success && $downloaded);
    }

    private function downloadMergedDetailExcel(string $baseUrl, string $sintaId): bool
    {
        $downloadUrl = $baseUrl . "/api/download-excel-detail/{$sintaId}";
        $fileResponse = Http::timeout(60)->get($downloadUrl);
        $isJsonError = str_contains((string) $fileResponse->header('Content-Type'), 'application/json')
            && data_get($fileResponse->json(), 'error');

        if (! $fileResponse->successful() || $isJsonError) {
            return false;
        }

        $excelPath = $this->mergedDetailFilePath($sintaId);

        if (! file_exists(dirname($excelPath))) {
            mkdir(dirname($excelPath), 0777, true);
        }

        $bytesWritten = file_put_contents($excelPath, $fileResponse->body());

        if ($bytesWritten === false) {
            Log::error("[SINTA FETCH ALL] Failed to write merged detail Excel file: {$excelPath}");

            return false;
        }

        return $this->mergedDetailFileExists($sintaId);
    }

    private function classifyScraperOutput(string $logOutput, bool $downloaded): array
    {
        $normalized = Str::of(strip_tags($logOutput))->lower()->toString();
        $fatalPatterns = ['traceback', 'gagal membuka halaman', 'httperror', 'status: 403', 'status: 404', 'status: 500', 'failed to connect to the docker python scraper', 'curl error', 'connection was interrupted', 'terjadi kesalahan fatal', '[fatal error]', 'sinta id tidak diberikan', 'exception'];

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
                'error_message' => 'Merged detail Excel was not downloaded or verified in scripts/output.',
                'warning_message' => null,
                'log_output' => $logOutput,
            ];
        }

        $warningPatterns = ['tidak ada publikasi', 'data scopus kosong/tidak ditemukan', 'data scholar kosong/tidak ditemukan', 'data garuda kosong/tidak ditemukan', 'data books kosong/tidak ditemukan', 'data services kosong/tidak ditemukan', 'data researches kosong/tidak ditemukan', "membuat sheet berisi 'none'", 'sheet contains', 'empty sheet', 'grafik garuda tidak ditemukan', 'gagal menemukan xaxis', 'gagal menemukan series', 'no valid data was processed'];

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

    private function mergedDetailFilePath(string $sintaId): string
    {
        return base_path("scripts/output/merged_data_{$sintaId}.xlsx");
    }

    private function mergedDetailFileExists(string $sintaId): bool
    {
        $filePath = $this->mergedDetailFilePath($sintaId);

        return file_exists($filePath) && is_file($filePath) && filesize($filePath) > 0;
    }
}
