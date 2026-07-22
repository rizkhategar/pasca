<?php

namespace App\Http\Controllers;

use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SimpleSintaFetchAllController extends Controller
{
    public function fetchAll(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            if (! $this->batchTablesReady()) {
                $this->stream(['output' => "[ERROR] Batch fetch tables are missing. Run: php artisan migrate\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $batch = $this->createBatchFromMasterLecturers();

            if (! $batch) {
                $this->stream(['output' => "[WARN] No SINTA lecturer records were found. Run Sync SINTA Lecturers first.\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $this->processBatchItems($batch);
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

            $this->stream(['output' => "[ERROR] PYTHON_SCRAPER_URL is empty. Set PYTHON_SCRAPER_URL in .env.\n"]);
            $this->stream(['done' => true]);
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
                'error_message' => null,
            ]);

            $this->stream(['output' => "[DONE] Semua dosen sudah memiliki file merged. Tidak ada scraping baru.\n"]);
            $this->stream(['done' => true]);
            return;
        }

        foreach ($items as $item) {
            $sintaId = (string) $item->sinta_id;
            $lecturerName = trim((string) $item->lecturer_name) ?: '-';

            if ($this->mergedDetailFileExists($sintaId)) {
                $item->update([
                    'status' => 'success',
                    'warning_message' => 'Existing merged detail Excel was found. Scraper was skipped.',
                    'error_message' => null,
                    'finished_at' => now(),
                ]);

                $this->refreshBatchCounters($batch);
                $this->stream(['output' => "[DONE] SINTA ID {$sintaId} - {$lecturerName}. File already exists: merged_data_{$sintaId}.xlsx\n"]);
                continue;
            }

            $batch->update([
                'status' => 'running',
                'current_sinta_id' => $sintaId,
                'error_message' => null,
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

            $this->stream(['output' => "[RUN] SINTA ID {$sintaId} - {$lecturerName} run\n"]);

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

                $this->stream(['output' => "[FAILED] SINTA ID {$sintaId} - {$lecturerName}. {$result['error_message']}\n"]);
                $this->stream(['done' => true]);
                return;
            }

            $label = $result['status'] === 'success_with_warning' ? 'DONE WITH WARNING' : 'DONE';
            $this->stream(['output' => "[{$label}] SINTA ID {$sintaId} - {$lecturerName}. File made: merged_data_{$sintaId}.xlsx\n"]);
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

            $this->stream(['output' => "[DONE] Fetch All selesai. Semua data selesai diproses.\n"]);
        } else {
            $batch->update([
                'status' => 'paused',
                'error_message' => 'Batch stopped with remaining pending, processing, or failed items.',
            ]);

            $this->stream(['output' => "[PAUSED] Fetch All berhenti karena masih ada item pending/failed.\n"]);
        }

        $this->stream(['done' => true]);
    }

    private function fetchOneLecturerDetail(string $baseUrl, string $sintaId): array
    {
        $streamUrl = $baseUrl . "/api/scrape-detail/{$sintaId}";
        $logOutput = "[LARAVEL] Connecting to Python scraper: {$streamUrl}\n";

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
        $this->keepAlive($ch);

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
                ? "[LARAVEL] merged_data_{$sintaId}.xlsx downloaded successfully.\n"
                : "[LARAVEL] merged_data_{$sintaId}.xlsx could not be downloaded.\n";
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
                $sintaId = (string) $lecturer->sinta_id;
                $hasMergedFile = $this->mergedDetailFileExists($sintaId);

                $batch->items()->create([
                    'sinta_id' => $sintaId,
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

    private function batchTablesReady(): bool
    {
        return Schema::hasTable('sinta_lecturer_fetch_batches')
            && Schema::hasTable('sinta_lecturer_fetch_batch_items')
            && Schema::hasTable('sinta_lecturer_study_program_settings');
    }

    private function mergedDetailFilePath(string $sintaId): string
    {
        return base_path("scripts/output/merged_data_{$sintaId}.xlsx");
    }

    private function mergedDetailFileExists(string $sintaId): bool
    {
        return file_exists($this->mergedDetailFilePath($sintaId));
    }
}
