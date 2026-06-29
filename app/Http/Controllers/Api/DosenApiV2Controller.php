<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostgraduateLecturer;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerDetail;
use App\Models\UndergraduateLecturer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class DosenApiV2Controller extends Controller
{
    /**
     * List all SINTA lecturers.
     *
     * Returns all lecturers from the `sinta_lecturers` table.
     *
     * Registration flags:
     *
     * | Field | Description |
     * | --- | --- |
     * | `registered_as.postgraduate` | True when the lecturer is registered in `postgraduate_lecturers`. |
     * | `registered_as.undergraduate` | True when the lecturer is registered in `undergraduate_lecturers`. |
     *
     * @group Lecturer API
     */
    public function index(): JsonResponse
    {
        $data = SintaLecturer::query()
            ->with(['detail', 'postgraduateLecturer', 'undergraduateLecturer'])
            ->orderBy('name')
            ->get()
            ->map(fn (SintaLecturer $lecturer) => $this->sintaLecturerPayload($lecturer))
            ->values();

        return $this->ok('All lecturers from the sinta_lecturers table.', $data);
    }

    /**
     * List registered lecturers by category.
     *
     * Category available:
     *
     * | Category | Description |
     * | --- | --- |
     * | `postgraduate` | Shows postgraduate lecturers from `postgraduate_lecturers` and their `postgraduate_lecturer_study_programs`. |
     * | `undergraduate` | Shows undergraduate lecturers from `undergraduate_lecturers` and their `undergraduate_lecturer_study_programs`. |
     *
     * Example: `GET /api/dosen/postgraduate`
     *
     * @group Lecturer API
     *
     * @urlParam category string required Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     */
    public function byCategory(string $category): JsonResponse
    {
        $category = $this->category($category);
        $model = $this->membershipModel($category);

        $data = $model::query()
            ->with(['sintaLecturer.detail', 'studyPrograms'])
            ->orderBy('name')
            ->get()
            ->map(fn (Model $lecturer) => $this->membershipPayload($lecturer, $category))
            ->values();

        return $this->ok('Registered lecturers for the selected category.', $data, [
            'category' => $category,
            'category_available' => $this->categoryAvailable(),
            'membership_table' => $this->membershipTable($category),
            'pivot_table' => $this->pivotTable($category),
        ]);
    }

    /**
     * Show full SINTA detail for a registered lecturer.
     *
     * Returns full SINTA detail for one lecturer in the selected category.
     *
     * Category available:
     *
     * | Category | Description |
     * | --- | --- |
     * | `postgraduate` | Shows detail only when the lecturer is registered in `postgraduate_lecturers`. |
     * | `undergraduate` | Shows detail only when the lecturer is registered in `undergraduate_lecturers`. |
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
     * @urlParam category string required Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     * @urlParam sinta_id string required SINTA ID. Example: 6954305
     */
    public function show(string $category, string $sinta_id): JsonResponse
    {
        $category = $this->category($category);
        $membership = $this->membership($category, $sinta_id, $this->allRelations());
        $detail = $membership->sintaDetail;

        return $this->ok('Full SINTA detail for a registered lecturer.', [
            'category' => $category,
            'membership' => $this->membershipPayload($membership, $category),
            'sinta_lecturer_details' => $detail ? $this->lecturerDetailPayload($detail) : null,
            'garuda' => $detail ? $this->moduleData($detail, 'garuda') : $this->emptyModule(),
            'scopus' => $detail ? $this->moduleData($detail, 'scopus') : $this->emptyModule(),
            'scholar' => $detail ? $this->moduleData($detail, 'scholar') : $this->emptyModule(),
            'book' => $detail ? $this->moduleData($detail, 'book') : $this->emptyModule(),
            'research' => $detail ? $this->moduleData($detail, 'research') : $this->emptyModule(),
            'service' => $detail ? $this->moduleData($detail, 'service') : $this->emptyModule(),
        ], [
            'category' => $category,
            'sinta_id' => $sinta_id,
            'category_available' => $this->categoryAvailable(),
            'module_available' => $this->moduleAvailable(),
        ]);
    }

    /**
     * Show selected SINTA data module.
     *
     * Returns one selected module. Without `{mode}`, the response returns both `index` and `yearly` keys when available.
     *
     * Category available:
     *
     * | Category | Description |
     * | --- | --- |
     * | `postgraduate` | Shows module data only for lecturers registered in `postgraduate_lecturers`. |
     * | `undergraduate` | Shows module data only for lecturers registered in `undergraduate_lecturers`. |
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
     * | `service` | Community service list and service yearly statistics. |
     * | `lecturer-details` | Main SINTA lecturer detail. Yearly returns an empty array. |
     *
     * @group Lecturer API
     *
     * @urlParam category string required Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     * @urlParam sinta_id string required SINTA ID. Example: 6954305
     * @urlParam module string required Available values: `garuda`, `scopus`, `scholar`, `book`, `research`, `service`, `lecturer-details`. Example: garuda
     */
    public function module(string $category, string $sinta_id, string $module): JsonResponse
    {
        return $this->moduleMode($category, $sinta_id, $module, null);
    }

    /**
     * Show selected SINTA data module by mode.
     *
     * Use `index` for list/table data only and `yearly` for yearly statistics only.
     *
     * Category available:
     *
     * | Category | Description |
     * | --- | --- |
     * | `postgraduate` | Shows selected data only for lecturers registered in `postgraduate_lecturers`. |
     * | `undergraduate` | Shows selected data only for lecturers registered in `undergraduate_lecturers`. |
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
     * @urlParam category string required Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     * @urlParam sinta_id string required SINTA ID. Example: 6954305
     * @urlParam module string required Available values: `garuda`, `scopus`, `scholar`, `book`, `research`, `service`, `lecturer-details`. Example: garuda
     * @urlParam mode string required Available values: `index`, `yearly`. Example: yearly
     */
    public function moduleMode(string $category, string $sinta_id, string $module, ?string $mode = null): JsonResponse
    {
        $category = $this->category($category);
        $module = $this->moduleName($module);
        $mode = $mode ? $this->mode($mode) : null;
        $membership = $this->membership($category, $sinta_id, $this->moduleRelations($module));
        $detail = $membership->sintaDetail;

        if (! $detail) {
            abort(404, 'SINTA lecturer detail was not found for this registered lecturer.');
        }

        $payload = $this->moduleData($detail, $module);

        return $this->ok('Selected SINTA module data for a registered lecturer.', $mode ? [$mode => $payload[$mode] ?? []] : $payload, [
            'category' => $category,
            'sinta_id' => $sinta_id,
            'module' => $module,
            'mode' => $mode,
            'category_available' => $this->categoryAvailable(),
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

    private function category(string $category): string
    {
        $category = strtolower(trim($category));
        abort_unless(in_array($category, ['postgraduate', 'undergraduate'], true), 404, 'Allowed categories are postgraduate and undergraduate.');

        return $category;
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

    private function membershipModel(string $category): string
    {
        return $category === 'postgraduate' ? PostgraduateLecturer::class : UndergraduateLecturer::class;
    }

    private function membership(string $category, string $sintaId, array $relations = []): Model
    {
        $model = $this->membershipModel($category);

        return $model::query()
            ->with([
                'sintaLecturer',
                'studyPrograms',
                'sintaDetail' => fn ($query) => $query->with($relations),
            ])
            ->where('sinta_id', $sintaId)
            ->firstOrFail();
    }

    private function membershipTable(string $category): string
    {
        return $category === 'postgraduate' ? 'postgraduate_lecturers' : 'undergraduate_lecturers';
    }

    private function pivotTable(string $category): string
    {
        return $category === 'postgraduate' ? 'postgraduate_lecturer_study_programs' : 'undergraduate_lecturer_study_programs';
    }

    private function categoryAvailable(): array
    {
        return [
            'postgraduate' => 'Shows postgraduate lecturers.',
            'undergraduate' => 'Shows undergraduate lecturers.',
        ];
    }

    private function moduleAvailable(): array
    {
        return [
            'garuda' => 'Garuda publication list and Garuda yearly statistics.',
            'scopus' => 'Scopus publication list and Scopus yearly statistics.',
            'scholar' => 'Scholar publication list and Scholar yearly statistics.',
            'book' => 'Book publication list. Yearly returns an empty array.',
            'research' => 'Research list and research yearly statistics.',
            'service' => 'Community service list and service yearly statistics.',
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

    private function membershipPayload(Model $lecturer, string $category): array
    {
        return [
            'category' => $category,
            'membership_table' => $this->membershipTable($category),
            'id' => $lecturer->id,
            'sinta_id' => $lecturer->sinta_id,
            'name' => $lecturer->name ?? $lecturer->sintaLecturer?->name,
            'institution' => $lecturer->institution,
            'profile_photo_url' => $this->photoUrl($lecturer->profile_photo ?? $lecturer->sintaDetail?->profile_photo),
            'sinta_lecturer' => $lecturer->sintaLecturer ? $this->sintaLecturerPayload($lecturer->sintaLecturer, false) : null,
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

    private function sintaLecturerPayload(SintaLecturer $lecturer, bool $withRegistration = true): array
    {
        $data = [
            'sinta_id' => $lecturer->sinta_id,
            'name' => $lecturer->name,
            'department' => $lecturer->department,
            'scopus_h_index' => $lecturer->scopus_h_index,
            'google_scholar_h_index' => $lecturer->google_scholar_h_index,
            'sinta_score_3yr' => $lecturer->sinta_score_3yr,
            'sinta_score' => $lecturer->sinta_score,
            'affiliation_score_3yr' => $lecturer->affiliation_score_3yr,
            'affiliation_score' => $lecturer->affiliation_score,
            'profile_url' => $lecturer->profile_url,
            'has_sinta_detail' => (bool) $lecturer->detail,
        ];

        if ($withRegistration) {
            $data['registered_as'] = [
                'postgraduate' => (bool) $lecturer->postgraduateLecturer,
                'undergraduate' => (bool) $lecturer->undergraduateLecturer,
            ];
        }

        return $data;
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
