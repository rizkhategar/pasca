<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SyncNewsApiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const NEWS_API_URL = 'https://panel-web.unw.ac.id/api/news';

    // Tambahkan batas waktu 20 Menit agar sistem tidak menggagalkan Job yang sedang berjalan
    public $timeout = 1200; 

    public function handle(): void
    {
        // 1. Ambil Halaman Pertama untuk mengetahui total halaman (last_page)
        $firstResponse = Http::withoutVerifying()->timeout(30)->get(self::NEWS_API_URL, ['page' => 1]);

        if (! $firstResponse->successful()) {
            return;
        }

        $firstPayload = $firstResponse->json();
        $lastPage = max(1, (int) data_get($firstPayload, 'meta.last_page', 1));
        $items = collect($this->extractNewsItems($firstPayload));

        // 2. Ambil Sisa Halaman secara Berurutan (Mencegah Error 429 Too Many Requests / Banned dari server UNW)
        if ($lastPage > 1) {
            for ($page = 2; $page <= $lastPage; $page++) {
                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->retry(3, 1000) // Jika gagal/timeout, coba otomatis 3x lagi
                    ->get(self::NEWS_API_URL, ['page' => $page]);

                if ($response->successful()) {
                    $items = $items->merge($this->extractNewsItems($response->json()));
                }
                
                // Beri jeda 0.2 Detik setiap tarik data agar server UNW tidak terbebani
                usleep(200000); 
            }
        }

        // 3. Simpan 1054+ Berita tersebut ke dalam Cache selama 12 Jam
        Cache::put('all_news_api_data', $items->values()->toArray(), now()->addHours(12));
    }

    private function extractNewsItems(array $payload): array
    {
        $items = data_get($payload, 'data', []);

        if (! is_array($items)) {
            $items = data_get($payload, 'data.data', []);
        }

        return collect(is_array($items) ? $items : [])
            ->filter(fn ($item): bool => is_array($item))
            ->values()
            ->toArray();
    }
}