<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class WarmSintaLecturerExcelProdiCacheJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public string $sintaId,
        public string $filePath,
        public string $fileTime,
    ) {}

    public function handle(): void
    {
        $lockKey = $this->cacheWarmLockKey();

        try {
            if (! file_exists($this->filePath)) {
                return;
            }

            if ((string) filemtime($this->filePath) !== $this->fileTime) {
                return;
            }

            $detectedStudyProgram = $this->readStudyProgramFromMergedExcelFile($this->filePath);

            Cache::put(
                $this->cacheKey(),
                is_string($detectedStudyProgram) ? trim($detectedStudyProgram) : '',
                now()->addDays(7),
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to warm SINTA lecturer Excel prodi cache.', [
                'sinta_id' => $this->sintaId,
                'file_path' => $this->filePath,
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

    private function readStudyProgramFromMergedExcelFile(string $filePath): ?string
    {
        $sheets = (new FastExcel())->importSheets($filePath);
        $rows = null;

        foreach ($sheets as $sheetName => $sheetRows) {
            $normalizedSheetName = Str::of((string) $sheetName)
                ->lower()
                ->replace([' ', '-'], '_')
                ->toString();

            if (str_contains($normalizedSheetName, 'data_dosen')) {
                $rows = collect($sheetRows);
                break;
            }
        }

        $rows ??= collect($sheets[0] ?? reset($sheets) ?: []);
        $firstRow = $rows->first();

        if (! $firstRow) {
            return null;
        }

        $row = array_change_key_case((array) $firstRow, CASE_LOWER);
        $value = $row['program studi']
            ?? $row['program_studi']
            ?? $row['study_program']
            ?? data_get(array_values((array) $firstRow), 2);
        $value = is_string($value) ? trim($value) : null;

        return $value !== '' ? $value : null;
    }
}
