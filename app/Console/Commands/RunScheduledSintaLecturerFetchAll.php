<?php

namespace App\Console\Commands;

use App\Filament\Resources\SintaLecturer\Pages\ImportSintaLecturers;
use App\Jobs\FetchAllSintaLecturerDetailsJob;
use App\Models\SintaLecturerAutomaticRun;
use App\Models\SintaLecturerFetchAllScheduleSetting;
use App\Models\SintaLecturerFetchBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RunScheduledSintaLecturerFetchAll extends Command
{
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
            $reason = 'Skipped because SINTA Fetch All automatic tables are not ready. Run php artisan migrate.';

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

        $this->purgeOldAutomaticRunLogs($now->toDateString());

        $refreshMessage = $this->cancelRefreshableFetchBatches($now);
        $this->releaseFetchAllOverlapLocks();

        if ($refreshMessage) {
            Log::warning('[SINTA SCHEDULED FETCH ALL] ' . $refreshMessage);
            $this->warn($refreshMessage);
        }

        $automaticRun = SintaLecturerAutomaticRun::updateOrCreate(
            [
                'run_date' => $now->toDateString(),
                'scheduled_time' => $scheduledTime,
            ],
            [
                'fetch_batch_id' => null,
                'status' => 'queued',
                'phase' => 'fetch',
                'fetch_started_at' => null,
                'fetch_finished_at' => null,
                'import_started_at' => null,
                'import_finished_at' => null,
                'failed_sinta_ids' => null,
                'missing_study_program_sinta_ids' => null,
                'error_message' => null,
                'summary_message' => "import & fetch automatic {$now->toDateString()} [queued]",
            ]
        );

        FetchAllSintaLecturerDetailsJob::dispatch((int) $automaticRun->id);

        $setting->forceFill([
            'last_run_at' => $now,
            'last_skip_reason' => null,
        ])->save();

        Log::info('[SINTA SCHEDULED FETCH ALL] Fetch All job dispatched by timer.', [
            'automatic_run_id' => $automaticRun->id,
            'scheduled_time' => $scheduledTime,
            'scheduled_at' => $scheduledAt->toDateTimeString(),
            'dispatched_at' => $now->toDateTimeString(),
        ]);

        $this->info('Scheduled SINTA Fetch All job dispatched. A fresh batch will be built and existing merged Excel files will be skipped.');

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

    private function cancelRefreshableFetchBatches($now): ?string
    {
        $batches = SintaLecturerFetchBatch::query()
            ->whereIn('status', ['queued', 'running', 'paused', 'failed'])
            ->latest('id')
            ->get();

        if ($batches->isEmpty()) {
            return null;
        }

        $message = 'Superseded by an automatic fresh Fetch All batch. Existing merged Excel files will be skipped in the new batch.';

        foreach ($batches as $batch) {
            $batch->items()
                ->whereIn('status', ['pending', 'processing'])
                ->update([
                    'status' => 'failed',
                    'error_message' => $message,
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
                'error_message' => $message,
            ])->save();
        }

        return $batches->count() . ' previous fetch batch(es) were cancelled before automatic Fetch All queued a fresh batch.';
    }

    private function releaseFetchAllOverlapLocks(): void
    {
        foreach ($this->fetchAllOverlapLockNames() as $lockName) {
            try {
                Cache::lock($lockName)->forceRelease();
            } catch (Throwable) {
                // Some cache drivers may not support forceRelease for a missing lock.
            }
        }
    }

    private function fetchAllOverlapLockNames(): array
    {
        return [
            'sinta-lecturer-fetch-all',
            'laravel-queue-overlap:sinta-lecturer-fetch-all',
            'laravel-queue-overlap:' . FetchAllSintaLecturerDetailsJob::class . ':sinta-lecturer-fetch-all',
        ];
    }

    private function purgeOldAutomaticRunLogs(string $today): void
    {
        $deletedCount = SintaLecturerAutomaticRun::query()
            ->where('run_date', '<', $today)
            ->delete();

        if ($deletedCount > 0) {
            Log::info('[SINTA SCHEDULED FETCH ALL] Old automatic run logs were purged before today scheduled fetch.', [
                'today' => $today,
                'deleted_count' => $deletedCount,
            ]);
        }
    }

    private function batchTablesReady(): bool
    {
        return Schema::hasTable('sinta_lecturer_fetch_batches')
            && Schema::hasTable('sinta_lecturer_fetch_batch_items')
            && Schema::hasTable('sinta_lecturer_study_program_settings')
            && Schema::hasTable('sinta_lecturer_automatic_runs');
    }
}
