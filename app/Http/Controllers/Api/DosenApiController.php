<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostgraduateLecturer;
use App\Models\SintaLecturer;
use App\Models\SintaLecturerDetail;
use App\Models\UndergraduateLecturer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DosenApiController extends Controller
{
    /**
     * List all SINTA lecturers.
     *
     * Returns every lecturer from the `sinta_lecturers` table. Default documentation and response metadata are in English. Use `?lang=id` to switch response metadata to Indonesian.
     *
     * Bahasa Indonesia: Menampilkan seluruh data dosen dari tabel `sinta_lecturers`. Gunakan `?lang=id` untuk metadata response Bahasa Indonesia.
     *
     * @group Lecturer API / API Dosen
     *
     * @queryParam lang string Optional language switch. Available values: `en`, `id`. Example: id
     */
    public function index(Request $request): JsonResponse
    {
        $dosens = SintaLecturer::query()
            ->with(['detail', 'postgraduateLecturer', 'undergraduateLecturer'])
            ->orderBy('name')
            ->get()
            ->map(fn (SintaLecturer $lecturer): array => $this->formatSintaLecturer($lecturer))
            ->values();

        return $this->respond($request, 'lecturers_all', $dosens);
    }

    /**
     * List registered lecturers by education level.
     *
     * Use `postgraduate` to list lecturers registered in `postgraduate_lecturers` with their `postgraduate_lecturer_study_programs`. Use `undergraduate` to list lecturers registered in `undergraduate_lecturers` with their `undergraduate_lecturer_study_programs`.
     *
     * Bahasa Indonesia: Gunakan `postgraduate` untuk daftar dosen pascasarjana dan `undergraduate` untuk daftar dosen sarjana/non-magister. Gunakan `?lang=id` untuk metadata response Bahasa Indonesia.
     *
     * @group Lecturer API / API Dosen
     *
     * @urlParam category string required Lecturer category. Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     * @queryParam lang string Optional language switch. Available values: `en`, `id`. Example: id
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        $category = $this->normalizeCategory($category);
        $modelClass = $this->membershipModelClass($category);

        $lecturers = $modelClass::query()
            ->with(['sintaLecturer.detail', 'studyPrograms'])
            ->orderBy('name')
            ->get()
            ->map(fn (Model $membership): array => $this->formatMembershipLecturer($membership, $category))
            ->values();

        return $this->respond($request, "lecturers_{$category}", $lecturers, [
            'category' => $category,
            'membership_table' => $this->membershipTable($category),
            'pivot_table' => $this->pivotTable($category),
        ]);
    }

    /**
     * Show full SINTA detail for a registered lecturer.
     *
     * Shows the complete SINTA detail for a lecturer registered in the selected category. The response includes `sinta_lecturer_details`, books, scholar, scopus, garuda, research, service, and yearly statistics when available.
     *
     * Bahasa Indonesia: Menampilkan seluruh detail SINTA untuk dosen yang terdaftar pada kategori yang dipilih, termasuk detail dosen, buku, scholar, scopus, garuda, penelitian, pengabdian, dan data yearly.
     *
     * @group Lecturer API / API Dosen
     *
     * @urlParam category string required Lecturer category. Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     * @urlParam sinta_id string required SINTA ID of the lecturer. Example: 6954305
     * @queryParam lang string Optional language switch. Available values: `en`, `id`. Example: id
     */
    public function show(Request $request, string $category, string $sinta_id): JsonResponse
    {
        $category = $this->normalizeCategory($category);
        $membership = $this->findMembershipOrFail($category, $sinta_id, $this->allDetailRelations());

        return $this->respond($request, "lecturer_{$category}_detail", $this->formatFullDetail($membership, $category), [
            'category' => $category,
            'sinta_id' => $sinta_id,
        ]);
    }

    /**
     * Show selected SINTA data module.
     *
     * Shows only one selected module for a registered lecturer. Supported modules: `garuda`, `scopus`, `scholar`, `book`, `research`, `service`, and `lecturer-details`. Without `{mode}`, both list/index and yearly data are returned when the module supports yearly data.
     *
     * Bahasa Indonesia: Menampilkan hanya modul data yang dipilih. Contoh: `garuda` hanya menampilkan publikasi Garuda dan yearly Garuda. Gunakan `?lang=id` untuk metadata response Bahasa Indonesia.
     *
     * @group Lecturer API / API Dosen
     *
     * @urlParam category string required Lecturer category. Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     * @urlParam sinta_id string required SINTA ID of the lecturer. Example: 6954305
     * @urlParam module string required Data module. Available values: `garuda`, `scopus`, `scholar`, `book`, `research`, `service`, `lecturer-details`. Example: garuda
     * @queryParam lang string Optional language switch. Available values: `en`, `id`. Example: id
     */
    public function module(Request $request, string $category, string $sinta_id, string $module): JsonResponse
    {
        return $this->moduleMode($request, $category, $sinta_id, $module, null);
    }

    /**
     * Show selected SINTA data module by mode.
     *
     * Use `{mode}=index` to return only the list data. Use `{mode}=yearly` to return only yearly statistics. This is useful for separating table/list payloads from chart/yearly payloads.
     *
     * Bahasa Indonesia: Gunakan `{mode}=index` untuk daftar data saja dan `{mode}=yearly` untuk data tahunan saja.
     *
     * @group Lecturer API / API Dosen
     *
     * @urlParam category string required Lecturer category. Available values: `postgraduate`, `undergraduate`. Example: postgraduate
     * @urlParam sinta_id string required SINTA ID of the lecturer. Example: 6954305
     * @urlParam module string required Data module. Available values: `garuda`, `scopus`, `scholar`, `book`, `research`, `service`, `lecturer-details`. Example: garuda
     * @urlParam mode string required Output mode. Available values: `index`, `yearly`. Example: yearly
     * @queryParam lang string Optional language switch. Available values: `en`, `id`. Example: id
     */
    public function moduleMode(Request $request, string $category, string $sinta_id, string $module, ?string $mode = null): JsonResponse
    {
        $category = $this->normalizeCategory($category);
        $normalizedModule = $this->normalizeModule($module);
        $normalizedMode = $mode ? $this->normalizeMode($mode) : null;
        $relations = $this->relationsForModule($normalizedModule);
        $membership = $this->findMembershipOrFail($category, $sinta_id, $relations);
        $detail = $membership->sintaDetail;

        if (! $detail) {
            abort(404, 'SINTA lecturer detail data was not found for this registered lecturer.');
        }

        $payload = $this->modulePayload($detail, $normalizedModule, $normalizedMode);

        return $this->respond($request, "lecturer_{$category}_{$normalizedModule}" . ($normalizedMode ? "_{$normalizedMode}" : ''), $payload, [
            'category' => $category,
            'sinta_id' => $sinta_id,
            'module' => $normalizedModule,
            'mode' => $normalizedMode,
        ]);
    }

    private function respond(Request $request, string $descriptionKey, mixed $data, array $extraMeta = []): JsonResponse
    {
        $lang = $this->language($request);

        return response()->json([
            'meta' => array_merge([
                'language' => $lang,
                'description' => $this->description($descriptionKey, $lang),
            ], $extraMeta),
            'data' => $data,
        ]);
    }

    private function language(Request $request): string
    {
        return strtolower((string) $request->query('lang')) === 'id' ? 'id' : 'en';
    }

    private function description(string $key, string $lang): string
    {
        $descriptions = [
            'lecturers_all' => [
                'en' => 'All lecturers from the sinta_lecturers table.',
                'id' => 'Seluruh data dosen dari tabel sinta_lecturers.',
            ],
            'lecturers_postgraduate' => [
                'en' => 'Registered postgraduate lecturers with their postgraduate study program memberships.',
                'id' => 'Daftar dosen pascasarjana beserta relasi program studinya.',
            ],
            'lecturers_undergraduate' => [
                'en' => 'Registered undergraduate lecturers with their undergraduate study program memberships.',
                'id' => 'Daftar dosen undergraduate beserta relasi program studinya.',
            ],
            'lecturer_postgraduate_detail' => [
                'en' => 'Full SINTA detail for a registered postgraduate lecturer.',
                'id' => 'Detail SINTA lengkap untuk dosen pascasarjana terdaftar.',
            ],
            'lecturer_undergraduate_detail' => [
                'en' => 'Full SINTA detail for a registered undergraduate lecturer.',
                'id' => 'Detail SINTA lengkap untuk dosen undergraduate terdaftar.',
            ],
        ];

        if (str_contains($key, '_index')) {
            return $lang === 'id'
                ? 'Daftar data saja untuk modul SINTA yang dipilih.'
                : 'Index/list data only for the selected SINTA module.';
        }

        if (str_contains($key, '_yearly')) {
            return $lang === 'id'
                ? 'Data statistik tahunan saja untuk modul SINTA yang dipilih.'
                : 'Yearly statistics only for the selected SINTA module.';
        }

        if (preg_match('/lecturer_(postgraduate|undergraduate)_([a-z_\-]+)/', $key, $matches)) {
            return $lang === 'id'
                ? 'Data modul SINTA yang dipilih untuk dosen terdaftar.'
                : 'Selected SINTA module data for a registered lecturer.';
        }

        return $descriptions[$key][$lang] ?? $key;
    }

    private function normalizeCategory(string $category): string
    {
        $category = strtolower(trim($category));

        if (! in_array($category, ['postgraduate', 'undergraduate'], true)) {
            abort(404, 'Allowed lecturer categories are postgraduate and undergraduate.');
        }

        return $category;
    }

    private function normalizeModule(string $module): string
    {
        $module = strtolower(trim(str_replace('_', '-', $module)));

        return match ($module) {
            'garuda' => 'garuda',
            'scopus' => 'scopus',
            'scholar', 'sinta-scholar', 'sinta-schollar' => 'scholar',
            'book', 'books' => 'book',
            'research', 'researches', 'penelitian' => 'research',
            'service', 'services', 'pengabdian' => 'service',
            'lecturer-detail', 'lecturer-details', 'detail', 'details', 'sinta-lecturer-details' => 'lecturer-details',
            default => abort(404, 'Allowed modules are garuda, scopus, scholar, book, research, service, and lecturer-details.'),
        };
    }

    private function normalizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));

        if (! in_array($mode, ['index', 'yearly'], true)) {
            abort(404, 'Allowed module modes are index and yearly.');
        }

        return $mode;
    }

    private function membershipModelClass(string $category): string
    {
        return $category === 'postgraduate'
            ? PostgraduateLecturer::class
            : UndergraduateLecturer::class;
    }

    private function membershipTable(string $category): string
    {
        return $category === 'postgraduate'
            ? 'postgraduate_lecturers'
            : 'undergraduate_lecturers';
    }

    private function pivotTable(string $category): string
    {
        return $category === 'postgraduate'
            ? 'postgraduate_lecturer_study_programs'
            : 'undergraduate_lecturer_study_programs';
    }

    private function findMembershipOrFail(string $category, string $sintaId, array $detailRelations = []): Model
    {
        $modelClass = $this->membershipModelClass($category);

        return $modelClass::query()
            ->with([
                'sintaLecturer',
                'studyPrograms',
                'sintaDetail' => fn ($query) => $query->with($detailRelations),
            ])
            ->where('sinta_id', $sintaId)
            ->firstOrFail();
    }

    private function allDetailRelations(): array
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

    private function relationsForModule(string $module): array
    {
        return match ($module) {
            'garuda' => ['garudaPublications', 'garudaYearlyStats'],
            'scopus' => ['scopusPublications', 'scopusYearlyStats'],
            'scholar' => ['scholarPublications', 'scholarYearlyStats'],
            'book' => ['books'],
            'research' => ['researches', 'researchYearlies'],
            'service' => ['services', 'serviceYearlies'],
            'lecturer-details' => [],
            default => [],
        };
    }

    private function modulePayload(SintaLecturerDetail $detail, string $module, ?string $mode): array
    {
        $payload = match ($module) {
            'garuda' => [
                'index' => $this->collectionToArray($detail->garudaPublications),
                'yearly' => $this->collectionToArray($detail->garudaYearlyStats),
            ],
            'scopus' => [
                'index' => $this->collectionToArray($detail->scopusPublications),
                'yearly' => $this->collectionToArray($detail->scopusYearlyStats),
            ],
            'scholar' => [
                'index' => $this->collectionToArray($detail->scholarPublications),
                'yearly' => $this->collectionToArray($detail->scholarYearlyStats),
            ],
            'book' => [
                'index' => $this->collectionToArray($detail->books),
                'yearly' => [],
            ],
            'research' => [
                'index' => $this->collectionToArray($detail->researches),
                'yearly' => $this->collectionToArray($detail->researchYearlies),
            ],
            'service' => [
                'index' => $this->collectionToArray($detail->services),
                'yearly' => $this->collectionToArray($detail->serviceYearlies),
            ],
            'lecturer-details' => [
                'index' => $this->formatLecturerDetail($detail),
                'yearly' => [],
            ],
            default => [],
        };

        if ($mode) {
            return [$mode => $payload[$mode] ?? []];
        }

        return $payload;
    }

    private function formatFullDetail(Model $membership, string $category): array
    {
        $detail = $membership->sintaDetail;

        return [
            'category' => $category,
            'membership' => $this->formatMembershipLecturer($membership, $category),
            'sinta_lecturer_details' => $detail ? $this->formatLecturerDetail($detail) : null,
            'scopus' => $detail ? $this->modulePayload($detail, 'scopus', null) : ['index' => [], 'yearly' => []],
            'scholar' => $detail ? $this->modulePayload($detail, 'scholar', null) : ['index' => [], 'yearly' => []],
            'garuda' => $detail ? $this->modulePayload($detail, 'garuda', null) : ['index' => [], 'yearly' => []],
            'books' => $detail ? $this->modulePayload($detail, 'book', null) : ['index' => [], 'yearly' => []],
            'research' => $detail ? $this->modulePayload($detail, 'research', null) : ['index' => [], 'yearly' => []],
            'service' => $detail ? $this->modulePayload($detail, 'service', null) : ['index' => [], 'yearly' => []],
        ];
    }

    private function formatMembershipLecturer(Model $membership, string $category): array
    {
        return [
            'category' => $category,
            'membership_table' => $this->membershipTable($category),
            'id' => $membership->id,
            'sinta_id' => $membership->sinta_id,
            'name' => $membership->name ?? $membership->sintaLecturer?->name,
            'institution' => $membership->institution,
            'profile_photo_url' => $this->resolveProfilePhotoUrl($membership->profile_photo ?? $membership->sintaDetail?->profile_photo),
            'sinta_lecturer' => $membership->sintaLecturer ? $this->formatSintaLecturer($membership->sintaLecturer, false) : null,
            'study_programs' => $this->studyProgramsToArray($membership->studyPrograms ?? collect()),
        ];
    }

    private function formatSintaLecturer(SintaLecturer $lecturer, bool $includeRegistration = true): array
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

        if ($includeRegistration) {
            $data['registered_as'] = [
                'postgraduate' => (bool) $lecturer->postgraduateLecturer,
                'undergraduate' => (bool) $lecturer->undergraduateLecturer,
            ];
        }

        return $data;
    }

    private function formatLecturerDetail(SintaLecturerDetail $detail): array
    {
        return [
            'sinta_id' => $detail->sinta_id,
            'institution' => $detail->institution,
            'study_program' => $detail->study_program,
            'profile_photo_url' => $this->resolveProfilePhotoUrl($detail->profile_photo),
            'research_interests' => $detail->research_interests,
            'sinta_scores' => [
                'overall' => $detail->sinta_score_overall ?? 0,
                'three_year' => $detail->sinta_score_3yr ?? 0,
                'affil' => $detail->affil_score ?? 0,
                'affil_three_year' => $detail->affil_score_3yr ?? 0,
            ],
        ];
    }

    private function studyProgramsToArray(Collection $studyPrograms): array
    {
        return $studyPrograms
            ->map(fn ($program): array => [
                'id' => $program->id,
                'id_unw_program_studi' => $program->id_unw_program_studi,
                'nama' => $program->nama,
                'display_name' => $program->display_name,
                'slug' => $program->slug,
                'page_slug' => $program->page_slug,
                'jenjang' => $program->jenjang,
                'jenjang_nama_singkat' => $program->jenjang_nama_singkat,
                'unw_fakultas_id' => $program->unw_fakultas_id,
                'unw_fakultas_nama' => $program->unw_fakultas_nama,
                'unw_fakultas_page_slug' => $program->unw_fakultas_page_slug,
            ])
            ->values()
            ->toArray();
    }

    private function collectionToArray($items): array
    {
        if (! $items) {
            return [];
        }

        return collect($items)
            ->map(fn ($item) => collect($item->toArray())->except(['created_at', 'updated_at'])->toArray())
            ->values()
            ->toArray();
    }

    private function resolveProfilePhotoUrl(?string $profilePhoto): ?string
    {
        if (! $profilePhoto) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $profilePhoto), '/');

        if ($path === '') {
            return null;
        }

        if (! str_contains($path, '/')) {
            $path = 'sinta-lecturers/' . $path;
        }

        return Storage::disk('public')->exists($path) ? url('storage/' . $path) : Storage::disk('public')->url($path);
    }
}
