<?php

namespace App\Http\Controllers;

use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SintaLecturerReadOnlyFetchMonitorController extends SintaLecturerFetchMonitorController
{
    /**
     * Watcher halaman hanya membaca status. Sinkronisasi program studi harus tetap
     * dimiliki oleh FetchAllSintaLecturerDetailsJob agar alur otomatis tidak balapan
     * dengan polling browser sebelum pengecekan kesiapan Import All dijalankan.
     */
    protected function ensureStudyProgramSync(SintaLecturerFetchBatch $batch): void
    {
        // Intentionally read-only.
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

        $mappedItems = $items->map(function (SintaLecturerFetchBatchItem $item) use ($settings, $masterLecturers): array {
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
}
