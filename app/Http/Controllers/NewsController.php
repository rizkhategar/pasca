<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    private const NEWS_API_URL = 'https://panel-web.unw.ac.id/api/news';
    private const NEWS_CACHE_KEY = 'pascasarjana_all_news_api_data_v3';
    private const CACHE_TTL_HOURS = 6;
    private const PAGE_CACHE_TTL_MINUTES = 10;
    private const API_PER_PAGE = 100;

    public function index(): Response
    {
        // Render immediately from a valid cached first page when available.
        // Empty/invalid API responses are intentionally never used as the first-page cache.
        $initialNewsPayload = Cache::get($this->pageCacheKey(1, 9));

        if (! $this->hasNewsItems($initialNewsPayload)) {
            $initialNewsPayload = null;
        }

        $viewData = compact('initialNewsPayload');
        $page = view('news.index', $viewData)->render();
        $bootstrap = view('component.news-fast-first-render', $viewData)->render();

        // News toolbar uses the generic .dropdown-trigger class. Keep its rounded filter styles
        // inside the news panel only so it cannot affect Profile / Academic header navigation.
        $headerIsolation = <<<'HTML'
<style>
    #siteHeader .nav-link.dropdown-trigger {
        display: flex !important;
        width: auto !important;
        min-width: 0 !important;
        height: 64px !important;
        grid-template-columns: none !important;
        border: 0 !important;
        border-radius: 0 !important;
        padding: 0 14px !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    #siteHeader .nav-item:hover > .nav-link.dropdown-trigger,
    #siteHeader .nav-item.open > .nav-link.dropdown-trigger,
    #siteHeader .nav-link.dropdown-trigger.nav-route-active,
    #siteHeader .nav-link.dropdown-trigger.nav-click-active {
        background: var(--yellow) !important;
        color: var(--white) !important;
    }

    @media (max-width: 992px) {
        #siteHeader .nav-link.dropdown-trigger {
            width: 100% !important;
            height: 50px !important;
            grid-template-columns: none !important;
            border-radius: 0 !important;
            padding: 0 18px !important;
        }
    }
</style>
HTML;

        $page = str_replace('</head>', $headerIsolation . "\n</head>", $page);

        $marker = "<script>\n        (function() {";
        $replacement = $bootstrap . "\n    <script>\n        (function() {";

        return response(str_replace($marker, $replacement, $page));
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
            // The default state fetches only the page being viewed. This keeps first paint fast.
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
                ? $items->sortBy(fn (array $item): string => $this->newsDate($item))->values()
                : $items->sortByDesc(fn (array $item): string => $this->newsDate($item))->values();

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
            report($exception);

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
        $cacheKey = $this->pageCacheKey($page, $perPage);
        $cached = Cache::get($cacheKey);

        if ($this->hasNewsItems($cached)) {
            return $cached;
        }

        $payload = $this->fetchNewsPageFromApi($page, $perPage);

        // Do not cache an empty item list alongside a non-zero API total.
        // That was the cause of the "Semua" filter showing no cards.
        if ($this->hasNewsItems($payload) || (int) data_get($payload, 'meta.total', 0) === 0) {
            Cache::put($cacheKey, $payload, now()->addMinutes(self::PAGE_CACHE_TTL_MINUTES));
        }

        return $payload;
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
            ?? data_get($payload, 'current_page')
            ?? $page
        ));
        $total = max($items->count(), (int) (
            data_get($payload, 'meta.total')
            ?? data_get($payload, 'data.total')
            ?? data_get($payload, 'total')
            ?? 0
        ));

        return [
            'data' => $items->all(),
            'meta' => $this->paginationMeta($currentPage, $lastPage, $perPage, $total),
        ];
    }

    private function pageCacheKey(int $page, int $perPage): string
    {
        return "pascasarjana_news_page_v3_{$perPage}_{$page}";
    }

    private function getAllNewsFromCache(): array
    {
        return Cache::remember(
            self::NEWS_CACHE_KEY,
            now()->addHours(self::CACHE_TTL_HOURS),
            fn (): array => $this->fetchAllNewsFromApi(),
        );
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
                            ->connectTimeout(5)
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
            ->filter(fn (mixed $item): bool => is_array($item))
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
        $candidates = [
            data_get($payload, 'data.data'),
            data_get($payload, 'data.items'),
            data_get($payload, 'data.rows'),
            data_get($payload, 'data.news.data'),
            data_get($payload, 'data.news'),
            data_get($payload, 'data'),
            data_get($payload, 'items'),
            data_get($payload, 'rows'),
            data_get($payload, 'news.data'),
            data_get($payload, 'news'),
            data_get($payload, 'result.data'),
            data_get($payload, 'result.items'),
            data_get($payload, 'result'),
        ];

        foreach ($candidates as $candidate) {
            $items = $this->newsListFromCandidate($candidate);

            if ($items->isNotEmpty()) {
                return $items;
            }
        }

        return $this->findNewsListRecursively($payload);
    }

    private function newsListFromCandidate(mixed $candidate): Collection
    {
        if (! is_array($candidate) || ! array_is_list($candidate)) {
            return collect();
        }

        $items = collect($candidate)
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values();

        if ($items->isEmpty()) {
            return collect();
        }

        return $items->contains(fn (array $item): bool => $this->looksLikeNewsItem($item))
            ? $items
            : collect();
    }

    private function findNewsListRecursively(mixed $value): Collection
    {
        if (! is_array($value)) {
            return collect();
        }

        $directList = $this->newsListFromCandidate($value);

        if ($directList->isNotEmpty()) {
            return $directList;
        }

        foreach ($value as $child) {
            $items = $this->findNewsListRecursively($child);

            if ($items->isNotEmpty()) {
                return $items;
            }
        }

        return collect();
    }

    private function looksLikeNewsItem(array $item): bool
    {
        return array_key_exists('title', $item)
            || array_key_exists('slug', $item)
            || array_key_exists('publishedAt', $item)
            || array_key_exists('published_at', $item)
            || array_key_exists('createdAt', $item)
            || array_key_exists('created_at', $item)
            || array_key_exists('body', $item)
            || array_key_exists('excerpt', $item);
    }

    private function hasNewsItems(mixed $payload): bool
    {
        return is_array($payload)
            && is_array(data_get($payload, 'data'))
            && count(data_get($payload, 'data')) > 0;
    }

    private function lastPageFromPayload(array $payload): int
    {
        return max(1, (int) (
            data_get($payload, 'meta.last_page')
            ?? data_get($payload, 'data.last_page')
            ?? data_get($payload, 'pagination.last_page')
            ?? data_get($payload, 'last_page')
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
