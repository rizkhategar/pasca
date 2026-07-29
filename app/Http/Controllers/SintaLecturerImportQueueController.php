<?php

namespace App\Http\Controllers;

use App\Jobs\ImportAllSintaLecturersJob;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerFetchBatchItem;
use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SintaLecturerImportQueueController extends Controller
{
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

            $batch = SintaLecturerFetchBatch::query()->latest('id')->first();

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
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because the latest batch still has failed, pending, or processing items. Run Fetch All again first.\n",
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
                    'output' => "<span class='text-danger-500'>[ERROR]</span> Import All is blocked because {$summary['missing_setting_count']} lecturer(s) do not have valid study program settings. Open Setting Prodi Fetch All first and select missing prodi.\n",
                    'done' => true,
                ]);

                return;
            }

            $importingCount = $batch->items()->where('import_status', 'importing')->count();

            if ($importingCount > 0) {
                $this->stream([
                    'output' => "<span class='text-warning-500'>[RUNNING]</span> Import All is currently processing {$importingCount} lecturer(s). The existing import watcher will continue monitoring it.\n",
                    'done' => true,
                ]);

                return;
            }

            $queuedCount = $batch->items()->where('import_status', 'queued')->count();

            if ($queuedCount === 0) {
                $batch->items()
                    ->whereIn('status', ['success', 'success_with_warning'])
                    ->whereIn('import_status', ['not_ready', 'ready', 'import_failed'])
                    ->update([
                        'import_status' => 'queued',
                        'import_error' => null,
                    ]);

                $queuedCount = $batch->items()->where('import_status', 'queued')->count();
            } else {
                $this->stream([
                    'output' => "<span class='text-warning-500 font-bold'>[RECOVER]</span> Found {$queuedCount} lecturer(s) left in queued status. Import All will be dispatched again using the latest batch.\n",
                ]);
            }

            if ($queuedCount === 0) {
                $this->stream([
                    'output' => "<span class='text-gray-400'>[INFO]</span> No lecturer is waiting to be imported in the latest batch.\n",
                    'done' => true,
                ]);

                return;
            }

            ImportAllSintaLecturersJob::dispatch((int) $batch->id);

            $this->stream([
                'output' => "<span class='text-success-400 font-bold'>[QUEUED]</span> Import All batch #{$batch->id} was dispatched for {$queuedCount} lecturer(s). Run <b>php artisan queue:work</b> if no progress appears.\n",
                'done' => true,
            ]);
        });
    }

    protected function batchReadinessSummary(SintaLecturerFetchBatch $batch): array
    {
        $batchItemIds = $batch->items()->pluck('sinta_id')->filter()->values();
        $currentMasterIds = SintaLecturer::query()->pluck('sinta_id')->filter()->values();
        $successItems = $batch->items()
            ->whereIn('status', ['success', 'success_with_warning'])
            ->get(['sinta_id']);
        $settingIds = SintaLecturerStudyProgramSetting::query()
            ->whereIn('sinta_id', $successItems->pluck('sinta_id'))
            ->whereNotNull('study_program_id')
            ->whereIn('study_program_id', StudyProgram::query()->select('id'))
            ->pluck('sinta_id')
            ->unique();
        $missingOutputFileCount = $successItems
            ->filter(fn (SintaLecturerFetchBatchItem $item): bool => ! $this->mergedDetailFileExists((string) $item->sinta_id))
            ->count();

        return [
            'missing_setting_count' => $successItems->reject(fn ($item) => $settingIds->contains($item->sinta_id))->count(),
            'missing_output_file_count' => $missingOutputFileCount,
            'failed_count' => $batch->items()->where('status', 'failed')->count(),
            'pending_count' => $batch->items()->where('status', 'pending')->count(),
            'processing_count' => $batch->items()->where('status', 'processing')->count(),
            'unfetched_count' => $currentMasterIds->diff($batchItemIds)->count(),
        ];
    }

    protected function mergedDetailFileExists(string $sintaId): bool
    {
        $path = base_path("scripts/output/merged_data_{$sintaId}.xlsx");

        return file_exists($path) && filesize($path) > 0;
    }

    protected function batchTablesReady(): bool
    {
        return Schema::hasTable('sinta_lecturer_fetch_batches')
            && Schema::hasTable('sinta_lecturer_fetch_batch_items')
            && Schema::hasTable('sinta_lecturer_study_program_settings');
    }

    protected function stream(array $payload): void
    {
        echo 'data: ' . json_encode($payload) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    protected function streamResponse(callable $callback): StreamedResponse
    {
        return new StreamedResponse(function () use ($callback): void {
            $callback();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
