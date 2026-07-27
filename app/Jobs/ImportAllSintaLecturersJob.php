<?php

namespace App\Jobs;

use App\Http\Controllers\BulkSintaLecturerController;
use App\Models\SintaLecturerAutomaticRun;
use App\Models\SintaLecturerFetchBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportAllSintaLecturersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;

    public int $tries = 5;

    public int $backoff = 10;

    public int $uniqueFor = 86400;

    public function __construct(public int $batchId, public ?int $automaticRunId = null)
    {
    }

    public function uniqueId(): string
    {
        return 'sinta-lecturer-import-all-' . $this->batchId;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('sinta-lecturer-import-all'))->dontRelease()];
    }

    public function handle(): void
    {
        $latestBatchId = SintaLecturerFetchBatch::query()->latest('id')->value('id');
        $automaticRun = $this->automaticRun();

        if ((int) $latestBatchId !== $this->batchId) {
            $message = 'Import job skipped because another fetch batch became the latest batch.';
            $this->failAutomaticRun($automaticRun, $message);

            Log::warning('[SINTA IMPORT ALL] ' . $message, [
                'job_batch_id' => $this->batchId,
                'latest_batch_id' => $latestBatchId,
            ]);

            return;
        }

        $automaticRun?->forceFill([
            'status' => 'importing',
            'phase' => 'import',
            'import_started_at' => $automaticRun->import_started_at ?: now(),
            'summary_message' => 'import & fetch automatic ' . $this->automaticRunDate($automaticRun) . ' [import running]',
        ])->save();

        Log::info('[SINTA IMPORT ALL] Background import all job started.', [
            'batch_id' => $this->batchId,
            'automatic_run_id' => $automaticRun?->id,
        ]);

        try {
            app(BulkSintaLecturerController::class)
                ->importAll()
                ->sendContent();

            $this->finishAutomaticRun($automaticRun);
        } catch (\Throwable $e) {
            $this->failAutomaticRun($automaticRun, $e->getMessage());
            throw $e;
        }

        Log::info('[SINTA IMPORT ALL] Background import all job finished.', [
            'batch_id' => $this->batchId,
            'automatic_run_id' => $automaticRun?->id,
        ]);
    }

    private function automaticRun(): ?SintaLecturerAutomaticRun
    {
        if (! $this->automaticRunId) {
            return null;
        }

        return SintaLecturerAutomaticRun::query()->find($this->automaticRunId);
    }

    private function finishAutomaticRun(?SintaLecturerAutomaticRun $automaticRun): void
    {
        if (! $automaticRun) {
            return;
        }

        $batch = SintaLecturerFetchBatch::query()->find($this->batchId);
        $date = $this->automaticRunDate($automaticRun);
        $failedIds = $batch
            ? $batch->items()->where('import_status', 'import_failed')->pluck('sinta_id')->filter()->map(fn ($id) => (string) $id)->values()->all()
            : [];

        if ($failedIds !== []) {
            $automaticRun->forceFill([
                'status' => 'failed',
                'phase' => 'failed',
                'import_finished_at' => now(),
                'failed_sinta_ids' => $failedIds,
                'error_message' => 'Some SINTA lecturers failed during Import All.',
                'summary_message' => 'import & fetch automatic ' . $date . ' [failed] : ' . implode(', ', $failedIds),
            ])->save();

            return;
        }

        $automaticRun->forceFill([
            'status' => 'done',
            'phase' => 'done',
            'import_finished_at' => now(),
            'failed_sinta_ids' => null,
            'missing_study_program_sinta_ids' => null,
            'error_message' => null,
            'summary_message' => 'import & fetch automatic ' . $date . ' [done]',
        ])->save();
    }

    private function failAutomaticRun(?SintaLecturerAutomaticRun $automaticRun, string $message): void
    {
        if (! $automaticRun) {
            return;
        }

        $date = $this->automaticRunDate($automaticRun);

        $automaticRun->forceFill([
            'status' => 'failed',
            'phase' => 'failed',
            'import_finished_at' => now(),
            'error_message' => $message,
            'summary_message' => 'import & fetch automatic ' . $date . ' [failed] : ' . $message,
        ])->save();
    }

    private function automaticRunDate(SintaLecturerAutomaticRun $automaticRun): string
    {
        return optional($automaticRun->run_date)->toDateString() ?: now()->toDateString();
    }
}
