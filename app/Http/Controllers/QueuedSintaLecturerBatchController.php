<?php

namespace App\Http\Controllers;

use App\Jobs\FetchAllSintaLecturerDetailsJob;
use App\Jobs\ImportAllSintaLecturersJob;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use App\Models\SintaLecturerStudyProgramSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QueuedSintaLecturerBatchController extends Controller
{
    private const FETCH_PROGRESS_HISTORY_LIMIT = 300;

    public function fetchAll(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            if (! $this->batchTablesReady()) {
                $this->stream([
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Batch tables are not available. Run php artisan migrate first.\n",
                    'done' => true,
                ]);
                return;
            }

            if ($this->hasActiveFetchBatch()) {
                $this->stream([
                    'output' => "<span class='text-warning-500'>[QUEUE]</span> A fetch-all batch is already running or waiting. Do not start another one. Check progress or wait until it finishes.\n",
                    'done' => true,
                ]);
                return;
            }

            if (! Cache::add('sinta_lecturer_fetch_all_dispatch_lock', true, now()->addMinutes(2))) {
                $this->stream([
                    'output' => "<span class='text-warning-500'>[QUEUE]</span> Fetch-all dispatch is already queued. Please wait a moment.\n",
                    'done' => true,
                ]);
                return;
            }

            FetchAllSintaLecturerDetailsJob::dispatch();

            $this->stream([
                'output' => "<span class='text-success-400 font-bold'>[QUEUED]</span> Fetch All is now running in background queue. You may leave or refresh this page. Run <b>php artisan queue:work</b> if no progress appears.\n",
                'done' => true,
            ]);
        });
    }

    public function importAll(): StreamedResponse
    {
        return $this->streamResponse(function (): void {
            if (! $this->batchTablesReady()) {
                $this->stream([
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Batch tables are not available. Run php artisan migrate first.\n",
                    'done' => true,
                ]);
                return;
            }

            $batch = $this->latestBatch();

            if (! $batch) {
                $this->stream([
                    'output' => "<span class='text-danger-500'>[ERROR]</span> No fetch batch was found. Run Fetch All first.\n",
                    'done' => true,
                ]);
                return;
            }

            $summary = $this->batchReadinessSummary($batch);

            if ($summary['unfetched_count'] > 0) {
                $this->stream([
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because {$summary['unfetched_count']} SINTA lecturer(s) are not included in the latest fetch batch. Run Fetch All again first.\n",
                    'done' => true,
                ]);
                return;
            }

            if ($summary['failed_count'] > 0 || $summary['pending_count'] > 0 || $summary['processing_count'] > 0) {
                $this->stream([
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because the latest batch still has failed, pending, or processing items. Use Resume or Retry Failed first.\n",
                    'done' => true,
                ]);
                return;
            }

            if ($summary['missing_output_file_count'] > 0) {
                $this->stream([
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because {$summary['missing_output_file_count']} merged Excel file(s) are missing from scripts/output. Run Fetch All again first.\n",
                    'done' => true,
                ]);
                return;
            }

            if ($summary['missing_setting_count'] > 0) {
                $this->stream([
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because {$summary['missing_setting_count']} lecturer(s) do not have study program settings. Open Setting Prodi Fetch All first.\n",
                    'done' => true,
                ]);
                return;
            }

            if ($this->hasActiveImportBatch($batch)) {
                $this->stream([
                    'output' => "<span class='text-warning-500'>[QUEUE]</span> Import All is already queued or running for the latest batch.\n",
                    'done' => true,
                ]);
                return;
            }

            $batch->items()
                ->whereIn('status', ['success', 'success_with_warning'])
                ->whereIn('import_status', ['not_ready', 'ready', 'import_failed'])
                ->update([
                    'import_status' => 'queued',
                    'import_error' => null,
                ]);

            ImportAllSintaLecturersJob::dispatch((int) $batch->id);

            $this->stream([
                'output' => "<span class='text-success-400 font-bold'>[QUEUED]</span> Import All is now running in background queue. You may leave or refresh this page. Run <b>php artisan queue:work</b> if no progress appears.\n",
                'done' => true,
            ]);
        });
    }

    public function status(): JsonResponse
    {
        if (! $this->batchTablesReady()) {
            return response()->json([
                'batch' => null,
                'message' => 'Batch tables are not available. Run php artisan migrate first.',
            ], 503);
        }

        $batch = $this->latestBatch();

        if (! $batch) {
            return response()->json([
                'batch' => null,
                'message' => 'No fetch batch was found.',
            ]);
        }

        $fetchCounts = [
            'pending' => $batch->items()->where('status', 'pending')->count(),
            'processing' => $batch->items()->where('status', 'processing')->count(),
            'success' => $batch->items()->where('status', 'success')->count(),
            'success_with_warning' => $batch->items()->where('status', 'success_with_warning')->count(),
            'failed' => $batch->items()->where('status', 'failed')->count(),
        ];

        $importCounts = [
            'not_ready' => $batch->items()->where('import_status', 'not_ready')->count(),
            'ready' => $batch->items()->where('import_status', 'ready')->count(),
            'queued' => $batch->items()->where('import_status', 'queued')->count(),
            'importing' => $batch->items()->where('import_status', 'importing')->count(),
            'imported' => $batch->items()->where('import_status', 'imported')->count(),
            'import_failed' => $batch->items()->where('import_status', 'import_failed')->count(),
        ];

        $currentItem = $batch->current_sinta_id
            ? $batch->items()->where('sinta_id', $batch->current_sinta_id)->first(['id', 'sinta_id', 'lecturer_name', 'status', 'import_status', 'started_at', 'finished_at'])
            : null;

        $recentFetchItems = $batch->items()
            ->whereIn('status', ['success', 'success_with_warning', 'failed'])
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->limit(self::FETCH_PROGRESS_HISTORY_LIMIT)
            ->get(['id', 'sinta_id', 'lecturer_name', 'status', 'warning_message', 'error_message', 'finished_at'])
            ->reverse()
            ->values();

        $summary = $this->batchReadinessSummary($batch);
        $isFetchActive = $this->batchHasActiveFetchWork($batch, $fetchCounts);

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'is_fetch_active' => $isFetchActive,
                'total_items' => $batch->total_items,
                'processed_items' => $batch->processed_items,
                'success_items' => $batch->success_items,
                'warning_items' => $batch->warning_items,
                'failed_items' => $batch->failed_items,
                'current_sinta_id' => $batch->current_sinta_id,
                'current_lecturer_name' => $currentItem?->lecturer_name,
                'error_message' => $batch->error_message,
                'started_at' => optional($batch->started_at)->toDateTimeString(),
                'paused_at' => optional($batch->paused_at)->toDateTimeString(),
                'finished_at' => optional($batch->finished_at)->toDateTimeString(),
            ],
            'fetch_counts' => $fetchCounts,
            'import_counts' => $importCounts,
            'is_fetch_active' => $isFetchActive,
            'current_fetch_item' => $currentItem ? $this->fetchProgressItemPayload($currentItem) : null,
            'latest_fetch_item' => $recentFetchItems->last() ? $this->fetchProgressItemPayload($recentFetchItems->last()) : null,
            'recent_fetch_items' => $recentFetchItems
                ->map(fn (SintaLecturerFetchBatchItem $item): array => $this->fetchProgressItemPayload($item))
                ->values(),
            'summary' => $summary,
        ]);
    }

    private function fetchProgressItemPayload(SintaLecturerFetchBatchItem $item): array
    {
        $sintaId = (string) $item->sinta_id;
        $isCompleted = in_array($item->status, ['success', 'success_with_warning'], true);
        $outputFile = "merged_data_{$sintaId}.xlsx";
        $outputFileExists = $isCompleted && $this->mergedDetailFileExists($sintaId);

        return [
            'id' => $item->id,
            'sinta_id' => $sintaId,
            'lecturer_name' => $item->lecturer_name,
            'status' => $item->status,
            'output_file' => $isCompleted ? $outputFile : null,
            'output_file_exists' => $outputFileExists,
            'output_file_path' => $isCompleted ? "scripts/output/{$outputFile}" : null,
            'warning_message' => $item->warning_message,
            'error_message' => $item->error_message,
            'started_at' => optional($item->started_at)->toDateTimeString(),
            'finished_at' => optional($item->finished_at)->toDateTimeString(),
        ];
    }

    private function stream(array $payload): void
    {
        echo 'data: ' . json_encode($payload) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    private function streamResponse(callable $callback): StreamedResponse
    {
        return new StreamedResponse(function () use ($callback): void {
            $callback();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function hasActiveFetchBatch(): bool
    {
        $batch = $this->latestBatch();

        if (! $batch || ! in_array($batch->status, ['queued', 'running'], true)) {
            return false;
        }

        return $batch->items()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }

    private function batchHasActiveFetchWork(SintaLecturerFetchBatch $batch, array $fetchCounts): bool
    {
        if (in_array($batch->status, ['completed', 'paused', 'failed', 'cancelled'], true)) {
            return false;
        }

        return ((int) ($fetchCounts['pending'] ?? 0) > 0)
            || ((int) ($fetchCounts['processing'] ?? 0) > 0)
            || in_array($batch->status, ['queued', 'running'], true);
    }

    private function hasActiveImportBatch(SintaLecturerFetchBatch $batch): bool
    {
        return $batch->items()
            ->whereIn('import_status', ['queued', 'importing'])
            ->exists();
    }

    private function latestBatch(): ?SintaLecturerFetchBatch
    {
        return SintaLecturerFetchBatch::query()->latest('id')->first();
    }

    private function batchReadinessSummary(SintaLecturerFetchBatch $batch): array
    {
        $batchItemIds = $batch->items()->pluck('sinta_id')->filter()->values();
        $currentMasterIds = SintaLecturer::query()->pluck('sinta_id')->filter()->values();
        $successItems = $batch->items()->whereIn('status', ['success', 'success_with_warning'])->get(['sinta_id']);
        $settingIds = SintaLecturerStudyProgramSetting::query()
            ->whereIn('sinta_id', $successItems->pluck('sinta_id'))
            ->pluck('sinta_id')
            ->unique();
        $missingOutputFileCount = $successItems
            ->filter(fn (SintaLecturerFetchBatchItem $item): bool => ! $this->mergedDetailFileExists((string) $item->sinta_id))
            ->count();

        return [
            'ready_count' => $successItems->filter(fn ($item) => $settingIds->contains($item->sinta_id))->count(),
            'missing_setting_count' => $successItems->reject(fn ($item) => $settingIds->contains($item->sinta_id))->count(),
            'missing_output_file_count' => $missingOutputFileCount,
            'failed_count' => $batch->items()->where('status', 'failed')->count(),
            'pending_count' => $batch->items()->where('status', 'pending')->count(),
            'processing_count' => $batch->items()->where('status', 'processing')->count(),
            'unfetched_count' => $currentMasterIds->diff($batchItemIds)->count(),
        ];
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

    private function batchTablesReady(): bool
    {
        return Schema::hasTable('sinta_lecturer_fetch_batches')
            && Schema::hasTable('sinta_lecturer_fetch_batch_items')
            && Schema::hasTable('sinta_lecturer_study_program_settings');
    }
}
