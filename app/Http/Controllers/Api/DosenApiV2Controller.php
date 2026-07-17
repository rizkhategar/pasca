<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostgraduateLecturer;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DosenApiV2Controller extends Controller
{
    /**
     * List all SINTA lecturers.
     *
     * Returns all lecturers from the `sinta_lecturers` master table.
     *
     * @group Lecturer API
     */
    public function index(): JsonResponse
    {
        $data = SintaLecturer::query()
            ->with(['detail', 'postgraduateLecturer'])
            ->orderBy('name')
            ->get()
            ->map(fn (SintaLecturer $lecturer) => $this->sintaLecturerPayload($lecturer))
            ->values();

        return $this->ok('All lecturers from the sinta_lecturers table.', $data);
    }

    /**
     * Show full SINTA detail for one lecturer.
     *
     * Returns master lecturer data, optional postgraduate registration, SINTA detail, and all available publication/yearly modules.
     *
     * Module included in full detail:
     *
     * | Module | Description |
     * | --- | --- |
     * | `lecturer-details` | Main SINTA lecturer detail from `sinta_lecturer_details`. |
     * | `garuda` | Garuda publications and Garuda yearly statistics. |
     * | `scopus` | Scopus publications and Scopus yearly statistics. |
     * | `scholar` | Scholar publications and Scholar yearly statistics. |
     * | `book` | Book publications. |
     * | `research` | Research data and research yearly statistics. |
     * | `service` | Community service data and service yearly statistics. |
     *
     * @group Lecturer API
     *
     * @urlParam sinta_id string required SINTA ID. Example: 6954305
     */
    public function show(string $sinta_id): JsonResponse
    {
        $lecturer = SintaLecturer::query()
            ->with([
                'postgraduateLecturer.studyPrograms',
                'detail' => fn ($query) => $query->with($this->allRelations()),
            ])
            ->where('sinta_id', $sinta_id)
            ->firstOrFail();

        $detail = $lecturer->detail;

        return $this->ok('Full SINTA detail for one lecturer.', [
            'sinta_lecturer' => $this->sintaLecturerPayload($lecturer),
            'postgraduate_membership' => $lecturer->postgraduateLecturer
                ? $this->postgraduateMembershipPayload($lecturer->postgraduateLecturer)
                : null,
            'sinta_lecturer_details' => $detail ? $this->lecturerDetailPayload($detail) : null,
            'garuda' => $detail ? $this->moduleData($detail, 'garuda') : $this->emptyModule(),
            'scopus' => $detail ? $this->moduleData($detail, 'scopus') : $this->emptyModule(),
            'scholar' => $detail ? $this->moduleData($detail, 'scholar') : $this->emptyModule(),
            'book' => $detail ? $this->moduleData($detail, 'book') : $this->emptyModule(),
            'research' => $detail ? $this->moduleData($detail, 'research') : $this->emptyModule(),
            'service' => $detail ? $this->moduleData($detail, 'service') : $this->emptyModule(),
        ], [
            'sinta_id' => $sinta_id,
            'module_available' => $this->moduleAvailable(),
        ]);
    }

    /**
     * Show selected SINTA data module.
     *
     * Returns one selected module. Without `{mode}`, the response returns both `index` and `yearly` keys when available.
     *
     * Module available:
     *
     * | Module | Description |
     * | --- | --- |
     * | `garuda` | Garuda publication list and Garuda yearly statistics. |
     * | `scopus` | Scopus publication list and Scopus yearly statistics. |
     * | `scholar` | Scholar publication list and Scholar yearly statistics. |
     * | `book` | Book publication list. Yearly returns an empty array. |
     * | `research` | Research list and research yearly statistics. |
     * | `service` | Community service data and service yearly statistics. |
     * | `lecturer-details` | Main SINTA lecturer detail. Yearly returns an empty array. |
     *
     * @group Lecturer API
     *
     * @urlParam sinta_id string required SINTA ID. Example: 6954305
     * @urlParam module string required Available values: `garuda`, `scopus`, `scholar`, `book`, `research`, `service`, `lecturer-details`. Example: garuda
     */
    public function module(string $sinta_id, string $module): JsonResponse
    {
        return $this->moduleMode($sinta_id, $module, null);
    }

    /**
     * Show selected SINTA data module by mode.
     *
     * Use `index` for list/table data only and `yearly` for yearly statistics only.
     *
     * Module available:
     *
     * | Module | `index` returns | `yearly` returns |
     * | --- | --- | --- |
     * | `garuda` | Garuda publications | Garuda yearly statistics |
     * | `scopus` | Scopus publications | Scopus yearly statistics |
     * | `scholar` | Scholar publications | Scholar yearly statistics |
     * | `book` | Book publications | Empty array |
     * | `research` | Research data | Research yearly statistics |
     * | `service` | Community service data | Service yearly statistics |
     * | `lecturer-details` | Main SINTA lecturer detail | Empty array |
     *
     * Mode available:
     *
     * | Mode | Description |
     * | --- | --- |
     * | `index` | Returns list/table data only. |
     * | `yearly` | Returns yearly statistics only. |
     *
     * @group Lecturer API
     *
     * @urlParam sinta_id string required SINTA ID. Example: 6954305
     * @urlParam module string required Available values: `garuda`, `scopus`, `scholar`, `book`, `research`, `service`, `lecturer-details`. Example: garuda
     * @urlParam mode string required Available values: `index`, `yearly`. Example: yearly
     */
    public function moduleMode(string $sinta_id, string $module, ?string $mode = null): JsonResponse
    {
        $module = $this->moduleName($module);
        $mode = $mode ? $this->mode($mode) : null;

        $lecturer = SintaLecturer::query()
            ->with(['detail' => fn ($query) => $query->with($this->moduleRelations($module))])
            ->where('sinta_id', $sinta_id)
            ->firstOrFail();

        $detail = $lecturer->detail;

        if (! $detail) {
            abort(404, 'SINTA lecturer detail was not found for this lecturer.');
        }

        $payload = $this->moduleData($detail, $module);

        return $this->ok('Selected SINTA module data for one lecturer.', $mode ? [$mode => $payload[$mode] ?? []] : $payload, [
            'sinta_id' => $sinta_id,
            'module' => $module,
            'mode' => $mode,
            'module_available' => $this->moduleAvailable(),
            'mode_available' => $this->modeAvailable(),
        ]);
    }

    private function ok(string $description, mixed $data, array $meta = []): JsonResponse
    {
        return response()->json([
            'meta' => array_merge(['description' => $description], $meta),
            'data' => $data,
        ]);
    }

    private function moduleName(string $module): string
    {
        return match (strtolower(trim(str_replace('_', '-', $module)))) {
            'garuda' => 'garuda',
            'scopus' => 'scopus',
            'scholar', 'sinta-scholar', 'sinta-schollar' => 'scholar',
            'book', 'books' => 'book',
            'research', 'researches' => 'research',
            'service', 'services' => 'service',
            'lecturer-detail', 'lecturer-details', 'detail', 'details', 'sinta-lecturer-details' => 'lecturer-details',
            default => abort(404, 'Allowed modules are garuda, scopus, scholar, book, research, service, and lecturer-details.'),
        };
    }

    private function mode(string $mode): string
    {
        $mode = strtolower(trim($mode));
        abort_unless(in_array($mode, ['index', 'yearly'], true), 404, 'Allowed modes are index and yearly.');

        return $mode;
    }

    private function moduleAvailable(): array
    {
        return [
            'garuda' => 'Garuda publication list and Garuda yearly statistics.',
            'scopus' => 'Scopus publication list and Scopus yearly statistics.',
            'scholar' => 'Scholar publication list and Scholar yearly statistics.',
            'book' => 'Book publication list. Yearly returns an empty array.',
            'research' => 'Research list and research yearly statistics.',
            'service' => 'Community service data and service yearly statistics.',
            'lecturer-details' => 'Main SINTA lecturer detail. Yearly returns an empty array.',
        ];
    }

    private function modeAvailable(): array
    {
        return [
            'index' => 'Returns list/table data only.',
            'yearly' => 'Returns yearly statistics only.',
        ];
    }

    private function allRelations(): array
    {
        return [
            'scopusPublications',
            'scopusYearlyStats',
            'scholarPublications',
            'scholarYearlyStats',
            'garudaPublications',
            'garudaYearlyStats',
            'books',
            'researches',
            'researchYearlies',
            'services',
            'serviceYearlies',
        ];
    }

    private function moduleRelations(string $module): array
    {
        return match ($module) {
            'garuda' => ['garudaPublications', 'garudaYearlyStats'],
            'scopus' => ['scopusPublications', 'scopusYearlyStats'],
            'scholar' => ['scholarPublications', 'scholarYearlyStats'],
            'book' => ['books'],
            'research' => ['researches', 'researchYearlies'],
            'service' => ['services', 'serviceYearlies'],
            default => [],
        };
    }

    private function moduleData(SintaLecturerDetail $detail, string $module): array
    {
        return match ($module) {
            'garuda' => ['index' => $this->items($detail->garudaPublications), 'yearly' => $this->items($detail->garudaYearlyStats)],
            'scopus' => ['index' => $this->items($detail->scopusPublications), 'yearly' => $this->items($detail->scopusYearlyStats)],
            'scholar' => ['index' => $this->items($detail->scholarPublications), 'yearly' => $this->items($detail->scholarYearlyStats)],
            'book' => ['index' => $this->items($detail->books), 'yearly' => []],
            'research' => ['index' => $this->items($detail->researches), 'yearly' => $this->items($detail->researchYearlies)],
            'service' => ['index' => $this->items($detail->services), 'yearly' => $this->items($detail->serviceYearlies)],
            'lecturer-details' => ['index' => $this->lecturerDetailPayload($detail), 'yearly' => []],
            default => $this->emptyModule(),
        };
    }

    private function emptyModule(): array
    {
        return ['index' => [], 'yearly' => []];
    }

    private function postgraduateMembershipPayload(PostgraduateLecturer $lecturer): array
    {
        return [
            'membership_table' => 'lecturers',
            'pivot_table' => 'lecturer_study_programs',
            'id' => $lecturer->id,
            'sinta_id' => $lecturer->sinta_id,
            'name' => $lecturer->name,
            'institution' => $lecturer->institution,
            'profile_photo_url' => $this->photoUrl($lecturer->profile_photo),
            'study_programs' => collect($lecturer->studyPrograms ?? [])->map(fn ($program) => [
                'id' => $program->id,
                'id_unw_program_studi' => $program->id_unw_program_studi,
                'nama' => $program->nama,
                'display_name' => $program->display_name,
                'jenjang' => $program->jenjang,
                'jenjang_nama_singkat' => $program->jenjang_nama_singkat,
                'unw_fakultas_nama' => $program->unw_fakultas_nama,
            ])->values(),
        ];
    }

    private function sintaLecturerPayload(SintaLecturer $lecturer): array
    {
        return [
            'sinta_id' => $lecturer->sinta_id,
            'name' => $lecturer->name,
            'department' => $lecturer->department,
            'scopus_h_index' => $lecturer->scopus_h_index,
            'google_scholar_h_index' => $lecturer->google_scholar_h_index,
            'sinta_score_3yr' => $lecturer->sinta_score_3yr,
            'sinta_score' => $lecturer->sinta_score,
            'affiliation_score_3yr' => $lecturer->affiliation_score_3yr,
            'affiliation_score' => $lecturer->affiliation_score,
            'profile_photo_url' => $this->photoUrl($lecturer->postgraduateLecturer?->profile_photo),
            'has_sinta_detail' => (bool) $lecturer->detail,
            'registered_as' => [
                'postgraduate' => (bool) $lecturer->postgraduateLecturer,
            ],
        ];
    }

    private function lecturerDetailPayload(SintaLecturerDetail $detail): array
    {
        return [
            'sinta_id' => $detail->sinta_id,
            'institution' => $detail->institution,
            'study_program' => $detail->study_program,
            'profile_photo_url' => $this->photoUrl($detail->profile_photo),
            'research_interests' => $detail->research_interests,
            'sinta_scores' => [
                'overall' => $detail->sinta_score_overall ?? 0,
                'three_year' => $detail->sinta_score_3yr ?? 0,
                'affil' => $detail->affil_score ?? 0,
                'affil_three_year' => $detail->affil_score_3yr ?? 0,
            ],
        ];
    }

    private function items($items): array
    {
        return collect($items ?? [])
            ->map(fn ($item) => collect($item->toArray())->except(['created_at', 'updated_at'])->toArray())
            ->values()
            ->toArray();
    }

    private function photoUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '') {
            return null;
        }

        if (! str_contains($path, '/')) {
            $path = 'sinta-lecturers/' . $path;
        }

        return Storage::disk('public')->exists($path) ? url('storage/' . $path) : Storage::disk('public')->url($path);
    }
}
