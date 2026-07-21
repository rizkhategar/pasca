<?php

namespace App\Jobs;

use App\Http\Controllers\BulkSintaLecturerController;
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

    public function __construct(public int $batchId)
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

        if ((int) $latestBatchId !== $this->batchId) {
            Log::warning('[SINTA IMPORT ALL] Import job skipped because another fetch batch became the latest batch.', [
                'job_batch_id' => $this->batchId,
                'latest_batch_id' => $latestBatchId,
            ]);

            return;
        }

        Log::info('[SINTA IMPORT ALL] Background import all job started.', ['batch_id' => $this->batchId]);

        app(BulkSintaLecturerController::class)
            ->importAll()
            ->sendContent();

        Log::info('[SINTA IMPORT ALL] Background import all job finished.', ['batch_id' => $this->batchId]);
    }
}
