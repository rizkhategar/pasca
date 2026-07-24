<?php

namespace App\Console\Commands;

use App\Jobs\FetchAllSintaLecturerDetailsJob;
use App\Models\SintaLecturerFetchAllScheduleSetting;
use App\Models\SintaLecturerFetchBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RunScheduledSintaLecturerFetchAll extends Command
{
    protected $signature = 'sinta:run-scheduled-fetch-all';

    protected $description = 'Dispatch automatic Fetch All for SINTA lecturers when the saved timer matches the current time.';

    public function handle(): int
    {
        if (! Schema::hasTable('sinta_lecturer_fetch_all_schedule_settings')) {
            $this->line('SINTA Fetch All schedule setting table is not ready.');

            return self::SUCCESS;
        }

        $setting = SintaLecturerFetchAllScheduleSetting::current();

        if (! $setting->is_enabled || ! $setting->scheduled_time) {
            return self::SUCCESS;
        }

        $now = now();
        $scheduledTime = $setting->formattedTime();

        if ($now->format('H:i') !== $scheduledTime) {
            return self::SUCCESS;
        }

        if ($setting->last_run_at && $setting->last_run_at->format('Y-m-d H:i') === $now->format('Y-m-d H:i')) {
            return self::SUCCESS;
        }

        $activeBatch = null;

        if (Schema::hasTable('sinta_lecturer_fetch_batches')) {
            $activeBatch = SintaLecturerFetchBatch::query()
                ->whereIn('status', ['queued', 'running'])
                ->latest('id')
                ->first();
        }

        if ($activeBatch) {
            $reason = "Skipped because Fetch All batch #{$activeBatch->id} is still {$activeBatch->status}.";

            $setting->forceFill([
                'last_run_at' => $now,
                'last_skip_reason' => $reason,
            ])->save();

            Log::warning('[SINTA SCHEDULED FETCH ALL] ' . $reason);
            $this->warn($reason);

            return self::SUCCESS;
        }

        FetchAllSintaLecturerDetailsJob::dispatch();

        $setting->forceFill([
            'last_run_at' => $now,
            'last_skip_reason' => null,
        ])->save();

        Log::info('[SINTA SCHEDULED FETCH ALL] Fetch All job dispatched by timer.', [
            'scheduled_time' => $scheduledTime,
            'dispatched_at' => $now->toDateTimeString(),
        ]);

        $this->info('Scheduled SINTA Fetch All job dispatched.');

        return self::SUCCESS;
    }
}
