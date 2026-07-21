<?php

namespace App\Jobs;

use App\Http\Controllers\BulkSintaLecturerController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportAllSintaLecturersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;

    public int $tries = 1;

    public function middleware(): array
    {
        return [new WithoutOverlapping('sinta-lecturer-import-all')];
    }

    public function handle(): void
    {
        Log::info('[SINTA IMPORT ALL] Background import all job started.');

        app(BulkSintaLecturerController::class)
            ->importAll()
            ->sendContent();

        Log::info('[SINTA IMPORT ALL] Background import all job finished.');
    }
}
