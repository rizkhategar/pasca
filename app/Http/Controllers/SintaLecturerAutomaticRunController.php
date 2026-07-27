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

        $logs = SintaLecturerAutomaticRun::query()
            ->latest('id')
            ->limit(10)
            ->get(['id', 'run_date', 'scheduled_time', 'status', 'phase', 'summary_message', 'error_message', 'updated_at'])
            ->reverse()
            ->map(fn (SintaLecturerAutomaticRun $run): array => [
                'id' => $run->id,
                'run_date' => optional($run->run_date)->toDateString(),
                'scheduled_time' => $run->scheduled_time,
                'status' => $run->status,
                'phase' => $run->phase,
                'summary_message' => $run->summary_message,
                'error_message' => $run->error_message,
                'updated_at' => optional($run->updated_at)->toDateTimeString(),
            ])
            ->values();

        return response()->json([
            'logs' => $logs,
        ]);
    }
}
