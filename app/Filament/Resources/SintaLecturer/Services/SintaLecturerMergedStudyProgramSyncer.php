<?php

namespace App\Filament\Resources\SintaLecturer\Services;

use App\Models\SintaLecturerStudyProgramSetting;
use App\Models\StudyProgram;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class SintaLecturerMergedStudyProgramSyncer
{
    public function __construct(
        protected SintaLecturerStudyProgramDetector $detector,
    ) {}

    public function syncFromMergedExcel(string $sintaId, string $filePath, ?int $userId = null): array
    {
        $rawStudyProgram = $this->readStudyProgramFromMergedExcelFile($filePath);

        return $this->syncFromRawStudyProgram($sintaId, $rawStudyProgram, $userId);
    }

    public function syncFromRawStudyProgram(string $sintaId, ?string $rawStudyProgram, ?int $userId = null): array
    {
        $rawStudyProgram = is_string($rawStudyProgram) ? trim($rawStudyProgram) : null;
        $isEmpty = ! $rawStudyProgram || $this->detector->isUnknownDepartment($rawStudyProgram);
        $programIds = collect();

        if (! $isEmpty) {
            $strictTeacherEducationIds = $this->strictTeacherEducationStudyProgramIds($rawStudyProgram);
            $programIds = $strictTeacherEducationIds->isNotEmpty()
                ? $strictTeacherEducationIds
                : $this->detector->suggestStudyProgramIds(
                    $rawStudyProgram,
                    $this->studyProgramModels(),
                );
        }

        DB::transaction(function () use ($sintaId, $programIds, $userId): void {
            SintaLecturerStudyProgramSetting::query()
                ->where('sinta_id', $sintaId)
                ->delete();

            if ($programIds->isEmpty()) {
                SintaLecturerStudyProgramSetting::query()->create([
                    'sinta_id' => $sintaId,
                    'study_program_id' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                return;
            }

            foreach ($programIds as $studyProgramId) {
                SintaLecturerStudyProgramSetting::query()->create([
                    'sinta_id' => $sintaId,
                    'study_program_id' => (int) $studyProgramId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        });

        return [
            'sinta_id' => $sintaId,
            'raw_study_program' => $rawStudyProgram,
            'study_program_ids' => $programIds->map(fn ($id) => (int) $id)->values()->all(),
            'status' => $programIds->isNotEmpty() ? 'matched' : ($isEmpty ? 'empty' : 'unmatched'),
        ];
    }

    public function readStudyProgramFromMergedExcelFile(string $filePath): ?string
    {
        try {
            if (! file_exists($filePath) || filesize($filePath) <= 0) {
                return null;
            }

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
                ?? $row['program study']
                ?? data_get(array_values((array) $firstRow), 2);
            $value = is_string($value) ? trim($value) : null;

            return $value !== '' ? $value : null;
        } catch (\Throwable $exception) {
            Log::warning('Failed to read study program from merged SINTA Excel.', [
                'file_path' => $filePath,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function strictTeacherEducationStudyProgramIds(string $rawStudyProgram): Collection
    {
        $probe = $this->normalizeProbeText($rawStudyProgram);
        $target = match (true) {
            str_contains($probe, 'pendidikan guru pendidikan anak usia dini')
                || str_contains($probe, 'guru pendidikan anak usia dini')
                || str_contains($probe, 'pendidikan anak usia dini')
                || str_contains($probe, 'anak usia dini') => 'paud',
            str_contains($probe, 'pendidikan guru sekolah dasar')
                || str_contains($probe, 'guru sekolah dasar')
                || str_contains($probe, 'sekolah dasar') => 'sd',
            default => null,
        };

        if (! $target) {
            return collect();
        }

        return $this->studyProgramModels()
            ->filter(function (StudyProgram $program) use ($target): bool {
                $programProbe = $this->normalizeProbeText(
                    trim((string) $program->nama . ' ' . (string) $program->display_name)
                );

                return match ($target) {
                    'paud' => str_contains($programProbe, 'guru')
                        && preg_match('/\bpaud\b/', $programProbe) === 1
                        && preg_match('/\bsd\b/', $programProbe) !== 1,
                    'sd' => str_contains($programProbe, 'guru')
                        && preg_match('/\bsd\b/', $programProbe) === 1
                        && preg_match('/\bpaud\b/', $programProbe) !== 1,
                    default => false,
                };
            })
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->take(1)
            ->values();
    }

    protected function normalizeProbeText(string $value): string
    {
        $value = Str::of($value)->lower()->ascii()->toString();
        $value = str_replace(['&', '/', '-', '_'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]+/', ' ', $value);
        $value = preg_replace('/\bopendidikan\b/', ' pendidikan ', $value);
        $value = preg_replace('/\b(s1|s2|s3|d3|d4|sarjana|magister|doktor|diploma|program|studi)\b/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    protected function studyProgramModels(): Collection
    {
        return StudyProgram::query()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get();
    }
}
