<?php

use App\Jobs\SyncNewsApiJob; // Import class Job yang sudah kita buat
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Tambahkan facade Schedule

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan sinkronisasi berita dari API berjalan otomatis
// Anda bisa mengubahnya menjadi ->everyFifteenMinutes() atau ->everyThirtyMinutes() jika butuh lebih realtime
Schedule::job(new SyncNewsApiJob)->hourly();

// Jalankan pengecekan timer hardcode Fetch All SINTA setiap menit.
// Jam eksekusi utama diatur di RunScheduledSintaLecturerFetchAll::HARDCODED_SCHEDULED_TIME.
Schedule::command('sinta:run-scheduled-fetch-all')->everyMinute();
