<?php

namespace App\Support;

use App\Models\StudyProgram;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StudyProgramOptions
{
    private const API_URL = 'https://panel-web.unw.ac.id/api/unw-program-studi';
    private const CACHE_KEY = 'academic_programs_select_all';

    public static function postgraduateOptions(): array
    {
        return self::optionsForTarget('postgraduate');
    }

    public static function undergraduateOptions(): array
    {
        return self::optionsForTarget('undergraduate');
    }

    public static function optionsForTarget(string $target): array
    {
        return self::programs()
            ->filter(fn (array $program): bool => self::matchesTarget($program['jenjang'] ?? null, $target))
            ->mapWithKeys(fn (array $program): array => [(string) $program['id'] => self::displayName($program)])
            ->sortBy(fn (string $value): string => $value)
            ->toArray();
    }

    public static function resolveNames(iterable $ids): array
    {
        $ids = collect($ids)->map(fn ($id): string => trim((string) $id))->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $map = self::programs()->mapWithKeys(fn (array $program): array => [(string) $program['id'] => self::displayName($program)]);

        return $ids->map(fn (string $id): string => $map[$id] ?? $id)->toArray();
    }

    public static function ensureStudyPrograms(iterable $ids = []): void
    {
        if (! Schema::hasTable('study_programs')) {
            return;
        }

        self::upsertPrograms(self::programs(false));

        collect($ids)->map(fn ($id): string => trim((string) $id))->filter()->unique()->each(function (string $id): void {
            StudyProgram::query()->firstOrCreate(
                ['id' => (int) $id],
                ['name' => 'Program Studi ' . $id, 'jenjang' => null, 'faculty_name' => null]
            );
        });
    }

    public static function programs(bool $useCache = true): Collection
    {
        if (Schema::hasTable('study_programs')) {
            $programs = StudyProgram::query()->orderBy('jenjang')->orderBy('name')->get();

            if ($programs->isNotEmpty()) {
                return $programs->map(fn (StudyProgram $program): array => [
                    'id' => $program->id,
                    'name' => $program->name,
                    'jenjang' => $program->jenjang,
                    'faculty_name' => $program->faculty_name,
                    'raw_payload' => $program->raw_payload,
                ]);
            }
        }

        $loader = function (): Collection {
            $response = Http::get(self::API_URL);

            if (! $response->successful()) {
                return collect();
            }

            return collect($response->json('data', []))
                ->filter(fn (array $item): bool => isset($item['id'], $item['nama']))
                ->map(fn (array $item): array => [
                    'id' => (int) $item['id'],
                    'name' => trim((string) ($item['nama'] ?? '')),
                    'jenjang' => trim((string) ($item['jenjang'] ?? '')) ?: null,
                    'faculty_name' => trim((string) data_get($item, 'unwFakultas.nama', '')) ?: null,
                    'raw_payload' => $item,
                ])
                ->filter(fn (array $program): bool => $program['id'] > 0 && $program['name'] !== '')
                ->values();
        };

        $programs = $useCache ? Cache::remember(self::CACHE_KEY, now()->addHours(12), $loader) : $loader();

        self::upsertPrograms($programs);

        return $programs;
    }

    public static function matchesTarget(?string $jenjang, string $target): bool
    {
        $normalizedJenjang = Str::of((string) $jenjang)->lower()->trim()->toString();
        $isMagister = Str::contains($normalizedJenjang, ['magister', 's2', 'strata 2']);

        return $target === 'postgraduate' ? $isMagister : ! $isMagister;
    }

    public static function displayName(array $program): string
    {
        return trim(collect([$program['jenjang'] ?? null, $program['name'] ?? null])->filter()->implode(' '));
    }

    private static function upsertPrograms(Collection $programs): void
    {
        if (! Schema::hasTable('study_programs') || $programs->isEmpty()) {
            return;
        }

        $now = now();

        StudyProgram::query()->upsert(
            $programs->map(fn (array $program): array => [
                'id' => $program['id'],
                'name' => $program['name'],
                'jenjang' => $program['jenjang'],
                'faculty_name' => $program['faculty_name'],
                'raw_payload' => isset($program['raw_payload']) ? json_encode($program['raw_payload']) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray(),
            ['id'],
            ['name', 'jenjang', 'faculty_name', 'raw_payload', 'updated_at']
        );
    }
}
