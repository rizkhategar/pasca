<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AcademicController extends Controller
{
    private static array $programOrder = [
        's2-keperawatan',
        'magister-keperawatan',
        's2-kesehatan-masyarakat',
        'magister-kesehatan-masyarakat',
        's2-manajemen-pendidikan',
        'magister-manajemen-pendidikan',
        's2-hukum',
        'magister-hukum',
    ];

    public static function getNavigationData(): array
    {
        return Cache::remember('academic_programs_nav', now()->addHours(12), function () {
            $response = Http::withoutVerifying()->get('https://panel-web.unw.ac.id/api/unw-program-studi');

            if (! $response->successful()) {
                return [];
            }

            return collect($response->json('data', []))
                ->filter(function ($item) {
                    return isset($item['id'], $item['slug'], $item['unwFakultas']['nama'])
                        && trim($item['unwFakultas']['nama']) === 'Pascasarjana';
                })
                ->map(function ($item) {
                    return [
                        // MENAMBAHKAN: Mengambil properti ID dari API untuk kebutuhan filter frontend
                        'id' => $item['id'],
                        'slug' => $item['slug'],
                        'display_name' => trim(($item['jenjang'] ?? '') . ' ' . ($item['nama'] ?? '')),
                    ];
                })
                ->sortBy(function ($item) {
                    $order = array_search($item['slug'], self::$programOrder, true);

                    return $order !== false ? $order : 99;
                })
                ->values()
                ->toArray();
        });
    }

    public function show(string $slug)
    {
        $response = Http::withoutVerifying()->get("https://panel-web.unw.ac.id/api/unw-program-studi-page/{$slug}");
        $academicProgramsNav = self::getNavigationData();

        if ($response->successful() && $response->json('data')) {
            $program = $response->json('data');

            return view('profile.academic', compact('program', 'academicProgramsNav'));
        }

        abort(404, 'Program Studi tidak ditemukan.');
    }
}