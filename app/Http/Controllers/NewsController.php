<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const NEWS_API_URL = 'https://panel-web.unw.ac.id/api/news';
    private const NEWS_CACHE_KEY = 'pascasarjana_all_news_api_data_v2';
    private const CACHE_TTL_HOURS = 6;
    private const PAGE_CACHE_TTL_MINUTES = 10;
    private const API_PER_PAGE = 100;

    public function index(): View
    {
        // Avoid blocking the first page render. Cached page-one data is injected when available,
        // while the browser refreshes it in the background through the JSON endpoint.
        $initialNewsPayload = Cache::get($this->pageCacheKey(1, 9));

        return view('news.index', compact('initialNewsPayload'));
    }

    public function search(Request $request): JsonResponse
    {
        $query = Str::of((string) $request->query('q'))->lower()->squish()->toString();
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(30, max(1, (int) $request->query('paginate', 9)));
        $categoryId = (string) $request->query('category_id', 'all');
        $categorySlug = (string) $request->query('category', 'all');
        $sort = strtolower((string) $request->query('sort', 'desc')) === 'asc' ? 'asc' : 'desc';

        try {
            // The normal landing state should never wait for every API page to be fetched.
            // It requests and caches only the page currently viewed, so news appears quickly.
            if ($query === '' && $categoryId === 'all' && $categorySlug === 'all' && $sort === 'desc') {
                return response()
                    ->json($this->getFastNewsPage($page, $perPage))
                    ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
            }

            $items = collect($this->getAllNewsFromCache());

            if ($categoryId !== 'all' && $categoryId !== '') {
                $items = $items->filter(fn (array $item): bool => (string) data_get($item, 'category.id', data_get($item, 'category_id', '')) === $categoryId);
            } elseif ($categorySlug !== 'all' && $categorySlug !== '') {
                $items = $items->filter(fn (array $item): bool => (string) data_get($item, 'category.slug', '') === $categorySlug);
            }

            if ($query !== '') {
                $items = $items->filter(fn (array $item): bool => $this->matchesNewsQuery($item, $query));
            }

            $items = $sort === 'asc'
                ? $items->sortBy(fn (array $item): string => (string) $this->newsDate($item))->values()
                : $items->sortByDesc(fn (array $item): string => (string) $this->newsDate($item))->values();

            $total = $items->count();
            $lastPage = max(1, (int) ceil($total / $perPage));
            $page = min($page, $lastPage);
            $pagedItems = $items->slice(($page - 1) * $perPage, $perPage)->values();

            return response()
                ->json([
                    'data' => $pagedItems,
                    'meta' => $this->paginationMeta($page, $lastPage, $perPage, $total),
                ])
                ->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
        } catch (\Throwable $exception) {
            return response()->json([
                'data' => [],
                'meta' => $this->paginationMeta(1, 1, $perPage, 0),
                'message' => 'Berita belum dapat dimuat.',
            ], 500);
        }
    }

    public function show(string $slug): View
    {
        return view('news.show', [
            'slug' => $slug,
        ]);
    }

    private function getFastNewsPage(int $page, int $perPage): array
    {
        return Cache::remember(
            $this->pageCacheKey($page, $perPage),
            now()->addMinutes(self::PAGE_CACHE_TTL_MINUTES),
            fn (): array => $this->fetchNewsPageFromApi($page, $perPage),
        );
    }

    private function fetchNewsPageFromApi(int $page, int $perPage): array
    {
        $response = $this->newsApiRequest([
            'paginate' => $perPage,
            'page' => $page,
        ]);

        if (! $response->successful()) {
            return [
                'data' => [],
                'meta' => $this->paginationMeta($page, 1, $perPage, 0),
            ];
        }

        $payload = $response->json();
        $items = $this->extractNewsItems($payload)->values();
        $lastPage = $this->lastPageFromPayload($payload);
        $currentPage = max(1, (int) (
            data_get($payload, 'meta.current_page')
            ?? data_get($payload, 'data.current_page')
            ?? $page
        ));
        $total = max($items->count(), (int) (
            data_get($payload, 'meta.total')
            ?? data_get($payload, 'data.total')
            ?? 0
        ));

        return [
            'data' => $items,
            'meta' => $this->paginationMeta($currentPage, $lastPage, $perPage, $total),
        ];
    }

    private function pageCacheKey(int $page, int $perPage): string
    {
        return "pascasarjana_news_page_v1_{$perPage}_{$page}";
    }

    private function getAllNewsFromCache(): array
    {
        return Cache::remember(self::NEWS_CACHE_KEY, now()->addHours(self::CACHE_TTL_HOURS), fn (): array => $this->fetchAllNewsFromApi());
    }

    private function fetchAllNewsFromApi(): array
    {
        set_time_limit(300);

        $baseParams = [
            'paginate' => self::API_PER_PAGE,
            'page' => 1,
        ];

        $firstResponse = $this->newsApiRequest($baseParams);

        if (! $firstResponse->successful()) {
            return [];
        }

        $firstPayload = $firstResponse->json();
        $items = $this->extractNewsItems($firstPayload);
        $lastPage = $this->lastPageFromPayload($firstPayload);

        if ($lastPage > 1) {
            foreach (array_chunk(range(2, $lastPage), 8) as $pageQueue) {
                $responses = Http::pool(function (Pool $pool) use ($pageQueue, $baseParams) {
                    $requests = [];

                    foreach ($pageQueue as $queuedPage) {
                        $requests[(string) $queuedPage] = $pool
                            ->as((string) $queuedPage)
                            ->withoutVerifying()
                            ->acceptJson()
                            ->timeout(15)
                            ->get(self::NEWS_API_URL, array_merge($baseParams, [
                                'page' => $queuedPage,
                            ]));
                    }

                    return $requests;
                });

                foreach ($responses as $response) {
                    if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                        $items = $items->merge($this->extractNewsItems($response->json()));
                    }
                }
            }
        }

        return $items
            ->unique(fn (array $item): string => (string) (data_get($item, 'id') ?: data_get($item, 'slug') ?: md5(json_encode($item))))
            ->values()
            ->toArray();
    }

    private function newsApiRequest(array $params)
    {
        return Http::withoutVerifying()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(12)
            ->retry(1, 200)
            ->get(self::NEWS_API_URL, $params);
    }

    private function extractNewsItems(array $payload): Collection
    {
        $directData = data_get($payload, 'data');

        if (is_array($directData) && array_is_list($directData)) {
            return collect($directData)->filter(fn ($item): bool => is_array($item))->values();
        }

        $nestedData = data_get($payload, 'data.data');

        if (is_array($nestedData) && array_is_list($nestedData)) {
            return collect($nestedData)->filter(fn ($item): bool => is_array($item))->values();
        }

        $itemsData = data_get($payload, 'items');

        if (is_array($itemsData) && array_is_list($itemsData)) {
            return collect($itemsData)->filter(fn ($item): bool => is_array($item))->values();
        }

        return collect();
    }

    private function lastPageFromPayload(array $payload): int
    {
        return max(1, (int) (
            data_get($payload, 'meta.last_page')
            ?? data_get($payload, 'data.last_page')
            ?? data_get($payload, 'pagination.last_page')
            ?? 1
        ));
    }

    private function matchesNewsQuery(array $item, string $query): bool
    {
        $haystack = Str::of(implode(' ', [
            data_get($item, 'title', ''),
            strip_tags((string) data_get($item, 'excerpt', '')),
            strip_tags((string) data_get($item, 'content', '')),
            strip_tags((string) data_get($item, 'body', '')),
            strip_tags((string) data_get($item, 'description', '')),
            data_get($item, 'category.name', ''),
            data_get($item, 'category.slug', ''),
            data_get($item, 'author.name', ''),
            $this->newsDate($item),
        ]))->lower()->squish()->toString();

        return collect(explode(' ', $query))
            ->filter()
            ->every(fn (string $keyword): bool => str_contains($haystack, $keyword));
    }

    private function newsDate(array $item): string
    {
        return (string) (
            data_get($item, 'publishedAt')
            ?? data_get($item, 'published_at')
            ?? data_get($item, 'createdAt')
            ?? data_get($item, 'created_at')
            ?? ''
        );
    }

    private function paginationMeta(int $page, int $lastPage, int $perPage, int $total): array
    {
        return [
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }
}
