<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const NEWS_API_URL = 'https://panel-web.unw.ac.id/api/news';
    private const NEWS_API_ORIGIN = 'https://panel-web.unw.ac.id';

    public function index(): View
    {
        $sliders = Slider::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->oldest('id')
            ->get();

        $homeNews = Cache::remember('home_latest_news_with_images_v1', now()->addMinutes(15), function (): array {
            return $this->fetchLatestNews();
        });

        return view('home', compact('sliders', 'homeNews'));
    }

    private function fetchLatestNews(): array
    {
        try {
            $response = Http::withoutVerifying()
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(1, 200)
                ->get(self::NEWS_API_URL, [
                    'paginate' => 4,
                    'page' => 1,
                ]);

            if (! $response->successful()) {
                return [];
            }

            return $this->extractNewsItems($response->json())
                ->take(4)
                ->map(fn (array $item): array => $this->normalizeNews($item))
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function extractNewsItems(array $payload)
    {
        $candidates = [
            data_get($payload, 'data.data'),
            data_get($payload, 'data.items'),
            data_get($payload, 'data.news'),
            data_get($payload, 'data'),
            data_get($payload, 'items'),
            data_get($payload, 'news'),
        ];

        foreach ($candidates as $candidate) {
            if (is_array($candidate) && array_is_list($candidate)) {
                $items = collect($candidate)
                    ->filter(fn (mixed $item): bool => is_array($item))
                    ->values();

                if ($items->isNotEmpty()) {
                    return $items;
                }
            }
        }

        return collect();
    }

    private function normalizeNews(array $item): array
    {
        $category = data_get($item, 'category', []);

        return [
            'title' => (string) data_get($item, 'title', 'Tanpa Judul'),
            'slug' => (string) data_get($item, 'slug', '#'),
            'image' => $this->resolveImageUrl((string) (
                data_get($item, 'image_thumbnail')
                ?? data_get($item, 'thumbnail')
                ?? data_get($item, 'image')
                ?? data_get($item, 'cover')
                ?? data_get($item, 'photo')
                ?? ''
            )),
            'excerpt' => trim(strip_tags((string) (data_get($item, 'excerpt') ?? data_get($item, 'body') ?? data_get($item, 'content') ?? ''))),
            'category_name' => (string) (data_get($category, 'name') ?? data_get($item, 'category_name') ?? 'Umum'),
            'date' => (string) (data_get($item, 'publishedAt') ?? data_get($item, 'published_at') ?? data_get($item, 'createdAt') ?? data_get($item, 'created_at') ?? ''),
        ];
    }

    private function resolveImageUrl(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        return rtrim(self::NEWS_API_ORIGIN, '/') . '/' . ltrim($path, '/');
    }
}
