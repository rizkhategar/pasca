<?php

namespace App\Filament\Resources\SintaLecturer\Services;

use App\Jobs\WarmSintaLecturerExcelProdiCacheJob;
use App\Models\SintaLecturerFetchBatch;
use App\Models\SintaLecturerStudyProgramSetting;
use Illuminate\Support\Facades\Cache;

class SintaLecturerStudyProgramCacheWarmer
{
    public function __construct(
        private readonly SintaLecturerStudyProgramDetector $detector,
    ) {}

    /**
     * Warm cache deteksi prodi untuk batch terakhir.
     * Nama job lama dipertahankan agar integrasi existing tetap aman,
     * tetapi isi job sekarang membaca sinta_lecturers.department, bukan Excel.
     */
    public function queueForLatestBatch(int $limit = 100): void
    {
        $batch = SintaLecturerFetchBatch::query()->latest('id')->first();

        if (! $batch) {
            return;
        }

        $batchLockKey = "sinta_import_excel_prodi_cache_batch_warm:{$batch->id}";

        if (! Cache::add($batchLockKey, true, now()->addMinutes(30))) {
            return;
        }

        $batch->items()
            ->whereIn('status', ['success', 'success_with_warning'])
            ->whereNotIn('sinta_id', SintaLecturerStudyProgramSetting::query()->select('sinta_id'))
            ->orderByDesc('finished_at')
            ->orderBy('id')
            ->limit($limit)
            ->pluck('sinta_id')
            ->filter()
            ->each(fn ($sintaId) => $this->queueForSintaId((string) $sintaId));
    }

    public function queueForSintaId(string $sintaId): void
    {
        if ($this->hasStoredStudyProgramSetting($sintaId)) {
            return;
        }

        $sourceVersion = $this->departmentSourceVersion($sintaId);
        $cacheKey = $this->detectedStudyProgramCacheKey($sintaId, $sourceVersion);

        if (Cache::has($cacheKey)) {
            return;
        }

        $lockKey = $this->detectedStudyProgramCacheWarmLockKey($sintaId, $sourceVersion);

        if (! Cache::add($lockKey, true, now()->addMinutes(15))) {
            return;
        }

        WarmSintaLecturerExcelProdiCacheJob::dispatch($sintaId, '', $sourceVersion);
    }

    public function detectedStudyProgramCacheKey(string $sintaId, string $sourceVersion): string
    {
        return "sinta_import_detected_study_program:{$sintaId}:{$sourceVersion}";
    }

    public function detectedStudyProgramCacheWarmLockKey(string $sintaId, string $sourceVersion): string
    {
        return "sinta_import_detected_study_program_warming:{$sintaId}:{$sourceVersion}";
    }

    public function departmentSourceVersion(string $sintaId): string
    {
        $rawDepartment = $this->detector->detectRawDepartment($sintaId) ?? '';

        return 'department:' . md5($rawDepartment);
    }

    private function hasStoredStudyProgramSetting(string $sintaId): bool
    {
        return SintaLecturerStudyProgramSetting::query()
            ->where('sinta_id', $sintaId)
            ->exists();
    }
}
