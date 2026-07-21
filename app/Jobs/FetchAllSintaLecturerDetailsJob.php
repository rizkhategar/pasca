<?php

namespace App\Jobs;

use App\Http\Controllers\SmartBulkSintaLecturerController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchAllSintaLecturerDetailsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 0;

    public int $tries = 5;

    public int $backoff = 10;

    public int $uniqueFor = 86400;

    public function uniqueId(): string
    {
        return 'sinta-lecturer-fetch-all';
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('sinta-lecturer-fetch-all'))->dontRelease()];
    }

    public function handle(): void
    {
        Log::info('[SINTA FETCH ALL] Background fetch all job started.');

        app(SmartBulkSintaLecturerController::class)
            ->fetchAll()
            ->sendContent();

        Log::info('[SINTA FETCH ALL] Background fetch all job finished.');
    }
}
