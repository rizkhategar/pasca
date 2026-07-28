<?php

namespace App\Jobs;

use App\Filament\Resources\SintaLecturer\Services\SintaLecturerStudyProgramDetector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WarmSintaLecturerExcelProdiCacheJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * Nama job masih dipertahankan untuk kompatibilitas kode lama.
     * Sumber deteksi sekarang bukan Excel, melainkan sinta_lecturers.department.
     */
    public function __construct(
        public string $sintaId,
        public string $filePath,
        public string $fileTime,
    ) {}

    public function handle(SintaLecturerStudyProgramDetector $detector): void
    {
        $lockKey = $this->cacheWarmLockKey();

        try {
            $detectedStudyProgram = $detector->detectRawDepartment($this->sintaId);

            Cache::put(
                $this->cacheKey(),
                is_string($detectedStudyProgram) ? trim($detectedStudyProgram) : '',
                now()->addDays(7),
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to warm SINTA lecturer department prodi cache.', [
                'sinta_id' => $this->sintaId,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            Cache::forget($lockKey);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Cache::forget($this->cacheWarmLockKey());
    }

    private function cacheKey(): string
    {
        return "sinta_import_detected_study_program:{$this->sintaId}:{$this->fileTime}";
    }

    private function cacheWarmLockKey(): string
    {
        return "sinta_import_detected_study_program_warming:{$this->sintaId}:{$this->fileTime}";
    }
}
