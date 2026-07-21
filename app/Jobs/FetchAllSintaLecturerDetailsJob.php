<?php

namespace App\Jobs;

use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchAllSintaLecturerDetailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;

    public int $tries = 5;

    public int $backoff = 10;

    public function middleware(): array
    {
        return [(new WithoutOverlapping('sinta-lecturer-fetch-all'))->dontRelease()];
    }

    public function handle(): void
    {
        Log::info('[SINTA FETCH ALL] Background fetch all job started.');

        $batch = $this->createBatchFromMasterLecturers();

        if (! $batch) {
            Log::warning('[SINTA FETCH ALL] No SINTA lecturer records were found.');
            return;
        }

        $this->processBatchItems($batch);

        Log::info('[SINTA FETCH ALL] Background fetch all job finished.', [
            'batch_id' => $batch->id,
        ]);
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
                    'error_message' => 'Superseded by a queued fetch-all batch.',
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
                $sintaId = (string) $lecturer->sinta_id;
                $hasMergedFile = $this->mergedDetailFileExists($sintaId);

                $batch->items()->create([
                    'sinta_id' => $sintaId,
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

    private function processBatchItems(SintaLecturerFetchBatch $batch): void
    {
        $baseUrl = rtrim((string) config('services.python_scraper.url'), '/');

        if ($baseUrl === '') {
            $batch->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => 'PYTHON_SCRAPER_URL is empty. Set PYTHON_SCRAPER_URL in .env.',
            ]);

            Log::error('[SINTA FETCH ALL] PYTHON_SCRAPER_URL is empty.');
            return;
        }

        $items = $batch->items()
            ->where('status', 'pending')
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

        foreach ($items as $item) {
            $sintaId = (string) $item->sinta_id;

            if ($this->mergedDetailFileExists($sintaId)) {
                $item->update([
                    'status' => 'success',
                    'warning_message' => 'Existing merged detail Excel was found. Python scraper was skipped.',
                    'error_message' => null,
                    'finished_at' => now(),
                ]);

                $this->refreshBatchCounters($batch);
                continue;
            }

            $batch->update([
                'status' => 'running',
                'current_sinta_id' => $sintaId,
                'error_message' => 'Queued fetch-all job is running in background.',
            ]);

            $item->update([
                'status' => 'processing',
                'started_at' => now(),
                'finished_at' => null,
                'error_message' => null,
                'warning_message' => null,
                'import_status' => 'not_ready',
                'import_error' => null,
            ]);

            Log::info('[SINTA FETCH ALL] Scraping lecturer detail.', [
                'batch_id' => $batch->id,
                'sinta_id' => $sintaId,
                'lecturer_name' => $item->lecturer_name,
            ]);

            $result = $this->fetchOneLecturerDetail($baseUrl, $sintaId);

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
                    'current_sinta_id' => $sintaId,
                ]);

                Log::error('[SINTA FETCH ALL] Batch paused because a lecturer failed.', [
                    'batch_id' => $batch->id,
                    'sinta_id' => $sintaId,
                    'error_message' => $result['error_message'],
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
            $message = "[CURL ERROR] Failed to connect to the Python scraper. URL: {$streamUrl}. Error: {$curlError}\n";
            Log::error($message);
            $logOutput .= $message;
        }

        curl_close($ch);

        $downloaded = false;

        if ($success) {
            $downloaded = $this->downloadMergedDetailExcel($baseUrl, $sintaId);
            $logOutput .= $downloaded
                ? "[LARAVEL QUEUE] merged_data_{$sintaId}.xlsx downloaded successfully.\n"
                : "[LARAVEL QUEUE] merged_data_{$sintaId}.xlsx could not be downloaded.\n";
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

        file_put_contents($excelPath, $fileResponse->body());

        return $this->mergedDetailFileExists($sintaId);
    }

    private function classifyScraperOutput(string $logOutput, bool $downloaded): array
    {
        $normalized = Str::of(strip_tags($logOutput))->lower()->toString();
        $fatalPatterns = ['traceback', 'gagal membuka halaman', 'httperror', 'status: 403', 'status: 404', 'status: 500', 'failed to connect to the python scraper', 'curl error', 'connection was interrupted', 'terjadi kesalahan fatal', '[fatal error]', 'sinta id tidak diberikan', 'exception'];

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
        $path = $this->mergedDetailFilePath($sintaId);

        return file_exists($path) && filesize($path) > 0;
    }
}
