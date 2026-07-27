<?php

namespace App\Http\Controllers;

use App\Models\SintaLecturerAutomaticRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class SintaLecturerAutomaticRunController extends Controller
{
    public function latest(): JsonResponse
    {
        if (! Schema::hasTable('sinta_lecturer_automatic_runs')) {
            return response()->json([
                'logs' => [],
                'message' => 'Automatic run log table is not available. Run php artisan migrate first.',
            ]);
        }

        $latestRun = SintaLecturerAutomaticRun::query()
            ->latest('id')
            ->first(['id', 'run_date', 'scheduled_time', 'status', 'phase', 'summary_message', 'error_message', 'updated_at']);

        if (! $latestRun) {
            return response()->json([
                'logs' => [],
            ]);
        }

        return response()->json([
            'logs' => [[
                'id' => $latestRun->id,
                'run_date' => optional($latestRun->run_date)->toDateString(),
                'scheduled_time' => $latestRun->scheduled_time,
                'status' => $latestRun->status,
                'phase' => $latestRun->phase,
                'summary_message' => $latestRun->summary_message,
                'error_message' => $latestRun->error_message,
                'updated_at' => optional($latestRun->updated_at)->toDateTimeString(),
            ]],
        ]);
    }
}
