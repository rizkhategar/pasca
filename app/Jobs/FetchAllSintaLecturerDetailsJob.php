<?php

namespace App\Jobs;

use App\Http\Controllers\SmartBulkSintaLecturerController;
use App\Models\SintaLecturerFetchBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        if ($this->hasActiveFetchBatch()) {
            Log::warning('[SINTA FETCH ALL] Background fetch all job skipped because a batch is already active.');
            return;
        }

        Log::info('[SINTA FETCH ALL] Background fetch all job started.');

        app(SmartBulkSintaLecturerController::class)
            ->fetchAll()
            ->sendContent();

        Log::info('[SINTA FETCH ALL] Background fetch all job finished.');
    }

    private function hasActiveFetchBatch(): bool
    {
        $batch = SintaLecturerFetchBatch::query()->latest('id')->first();

        if (! $batch || ! in_array($batch->status, ['queued', 'running'], true)) {
            return false;
        }

        return $batch->items()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }
}
