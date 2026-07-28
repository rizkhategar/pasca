<?php

namespace App\Filament\Resources\SintaLecturer\Services;

use App\Models\SintaLecturer;
use App\Models\StudyProgram;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SintaLecturerStudyProgramDetector
{
    /**
     * Ambil teks program studi dari kolom sinta_lecturers.department.
     * Contoh format: "Profesi - Pendidikan Profesi Bidan" atau "Manajemen Pendidikan (S2)".
     */
    public function detectRawDepartment(string $sintaId): ?string
    {
        $department = SintaLecturer::query()
            ->where('sinta_id', $sintaId)
            ->value('department');

        if (! is_string($department)) {
            return null;
        }

        $department = trim($department);

        if ($department === '' || $this->isUnknownDepartment($department)) {
            return null;
        }

        return $department;
    }

    /**
     * Cocokkan department SINTA dengan tabel study_programs.
     * Return ID program studi terbaik, maksimal 2 jika score terbaik sama.
     */
    public function suggestStudyProgramIdsFromDepartment(string $sintaId, ?Collection $programs = null): Collection
    {
        $rawDepartment = $this->detectRawDepartment($sintaId);

        if (! $rawDepartment) {
            return collect();
        }

        return $this->suggestStudyProgramIds($rawDepartment, $programs ?? $this->getStudyProgramModels());
    }

    public function getStudyProgramModels(): Collection
    {
        return StudyProgram::query()
            ->orderBy('jenjang')
            ->orderBy('nama')
            ->get();
    }

    public function suggestStudyProgramIds(?string $rawStudyProgram, Collection $programs): Collection
    {
        if (! $rawStudyProgram || $this->isUnknownDepartment($rawStudyProgram)) {
            return collect();
        }

        $parsed = $this->parseExternalStudyProgram($rawStudyProgram);
        $externalLevel = $this->canonicalLevel($parsed['level']);
        $externalName = $this->normalizeStudyProgramText($parsed['name']);
        $externalTokens = $this->studyProgramTokens($externalName);

        $scored = $programs
            ->map(function (StudyProgram $program) use ($externalLevel, $externalName, $externalTokens): array {
                $programLevel = $this->canonicalLevel($program->jenjang_nama_singkat ?: $program->jenjang);
                $programName = $this->normalizeStudyProgramText((string) $program->nama);
                $programDisplay = $this->normalizeStudyProgramText((string) $program->display_name);
                $programTokens = $this->studyProgramTokens($programName . ' ' . $programDisplay);
                $score = 0;

                if ($externalLevel && $programLevel && $externalLevel === $programLevel) {
                    $score += 100;
                } elseif ($externalLevel && $programLevel && $externalLevel !== $programLevel) {
                    $score -= 40;
                }

                if ($externalName !== '' && ($externalName === $programName || $externalName === $programDisplay)) {
                    $score += 90;
                }

                if ($externalName !== '' && $programName !== '' && (str_contains($externalName, $programName) || str_contains($programName, $externalName))) {
                    $score += 70;
                }

                if ($externalName !== '' && $programDisplay !== '' && (str_contains($externalName, $programDisplay) || str_contains($programDisplay, $externalName))) {
                    $score += 50;
                }

                $score += $externalTokens->intersect($programTokens)->count() * 25;

                return [
                    'id' => (int) $program->id,
                    'score' => $score,
                ];
            })
            ->filter(fn (array $item): bool => $item['score'] >= 80)
            ->sortByDesc('score')
            ->values();

        if ($scored->isEmpty()) {
            return collect();
        }

        $bestScore = (int) $scored->first()['score'];

        return $scored
            ->filter(fn (array $item): bool => (int) $item['score'] === $bestScore)
            ->pluck('id')
            ->take(2)
            ->values();
    }

    public function parseExternalStudyProgram(string $raw): array
    {
        $raw = trim($raw);
        $level = null;
        $name = $raw;

        // Format: "Profesi - Pendidikan Profesi Bidan" atau "S2 - Manajemen Pendidikan".
        if (preg_match('/^\s*(S1|S2|S3|D3|D4|Profesi|Sarjana|Magister|Doktor|Diploma\s*3|Diploma\s*4)\s*[-–—]\s*(.+)$/iu', $raw, $matches)) {
            $level = trim($matches[1]);
            $name = trim($matches[2]);
        }

        // Format: "Manajemen Pendidikan (S2)" atau "Pendidikan Profesi Bidan (Profesi)".
        if (! $level && preg_match('/^\s*(.+?)\s*\((S1|S2|S3|D3|D4|Profesi|Sarjana|Magister|Doktor|Diploma\s*3|Diploma\s*4)\)\s*$/iu', $raw, $matches)) {
            $name = trim($matches[1]);
            $level = trim($matches[2]);
        }

        // Format fallback: cari level di mana saja, tapi bersihkan token level dari nama.
        if (! $level && preg_match('/\b(S1|S2|S3|D3|D4|Profesi|Sarjana|Magister|Doktor|Diploma\s*3|Diploma\s*4)\b/i', $raw, $matches)) {
            $level = $matches[1];
            $name = trim(preg_replace('/\s*[\(\[]?\b' . preg_quote($matches[1], '/') . '\b[\)\]]?\s*/i', ' ', $raw));
        }

        return [
            'level' => $level,
            'name' => trim($name),
        ];
    }

    public function canonicalLevel(?string $value): ?string
    {
        $value = Str::of((string) $value)->lower()->ascii()->toString();
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = trim($value);

        return match (true) {
            str_contains($value, 's1') || str_contains($value, 'sarjana') => 's1',
            str_contains($value, 's2') || str_contains($value, 'magister') => 's2',
            str_contains($value, 's3') || str_contains($value, 'doktor') => 's3',
            str_contains($value, 'd3') || str_contains($value, 'diploma 3') => 'd3',
            str_contains($value, 'd4') || str_contains($value, 'diploma 4') => 'd4',
            str_contains($value, 'profesi') => 'profesi',
            default => null,
        };
    }

    public function normalizeStudyProgramText(string $value): string
    {
        $value = Str::of($value)->lower()->ascii()->toString();
        $value = str_replace(['&', '/', '-', '_'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]+/', ' ', $value);
        $value = preg_replace('/\bopendidikan\b/', ' pendidikan ', $value);
        $value = $this->normalizeStudyProgramAliases($value);
        $value = preg_replace('/\b(s1|s2|s3|d3|d4|sarjana|magister|doktor|diploma|program|studi)\b/', ' ', $value);
        $value = preg_replace('/\b(pendidikan\s+profesi|pendidikan|profesi)\b/', ' ', $value);
        $value = preg_replace('/\bbidan\b/', ' kebidanan ', $value);
        $value = preg_replace('/\bilmu\b/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    protected function normalizeStudyProgramAliases(string $value): string
    {
        $aliases = [
            // Excel uses the long wording, while study_programs.nama stores the official abbreviations.
            '/\bpendidikan\s+guru\s+pendidikan\s+anak\s+usia\s+dini\b/' => ' pendidikan guru paud ',
            '/\bguru\s+pendidikan\s+anak\s+usia\s+dini\b/' => ' guru paud ',
            '/\bpendidikan\s+anak\s+usia\s+dini\b/' => ' paud ',
            '/\banak\s+usia\s+dini\b/' => ' paud ',
            '/\bpendidikan\s+guru\s+sekolah\s+dasar\b/' => ' pendidikan guru sd ',
            '/\bguru\s+sekolah\s+dasar\b/' => ' guru sd ',
            '/\bsekolah\s+dasar\b/' => ' sd ',
        ];

        foreach ($aliases as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value);
        }

        return $value;
    }

    public function studyProgramTokens(string $value): Collection
    {
        $shortMeaningfulTokens = ['sd'];

        return collect(explode(' ', $value))
            ->map(fn (string $token): string => trim($token))
            ->filter(fn (string $token): bool => $token !== '' && (strlen($token) > 2 || in_array($token, $shortMeaningfulTokens, true)))
            ->unique()
            ->values();
    }

    public function isUnknownDepartment(?string $department): bool
    {
        $value = Str::of((string) $department)->lower()->ascii()->toString();
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = trim($value);

        return $value === ''
            || in_array($value, ['unknown', 'null', 'none', 'n/a', 'na', '-', 'tidak diketahui', 'belum diketahui'], true);
    }
}
