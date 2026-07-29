<?php

namespace App\Http\Controllers;

use App\Filament\Resources\SintaLecturer\Services\SintaLecturerMergedStudyProgramSyncer;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SintaLecturerFetchMonitorController extends Controller
{
    private const FETCH_PROGRESS_HISTORY_LIMIT = 300;

    public function __construct(
        protected SintaLecturerMergedStudyProgramSyncer $syncer,
    ) {}

    public function status(Request $request): JsonResponse
    {
        if (! $this->batchTablesReady()) {
            return response()->json([
                'batch' => null,
                'message' => 'Batch tables are not available. Run php artisan migrate first.',
            ], 503);
        }

        $batch = $this->resolveBatch($request);

        if (! $batch) {
            return response()->json([
                'batch' => null,
                'message' => 'No fetch batch was found.',
            ]);
        }

        $this->ensureStudyProgramSync($batch);
        $batch->refresh();

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
            ? $batch->items()
                ->where('sinta_id', $batch->current_sinta_id)
                ->first(['id', 'sinta_id', 'lecturer_name', 'status', 'import_status', 'started_at', 'finished_at'])
            : null;

        $recentFetchItems = $batch->items()
            ->whereIn('status', ['success', 'success_with_warning', 'failed'])
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->orderByDesc('id')
            ->limit(self::FETCH_PROGRESS_HISTORY_LIMIT)
            ->get(['id', 'sinta_id', 'lecturer_name', 'status', 'warning_message', 'error_message', 'started_at', 'finished_at'])
            ->reverse()
            ->values();

        $isFetchActive = $this->batchHasActiveFetchWork($batch, $fetchCounts);
        $syncFinished = $this->studyProgramSyncFinished($batch);

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'is_fetch_active' => $isFetchActive,
                'study_program_sync_finished' => $syncFinished,
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
            'study_program_sync_finished' => $syncFinished,
            'current_fetch_item' => $currentItem ? $this->fetchProgressItemPayload($currentItem) : null,
            'latest_fetch_item' => $recentFetchItems->last() ? $this->fetchProgressItemPayload($recentFetchItems->last()) : null,
            'recent_fetch_items' => $recentFetchItems
                ->map(fn (SintaLecturerFetchBatchItem $item): array => $this->fetchProgressItemPayload($item))
                ->values(),
            'summary' => $this->batchReadinessSummary($batch),
        ]);
    }

    public function studyProgramSettings(Request $request): JsonResponse
    {
        if (! $this->batchTablesReady()) {
            return response()->json([
                'batch' => null,
                'programs' => [],
                'items' => [],
                'summary' => $this->emptySummary(),
                'message' => 'Batch tables are not available. Run php artisan migrate first.',
            ], 503);
        }

        $batch = $this->resolveBatch($request);
        $programModels = StudyProgram::query()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get();
        $programs = $programModels
            ->map(fn (StudyProgram $program): array => [
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
                'summary' => $this->emptySummary(),
            ]);
        }

        $this->ensureStudyProgramSync($batch);
        $batch->refresh();

        $items = $batch->items()
            ->orderBy('lecturer_name')
            ->orderBy('sinta_id')
            ->get();
        $sintaIds = $items->pluck('sinta_id')->filter()->values();
        $masterLecturers = SintaLecturer::query()
            ->whereIn('sinta_id', $sintaIds)
            ->get(['sinta_id', 'name'])
            ->keyBy('sinta_id');
        $settings = SintaLecturerStudyProgramSetting::query()
            ->whereIn('sinta_id', $sintaIds)
            ->get()
            ->groupBy('sinta_id');
        $syncFinished = $this->studyProgramSyncFinished($batch);

        $mappedItems = $items->map(function (SintaLecturerFetchBatchItem $item) use ($settings, $masterLecturers, $syncFinished): array {
            $sintaId = (string) $item->sinta_id;
            $filePath = $this->mergedDetailFilePath($sintaId);
            $rawStudyProgram = $this->syncer->readStudyProgramFromMergedExcelFile($filePath);
            $resolved = $this->syncer->resolveFromRawStudyProgram($rawStudyProgram);
            $expectedIds = collect($resolved['study_program_ids'])
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->unique()
                ->values();
            $storedRecords = $settings->get($sintaId, collect());
            $storedIds = $storedRecords
                ->pluck('study_program_id')
                ->filter(fn ($id): bool => $id !== null)
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->unique()
                ->values();

            // Perbaiki otomatis data lama yang masih menyimpan PAUD dan SD sekaligus.
            if ($syncFinished
                && $resolved['strict_target'] !== null
                && $storedIds->sort()->values()->all() !== $expectedIds->sort()->values()->all()) {
                $this->syncer->syncFromRawStudyProgram($sintaId, $rawStudyProgram);
                $storedIds = $expectedIds;
                $storedRecords = collect([true]);
            }

            $hasStoredSetting = $storedRecords->isNotEmpty();
            $suggestedIds = $hasStoredSetting ? collect() : $expectedIds;
            $selectedIds = $hasStoredSetting ? $storedIds : $suggestedIds;
            $canSet = in_array($item->status, ['success', 'success_with_warning'], true);
            $name = $item->lecturer_name ?: data_get($masterLecturers->get($sintaId), 'name');

            return [
                'sinta_id' => $sintaId,
                'lecturer_name' => $name ?: '-',
                'fetch_status' => $item->status,
                'import_status' => $item->import_status,
                'warning_message' => $item->warning_message,
                'error_message' => $item->error_message,
                'can_set_program' => $canSet,
                'detected_study_program' => $rawStudyProgram,
                'study_program_ids' => $selectedIds->values(),
                'setting_status' => $canSet
                    ? ($hasStoredSetting ? 'complete' : ($suggestedIds->isNotEmpty() ? 'auto_suggested' : 'not_set'))
                    : 'blocked',
            ];
        })->values();

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'study_program_sync_finished' => $syncFinished,
                'total_items' => $batch->total_items,
                'processed_items' => $batch->processed_items,
                'success_items' => $batch->success_items,
                'warning_items' => $batch->warning_items,
                'failed_items' => $batch->failed_items,
                'error_message' => $batch->error_message,
            ],
            'programs' => $programs,
            'items' => $mappedItems,
            'summary' => $this->batchReadinessSummary($batch, $mappedItems),
        ]);
    }

    protected function ensureStudyProgramSync(SintaLecturerFetchBatch $batch): void
    {
        $batch->refresh();

        if ($batch->status !== 'completed' || $this->studyProgramSyncFinished($batch)) {
            return;
        }

        $lock = Cache::lock("sinta-fetch-prodi-sync-v2:{$batch->id}", 300);

        if (! $lock->get()) {
            return;
        }

        try {
            $batch->refresh();

            if ($this->studyProgramSyncFinished($batch)) {
                return;
            }

            $summary = [
                'matched' => 0,
                'empty' => 0,
                'unmatched' => 0,
                'failed' => 0,
                'skipped' => 0,
            ];

            $items = $batch->items()
                ->whereIn('status', ['success', 'success_with_warning'])
                ->orderBy('id')
                ->get(['id', 'sinta_id']);

            foreach ($items as $item) {
                $sintaId = (string) $item->sinta_id;
                $filePath = $this->mergedDetailFilePath($sintaId);

                if (! $this->mergedDetailFileExists($sintaId)) {
                    $summary['skipped']++;
                    continue;
                }

                try {
                    $result = $this->syncer->syncFromMergedExcel($sintaId, $filePath);
                    $status = (string) ($result['status'] ?? 'unmatched');

                    if (array_key_exists($status, $summary)) {
                        $summary[$status]++;
                    }
                } catch (\Throwable $exception) {
                    $summary['failed']++;
                    Log::warning('[SINTA FETCH MONITOR] Failed to sync lecturer study program.', [
                        'batch_id' => $batch->id,
                        'sinta_id' => $sintaId,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            $batch->forceFill([
                'error_message' => 'Fetch All completed. Study program settings synced v2 from merged Excel. Matched: '
                    . $summary['matched']
                    . ', empty: ' . $summary['empty']
                    . ', unmatched: ' . $summary['unmatched']
                    . ', failed: ' . $summary['failed']
                    . ', skipped missing file: ' . $summary['skipped']
                    . '.',
            ])->save();
        } finally {
            $lock->release();
        }
    }

    protected function resolveBatch(Request $request): ?SintaLecturerFetchBatch
    {
        $batchId = (int) $request->query('batch_id', 0);

        return $batchId > 0
            ? SintaLecturerFetchBatch::query()->find($batchId)
            : SintaLecturerFetchBatch::query()->latest('id')->first();
    }

    protected function studyProgramSyncFinished(SintaLecturerFetchBatch $batch): bool
    {
        return $batch->status === 'completed'
            && Str::contains(Str::lower((string) $batch->error_message), 'study program settings synced v2');
    }

    protected function batchHasActiveFetchWork(SintaLecturerFetchBatch $batch, array $counts): bool
    {
        if (in_array($batch->status, ['completed', 'paused', 'failed', 'cancelled'], true)) {
            return false;
        }

        return (int) ($counts['pending'] ?? 0) > 0
            || (int) ($counts['processing'] ?? 0) > 0
            || in_array($batch->status, ['queued', 'running'], true);
    }

    protected function fetchProgressItemPayload(SintaLecturerFetchBatchItem $item): array
    {
        $sintaId = (string) $item->sinta_id;
        $isCompleted = in_array($item->status, ['success', 'success_with_warning'], true);
        $outputFile = "merged_data_{$sintaId}.xlsx";

        return [
            'id' => $item->id,
            'sinta_id' => $sintaId,
            'lecturer_name' => $item->lecturer_name,
            'status' => $item->status,
            'output_file' => $isCompleted ? $outputFile : null,
            'output_file_exists' => $isCompleted && $this->mergedDetailFileExists($sintaId),
            'output_file_path' => $isCompleted ? "scripts/output/{$outputFile}" : null,
            'warning_message' => $item->warning_message,
            'error_message' => $item->error_message,
            'started_at' => optional($item->started_at)->toDateTimeString(),
            'finished_at' => optional($item->finished_at)->toDateTimeString(),
        ];
    }

    protected function batchReadinessSummary(SintaLecturerFetchBatch $batch, ?Collection $mappedItems = null): array
    {
        $batchItemIds = $batch->items()->pluck('sinta_id')->filter()->values();
        $currentMasterIds = SintaLecturer::query()->pluck('sinta_id')->filter()->values();

        if ($mappedItems) {
            $readyCount = $mappedItems->filter(fn ($item): bool => ! empty(data_get($item, 'study_program_ids', [])))->count();
            $missingSettingCount = $mappedItems->filter(fn ($item): bool => empty(data_get($item, 'study_program_ids', [])))->count();
        } else {
            $successIds = $batch->items()
                ->whereIn('status', ['success', 'success_with_warning'])
                ->pluck('sinta_id');
            $readyIds = SintaLecturerStudyProgramSetting::query()
                ->whereIn('sinta_id', $successIds)
                ->whereNotNull('study_program_id')
                ->pluck('sinta_id')
                ->unique();
            $readyCount = $readyIds->count();
            $missingSettingCount = $successIds->unique()->diff($readyIds)->count();
        }

        return [
            'ready_count' => $readyCount,
            'missing_setting_count' => $missingSettingCount,
            'failed_count' => $batch->items()->where('status', 'failed')->count(),
            'pending_count' => $batch->items()->where('status', 'pending')->count(),
            'processing_count' => $batch->items()->where('status', 'processing')->count(),
            'unfetched_count' => $currentMasterIds->diff($batchItemIds)->count(),
        ];
    }

    protected function emptySummary(): array
    {
        return [
            'ready_count' => 0,
            'missing_setting_count' => 0,
            'failed_count' => 0,
            'pending_count' => 0,
            'processing_count' => 0,
            'unfetched_count' => SintaLecturer::query()->count(),
        ];
    }

    protected function mergedDetailFilePath(string $sintaId): string
    {
        return base_path("scripts/output/merged_data_{$sintaId}.xlsx");
    }

    protected function mergedDetailFileExists(string $sintaId): bool
    {
        $path = $this->mergedDetailFilePath($sintaId);

        return file_exists($path) && filesize($path) > 0;
    }

    protected function batchTablesReady(): bool
    {
        return Schema::hasTable('sinta_lecturer_fetch_batches')
            && Schema::hasTable('sinta_lecturer_fetch_batch_items')
            && Schema::hasTable('sinta_lecturer_study_program_settings');
    }
}
