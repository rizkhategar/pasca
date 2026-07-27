<?php

namespace App\Filament\Resources\SintaLecturer\Pages\Concerns;

use App\Filament\Resources\SintaLecturer\Services\SintaLecturerStudyProgramCacheWarmer;
use App\Filament\Resources\SintaLecturer\Services\SintaLecturerStudyProgramDetector;
use Illuminate\Support\Collection;

trait HasBulkProdiSettingAction
{
    protected function bulkProdiStudyProgramDetector(): SintaLecturerStudyProgramDetector
    {
        return app(SintaLecturerStudyProgramDetector::class);
    }

    protected function bulkProdiStudyProgramCacheWarmer(): SintaLecturerStudyProgramCacheWarmer
    {
        return app(SintaLecturerStudyProgramCacheWarmer::class);
    }

    protected function detectStudyProgramFromSintaDepartment(string $sintaId): ?string
    {
        return $this->bulkProdiStudyProgramDetector()->detectRawDepartment($sintaId);
    }

    protected function suggestStudyProgramIdsFromSintaDepartment(string $sintaId, ?Collection $programs = null): Collection
    {
        return $this->bulkProdiStudyProgramDetector()->suggestStudyProgramIdsFromDepartment($sintaId, $programs);
    }

    protected function queueDepartmentProdiCacheWarmForLatestBatch(int $limit = 100): void
    {
        $this->bulkProdiStudyProgramCacheWarmer()->queueForLatestBatch($limit);
    }

    protected function queueDepartmentProdiCacheWarm(string $sintaId): void
    {
        $this->bulkProdiStudyProgramCacheWarmer()->queueForSintaId($sintaId);
    }
}
