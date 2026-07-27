<?php

namespace App\Console\Commands;

use App\Filament\Resources\SintaLecturer\Pages\ImportSintaLecturers;
use App\Jobs\FetchAllSintaLecturerDetailsJob;
use App\Models\SintaLecturerFetchAllScheduleSetting;
use App\Models\SintaLecturerFetchBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RunScheduledSintaLecturerFetchAll extends Command
{
    private const STALE_ACTIVE_BATCH_MINUTES = 10;

    protected $signature = 'sinta:run-scheduled-fetch-all';

    protected $description = 'Dispatch automatic Fetch All for SINTA lecturers when the hardcoded timer has been reached.';

    public function handle(): int
    {
        if (! Schema::hasTable('sinta_lecturer_fetch_all_schedule_settings')) {
            $this->line('SINTA Fetch All schedule setting table is not ready.');

            return self::SUCCESS;
        }

        $setting = SintaLecturerFetchAllScheduleSetting::current();

        if (! $this->batchTablesReady()) {
            $reason = 'Skipped because SINTA Fetch All batch tables are not ready. Run php artisan migrate.';

            $setting->forceFill([
                'last_skip_reason' => $reason,
            ])->save();

            $this->warn($reason);

            return self::SUCCESS;
        }

        $now = now();
        $scheduledTime = ImportSintaLecturers::AUTO_FETCH_ALL_TIME;
        $scheduledAt = $this->scheduledAtForToday($scheduledTime);

        if ($now->lt($scheduledAt)) {
            return self::SUCCESS;
        }

        if ($this->hasAlreadyRunForCurrentSchedule($setting, $scheduledAt, $now)) {
            return self::SUCCESS;
        }

        $activeBatch = $this->activeFetchBatch();

        if ($activeBatch && ! $this->isActiveBatchStale($activeBatch, $now)) {
            $reason = "Skipped because Fetch All batch #{$activeBatch->id} is still {$activeBatch->status}.";

            $setting->forceFill([
                'last_run_at' => $now,
                'last_skip_reason' => $reason,
            ])->save();

            Log::warning('[SINTA SCHEDULED FETCH ALL] ' . $reason);
            $this->warn($reason);

            return self::SUCCESS;
        }

        if ($activeBatch) {
            $reason = "Fetch All batch #{$activeBatch->id} looked stale for at least " . self::STALE_ACTIVE_BATCH_MINUTES . ' minutes, so the scheduler cancelled it and queued a fresh Fetch All job.';
            $this->cancelStaleActiveBatch($activeBatch, $reason, $now);

            Log::warning('[SINTA SCHEDULED FETCH ALL] ' . $reason);
            $this->warn($reason);
        }

        FetchAllSintaLecturerDetailsJob::dispatch();

        $setting->forceFill([
            'last_run_at' => $now,
            'last_skip_reason' => null,
        ])->save();

        Log::info('[SINTA SCHEDULED FETCH ALL] Fetch All job dispatched by timer.', [
            'scheduled_time' => $scheduledTime,
            'scheduled_at' => $scheduledAt->toDateTimeString(),
            'dispatched_at' => $now->toDateTimeString(),
        ]);

        $this->info('Scheduled SINTA Fetch All job dispatched.');

        return self::SUCCESS;
    }

    private function scheduledAtForToday(string $scheduledTime)
    {
        [$hour, $minute] = array_pad(array_map('intval', explode(':', $scheduledTime)), 2, 0);

        return now()->setTime($hour, $minute);
    }

    private function hasAlreadyRunForCurrentSchedule(SintaLecturerFetchAllScheduleSetting $setting, $scheduledAt, $now): bool
    {
        if (! $setting->last_run_at) {
            return false;
        }

        $lastRunAt = $setting->last_run_at->copy()->timezone((string) config('app.timezone'));

        return $lastRunAt->toDateString() === $now->toDateString()
            && $lastRunAt->greaterThanOrEqualTo($scheduledAt);
    }

    private function activeFetchBatch(): ?SintaLecturerFetchBatch
    {
        return SintaLecturerFetchBatch::query()
            ->whereIn('status', ['queued', 'running'])
            ->whereHas('items', function ($query): void {
                $query->whereIn('status', ['pending', 'processing']);
            })
            ->latest('id')
            ->first();
    }

    private function isActiveBatchStale(SintaLecturerFetchBatch $batch, $now): bool
    {
        $processingItem = $batch->items()
            ->where('status', 'processing')
            ->orderBy('started_at')
            ->first(['started_at']);

        $referenceAt = $processingItem?->started_at ?? $batch->started_at;

        if (! $referenceAt) {
            return false;
        }

        return $referenceAt->diffInMinutes($now) >= self::STALE_ACTIVE_BATCH_MINUTES;
    }

    private function cancelStaleActiveBatch(SintaLecturerFetchBatch $batch, string $reason, $now): void
    {
        $batch->items()
            ->whereIn('status', ['pending', 'processing'])
            ->update([
                'status' => 'failed',
                'error_message' => $reason,
                'finished_at' => $now,
            ]);

        $batch->forceFill([
            'status' => 'cancelled',
            'processed_items' => $batch->items()->whereIn('status', ['success', 'success_with_warning', 'failed'])->count(),
            'success_items' => $batch->items()->where('status', 'success')->count(),
            'warning_items' => $batch->items()->where('status', 'success_with_warning')->count(),
            'failed_items' => $batch->items()->where('status', 'failed')->count(),
            'current_sinta_id' => null,
            'finished_at' => $now,
            'error_message' => $reason,
        ])->save();
    }

    private function batchTablesReady(): bool
    {
        return Schema::hasTable('sinta_lecturer_fetch_batches')
            && Schema::hasTable('sinta_lecturer_fetch_batch_items');
    }
}
